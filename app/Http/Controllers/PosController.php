<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeldTransaction;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    protected $transactionService;
    protected $productRepository;

    public function __construct(
        \App\Services\TransactionService $transactionService,
        \App\Interfaces\ProductRepositoryInterface $productRepository
    ) {
        $this->transactionService = $transactionService;
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        // Load only initial subset of products to speed up first load
        $products = \App\Models\Product::with('stocks')->limit(50)->get();

        // Load favorite products for quick buttons — capped at 50 to prevent memory exhaustion
        $favoriteProducts = \App\Models\Product::with('stocks')
            ->where('is_favorite', true)
            ->limit(50)
            ->get();

        $categories = Category::all();
        $paymentMethods = PaymentMethod::where('is_active', true)->ordered()->get();
        $heldTransactions = HeldTransaction::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $activePromos = \App\Models\Promo::active()->get();

        return view('pos.index', compact('products', 'categories', 'paymentMethods', 'heldTransactions', 'activePromos', 'favoriteProducts'));
    }

    public function search(Request $request)
    {
        $searchTerm = trim($request->input('q', ''));
        $categoryId = $request->input('category_id', 'all');

        $query = \App\Models\Product::with('stocks');

        if ($searchTerm !== '') {
            // If input looks like a barcode (digits only), do exact match first (uses unique index)
            if (ctype_digit($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('barcode', $searchTerm)
                      ->orWhereRaw('MATCH(name, barcode) AGAINST(? IN BOOLEAN MODE)', [$searchTerm . '*']);
                });
            } else {
                // Use FULLTEXT search: avoids full table scan, uses idx_products_fulltext index
                $query->whereRaw('MATCH(name, barcode) AGAINST(? IN BOOLEAN MODE)', [$searchTerm . '*']);
            }
        }

        if ($categoryId !== 'all' && $categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        // Hard cap: max 50 results per search request
        $products = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function logs(Request $request)
    {
        $query = AuditLog::where('user_id', Auth::id());

        // Filter by type
        if ($request->has('type')) {
            $type = $request->type;
            if ($type === 'login') {
                $query->where('action', 'login');
            } elseif ($type === 'transaction') {
                $query->where('action', 'transaction_created');
            }
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pos.logs', compact('logs'));
    }

    public function history(Request $request)
    {
        $userId = Auth::id();

        // Default to today if no date filters provided — prevents full table SUM over millions of rows
        $startDate = $request->filled('start_date') ? $request->start_date : today()->toDateString();
        $endDate   = $request->filled('end_date')   ? $request->end_date   : today()->toDateString();

        $query = Transaction::where('user_id', $userId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->with('items.product');

        // Get Summary Stats (before pagination)
        $summaryQuery = clone $query;
        $totalRevenue = $summaryQuery->sum('total_amount') ?? 0;

        // Get Sold Items Summary — uses direct JOIN instead of slow whereIn(subquery)
        // Cached per user per date range for 10 minutes
        $cacheKey = 'sold_items_' . $userId . '_' . $startDate . '_' . $endDate;
        $soldItems = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId, $startDate, $endDate) {
            return TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->where('transactions.user_id', $userId)
                ->whereDate('transactions.created_at', '>=', $startDate)
                ->whereDate('transactions.created_at', '<=', $endDate)
                ->select(
                    'products.name',
                    DB::raw('SUM(transaction_items.quantity) as total_qty'),
                    DB::raw('SUM(transaction_items.subtotal) as total_sales')
                )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_qty')
                ->limit(50)   // cap top 50 sold items — prevents unbounded result set
                ->get();
        });

        // Use simplePaginate: avoids expensive COUNT(*) on large datasets
        $transactions = $query->orderBy('created_at', 'desc')
            ->simplePaginate(10)
            ->withQueryString();

        $enableReturns = \App\Models\Setting::get('cashier_enable_returns', true);

        return view('pos.history', compact('transactions', 'totalRevenue', 'soldItems', 'enableReturns', 'startDate', 'endDate'));
    }

    public function showReceipt($id)
    {
        $transaction = Transaction::where('user_id', Auth::id())
            ->with(['user', 'items.product'])
            ->findOrFail($id);

        $paperSize = \App\Models\Setting::get('receipt_paper_size', '58mm');

        return view('pos.receipt', compact('transaction', 'paperSize'));
    }

    public function scanner()
    {
        // Do NOT pre-load all products — lookup happens via barcode AJAX on scan event
        return view('pos.scanner');
    }

    /**
     * Barcode lookup for scanner — returns a single product by exact barcode match.
     * Called via AJAX from the scanner page on each scan event.
     */
    public function lookupBarcode(Request $request)
    {
        $barcode = trim($request->input('barcode', ''));

        if (empty($barcode)) {
            return response()->json(['success' => false, 'message' => 'Barcode tidak boleh kosong'], 422);
        }

        // Exact match on indexed unique barcode column — O(1) lookup regardless of table size
        $product = \App\Models\Product::with('stocks')
            ->where('barcode', $barcode)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'cash_amount' => 'nullable|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
            'payment_proof' => 'nullable|image|max:5120', // Max 5MB
        ]);

        try {
            $data = [
                'user_id' => Auth::id(),
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['discount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'cash_amount' => $validated['cash_amount'] ?? $validated['total_amount'],
                'change_amount' => $validated['change_amount'] ?? 0,
                'payment_proof' => null,
                'status' => 'completed',
            ];

            // Dynamic validation for payment method and proof
            $paymentMethod = PaymentMethod::where('slug', $validated['payment_method'])->first();

            // Handle Payment Proof (compressed)
            if ($request->hasFile('payment_proof')) {
                $imageService = app(\App\Services\ImageService::class);
                $originalSize = $imageService->getFileSizeKB($request->file('payment_proof'));
                $data['payment_proof'] = $imageService->compress(
                    $request->file('payment_proof'),
                    'uploads/payment_proofs'
                );
                Log::info("Payment proof compressed: {$originalSize}KB -> saved as {$data['payment_proof']}");
            } elseif ($paymentMethod && $paymentMethod->proof_requirement === 'required') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bukti pembayaran wajib diunggah untuk metode ' . $paymentMethod->name
                ], 422);
            }

            $items = $validated['items'];

            return DB::transaction(function () use ($data, $items) {
                $transaction = $this->transactionService->processTransaction($data, $items);

                // AUTO AUDIT LOG
                AuditLog::log(
                    'transaction_created',
                    'Transaction',
                    $transaction->id,
                    $transaction->total_amount,
                    $transaction->payment_method,
                    [
                        'subtotal' => $transaction->subtotal,
                        'discount' => $transaction->discount,
                        'items_count' => count($items),
                        'cash_amount' => $data['cash_amount'],
                        'change_amount' => $data['change_amount'],
                    ],
                    'Invoice: ' . $transaction->invoice_no
                );

                return response()->json([
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'invoice_no' => $transaction->invoice_no,
                    'change' => $data['change_amount']
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal: ' . implode(', ', $e->errors()[array_key_first($e->errors())])], 422);
        } catch (\Exception $e) {
            Log::error('POS Checkout Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hold current transaction
     */
    public function holdTransaction(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'subtotal' => 'required|numeric',
            'total' => 'required|numeric',
        ]);

        try {
            $held = HeldTransaction::create([
                'user_id' => Auth::id(),
                'items' => $request->items,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'total' => $request->total,
                'note' => $request->note,
            ]);

            return response()->json(['success' => true, 'held_id' => $held->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get held transactions
     */
    public function getHeldTransactions()
    {
        $held = HeldTransaction::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $held]);
    }

    /**
     * Resume held transaction
     */
    public function resumeHeldTransaction($id)
    {
        $held = HeldTransaction::where('user_id', Auth::id())->findOrFail($id);

        // Return the held transaction data
        $data = [
            'items' => $held->items,
            // customer_id removed
            'subtotal' => $held->subtotal,
            'discount' => $held->discount,
            'total' => $held->total,
        ];

        // Delete the held transaction
        $held->delete();

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Delete held transaction
     */
    public function deleteHeldTransaction($id)
    {
        $held = HeldTransaction::where('user_id', Auth::id())->findOrFail($id);
        $held->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Print receipt for a transaction
     */
    public function printReceipt($transactionId)
    {
        $transaction = Transaction::where('user_id', Auth::id())
            ->with(['user', 'items.product'])
            ->findOrFail($transactionId);

        $paperSize = \App\Models\Setting::get('receipt_paper_size', '58mm');

        return view('pos.receipt', compact('transaction', 'paperSize'));
    }
}


