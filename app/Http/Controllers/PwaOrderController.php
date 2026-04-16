<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PwaOrder;
use App\Models\PwaOrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Journal;
use App\Models\Category;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PwaOrderController extends Controller
{
    protected $transactionService;

    public function __construct(\App\Services\TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    // =============================================
    // PUBLIC PWA STOREFRONT ENDPOINTS (No Auth)
    // =============================================

    /**
     * Show the PWA storefront page
     */
    public function storefront()
    {
        $categories = Category::orderBy('name')->get();
        return view('pwa.storefront', compact('categories'));
    }

    /**
     * API: Get products with stock info for storefront
     */
    public function apiProducts(Request $request)
    {
        $query = Product::with('stocks', 'category');

        // Search
        if ($request->filled('q')) {
            $searchTerm = trim($request->q);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('barcode', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Category filter
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('name')->limit(100)->get()->map(function ($product) {
            return [
                'id'            => $product->id,
                'name'          => $product->name,
                'price'         => (float) $product->price,
                'image'         => $product->image ? asset('uploads/products/' . $product->image) : null,
                'category'      => $product->category->name ?? 'Lainnya',
                'category_id'   => $product->category_id,
                'stock'         => $product->available_stock,
                'unit'          => $product->unit ?? 'pcs',
            ];
        });

        return response()->json(['success' => true, 'data' => $products]);
    }

    /**
     * API: Check if the store is open (cashier is logged in)
     */
    public function apiStoreStatus()
    {
        // Check if any cashier has an active session
        $cashierOnline = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereRaw('LOWER(roles.name) = ?', ['cashier'])
            ->where('sessions.last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->exists();

        $storeName = Setting::get('store_name', 'ARTIKA Minimarket');

        return response()->json([
            'success'    => true,
            'open'       => $cashierOnline,
            'store_name' => $storeName,
            'message'    => $cashierOnline
                ? 'Toko sedang buka — Silakan pesan!'
                : 'Toko sedang tutup — Kasir belum login.',
        ]);
    }

    /**
     * API: Create a new PWA order
     */
    public function apiCreateOrder(Request $request)
    {
        $validated = $request->validate([
            'customer_name'     => 'required|string|max:100',
            'customer_whatsapp' => 'required|string|max:20',
            'delivery_location' => 'required|string|max:200',
            'notes'             => 'nullable|string|max:500',
            'items'             => 'required|array|min:1|max:50',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1|max:100',
        ]);

        // Verify store is open
        $cashierOnline = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereRaw('LOWER(roles.name) = ?', ['cashier'])
            ->where('sessions.last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->exists();

        if (!$cashierOnline) {
            return response()->json([
                'success' => false,
                'message' => 'Toko sedang tutup. Kasir belum login.',
            ], 422);
        }

        try {
            return DB::transaction(function () use ($validated) {
                // Fetch all products in one query
                $productIds = array_column($validated['items'], 'product_id');
                $products = Product::with('stocks')
                    ->whereIn('id', $productIds)
                    ->get()
                    ->keyBy('id');

                // Validate stock and build order items
                $orderItems = [];
                $subtotal = 0;

                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['product_id']);

                    if (!$product) {
                        throw new \Exception("Produk tidak ditemukan atau sudah nonaktif.");
                    }

                    $available = $product->available_stock;
                    if ($available < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak cukup. Tersedia: {$available}");
                    }

                    $itemSubtotal = $product->price * $item['quantity'];
                    $subtotal += $itemSubtotal;

                    $orderItems[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'quantity'     => $item['quantity'],
                        'price'        => $product->price,
                        'subtotal'     => $itemSubtotal,
                    ];
                }

                // Create the order
                $order = PwaOrder::create([
                    'order_no'           => PwaOrder::generateOrderNumber(),
                    'customer_name'      => $validated['customer_name'],
                    'customer_whatsapp'  => $validated['customer_whatsapp'],
                    'delivery_location'  => $validated['delivery_location'],
                    'subtotal'           => $subtotal,
                    'total_amount'       => $subtotal,
                    'status'             => 'pending',
                    'notes'              => $validated['notes'] ?? null,
                ]);

                // Bulk insert order items
                $now = now()->toDateTimeString();
                $itemsData = array_map(function ($item) use ($order, $now) {
                    return array_merge($item, [
                        'pwa_order_id' => $order->id,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                }, $orderItems);

                PwaOrderItem::insert($itemsData);

                return response()->json([
                    'success'  => true,
                    'order_no' => $order->order_no,
                    'total'    => $order->total_amount,
                    'message'  => 'Pesanan berhasil dikirim! Tunggu konfirmasi dari kasir.',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('PWA Order Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API: Get order history for a customer by WhatsApp
     */
    public function apiOrderHistory(Request $request)
    {
        $wa = $request->input('whatsapp');
        if (!$wa) {
            return response()->json(['success' => false, 'message' => 'Nomor WhatsApp diperlukan.'], 400);
        }

        $orders = PwaOrder::with('items')
            ->where('customer_whatsapp', $wa)
            ->orderBy('created_at', 'desc')
            ->limit(10) // Limit to last 10 orders
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $orders
        ]);
    }


    // =============================================
    // CASHIER DASHBOARD ENDPOINTS (Auth Required)
    // =============================================

    /**
     * Show the cashier PWA orders dashboard
     */
    public function cashierDashboard()
    {
        $stats = [
            'pending'    => PwaOrder::today()->pending()->count(),
            'processing' => PwaOrder::today()->processing()->count(),
            'completed'  => PwaOrder::today()->where('status', 'completed')->count(),
            'revenue'    => PwaOrder::today()->where('status', 'completed')->sum('total_amount'),
        ];

        $orders = PwaOrder::with('items')
            ->today()
            ->orderByRaw("FIELD(status, 'pending', 'processing', 'completed', 'cancelled')")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pos.pwa-orders', compact('stats', 'orders'));
    }

    /**
     * API: Get orders list for real-time polling
     */
    public function apiOrdersList(Request $request)
    {
        $orders = PwaOrder::with('items')
            ->today()
            ->orderByRaw("FIELD(status, 'pending', 'processing', 'completed', 'cancelled')")
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'pending'    => PwaOrder::today()->pending()->count(),
            'processing' => PwaOrder::today()->processing()->count(),
            'completed'  => PwaOrder::today()->where('status', 'completed')->count(),
            'revenue'    => (float) PwaOrder::today()->where('status', 'completed')->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'orders'  => $orders,
            'stats'   => $stats,
        ]);
    }

    /**
     * Process a PWA order: deduct stock + update status
     */
    public function processOrder($id)
    {
        $order = PwaOrder::with('items')->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah diproses atau dibatalkan.',
            ], 422);
        }

        try {
            return DB::transaction(function () use ($order) {
                $now = now()->toDateTimeString();
                $userId = Auth::id();

                // Bulk-fetch products and stocks
                $productIds = $order->items->pluck('product_id')->toArray();
                $products = Product::with('stocks')->whereIn('id', $productIds)->get()->keyBy('id');

                // Validate stock
                foreach ($order->items as $item) {
                    $product = $products->get($item->product_id);
                    $available = $product ? $product->available_stock : 0;

                    if ($available < $item->quantity) {
                        throw new \Exception("Stok {$item->product_name} tidak cukup! Tersedia: {$available}, Diminta: {$item->quantity}");
                    }
                }

                // Deduct stock using bulk CASE UPDATE (same pattern as TransactionService)
                $stockUpdateCases = '';
                $stockUpdateIds = [];
                $stockMovementsData = [];

                foreach ($order->items as $item) {
                    $product = $products->get($item->product_id);
                    $stockRecord = $product->stocks->first();
                    $qtyBefore = $stockRecord ? $stockRecord->quantity : 0;
                    $qtyAfter = $qtyBefore - $item->quantity;

                    $productId = (int) $item->product_id;
                    $qtyToDeduct = (int) $item->quantity;
                    $stockUpdateCases .= "WHEN product_id = {$productId} THEN quantity - {$qtyToDeduct} ";
                    $stockUpdateIds[] = $productId;

                    $stockMovementsData[] = [
                        'product_id'      => $item->product_id,
                        'user_id'         => $userId,
                        'type'            => 'out',
                        'quantity_before'  => $qtyBefore,
                        'quantity_after'   => $qtyAfter,
                        'quantity_change'  => -$item->quantity,
                        'reason'          => 'PWA Order',
                        'reference'       => $order->order_no,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                }

                // Execute bulk stock update
                if (!empty($stockUpdateIds)) {
                    $idsList = implode(',', $stockUpdateIds);
                    DB::statement("UPDATE stocks SET quantity = CASE {$stockUpdateCases} ELSE quantity END WHERE product_id IN ({$idsList})");
                }

                // Bulk insert stock movements
                if (!empty($stockMovementsData)) {
                    StockMovement::insert($stockMovementsData);
                }

                // Update order status
                $order->update([
                    'status'       => 'processing',
                    'processed_by' => $userId,
                    'processed_at' => now(),
                ]);

                // Audit log
                AuditLog::log(
                    'pwa_order_processed',
                    'PwaOrder',
                    $order->id,
                    $order->total_amount,
                    'pwa',
                    [
                        'order_no'   => $order->order_no,
                        'customer'   => $order->customer_name,
                        'items_count' => $order->items->count(),
                    ],
                    'PWA Order Processed: ' . $order->order_no
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil diproses! Stok sudah dipotong.',
                    'receipt_url' => route('pos.pwa-orders.receipt', $order->id),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('PWA Process Order Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete a PWA order: create Transaction record for daily reports
     */
    public function completeOrder($id)
    {
        $order = PwaOrder::with('items')->findOrFail($id);

        if ($order->status !== 'processing') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan harus berstatus "Diproses" untuk ditandai LUNAS.',
            ], 422);
        }

        try {
            return DB::transaction(function () use ($order) {
                $now = now()->toDateTimeString();
                $userId = Auth::id();

                // Create Transaction record (so it appears in daily POS reports)
                $invoiceNo = $this->generatePwaInvoice($order);

                $transaction = Transaction::create([
                    'invoice_no'     => $invoiceNo,
                    'user_id'        => $userId,
                    'subtotal'       => $order->subtotal,
                    'discount'       => 0,
                    'total_amount'   => $order->total_amount,
                    'payment_method' => 'cash',
                    'cash_amount'    => $order->total_amount,
                    'change_amount'  => 0,
                    'status'         => 'completed',
                ]);

                // Create Transaction Items
                $transactionItemsData = [];
                foreach ($order->items as $item) {
                    $transactionItemsData[] = [
                        'transaction_id' => $transaction->id,
                        'product_id'     => $item->product_id,
                        'quantity'       => $item->quantity,
                        'price'          => $item->price,
                        'subtotal'       => $item->subtotal,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
                TransactionItem::insert($transactionItemsData);

                // Create Journal entries (double-entry accounting)
                Journal::insert([
                    [
                        'transaction_id' => $transaction->id,
                        'type'           => 'debit',
                        'account_name'   => 'Cash',
                        'amount'         => $transaction->total_amount,
                        'description'    => 'PWA Sales ' . $invoiceNo . ' (' . $order->order_no . ')',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ],
                    [
                        'transaction_id' => $transaction->id,
                        'type'           => 'credit',
                        'account_name'   => 'Sales Revenue',
                        'amount'         => $transaction->total_amount,
                        'description'    => 'PWA Sales ' . $invoiceNo . ' (' . $order->order_no . ')',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ],
                ]);

                // Update order status
                $order->update([
                    'status'         => 'completed',
                    'completed_at'   => now(),
                    'transaction_id' => $transaction->id,
                ]);

                // Audit log
                AuditLog::log(
                    'pwa_order_completed',
                    'PwaOrder',
                    $order->id,
                    $order->total_amount,
                    'cash',
                    [
                        'order_no'   => $order->order_no,
                        'invoice_no' => $invoiceNo,
                        'customer'   => $order->customer_name,
                    ],
                    'PWA Order LUNAS: ' . $order->order_no . ' → ' . $invoiceNo
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan telah LUNAS dan masuk laporan penjualan!',
                    'invoice_no' => $invoiceNo,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('PWA Complete Order Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a PWA order
     */
    public function cancelOrder(Request $request, $id)
    {
        $order = PwaOrder::with('items')->findOrFail($id);

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini tidak bisa dibatalkan.',
            ], 422);
        }

        try {
            return DB::transaction(function () use ($order, $request) {
                $now = now()->toDateTimeString();
                $userId = Auth::id();

                // If status is 'processing', restore stock
                if ($order->status === 'processing') {
                    $stockMovementsData = [];

                    foreach ($order->items as $item) {
                        $stock = Stock::where('product_id', $item->product_id)->first();
                        $qtyBefore = $stock ? $stock->quantity : 0;
                        $qtyAfter = $qtyBefore + $item->quantity;

                        // Restore stock
                        if ($stock) {
                            $stock->increment('quantity', $item->quantity);
                        }

                        $stockMovementsData[] = [
                            'product_id'      => $item->product_id,
                            'user_id'         => $userId,
                            'type'            => 'in',
                            'quantity_before'  => $qtyBefore,
                            'quantity_after'   => $qtyAfter,
                            'quantity_change'  => $item->quantity,
                            'reason'          => 'PWA Order Cancelled (Stock Restore)',
                            'reference'       => $order->order_no,
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ];
                    }

                    if (!empty($stockMovementsData)) {
                        StockMovement::insert($stockMovementsData);
                    }
                }

                // Update order status
                $order->update([
                    'status'        => 'cancelled',
                    'cancelled_at'  => now(),
                    'cancel_reason' => $request->input('reason', 'Dibatalkan oleh kasir'),
                ]);

                // Audit log
                AuditLog::log(
                    'pwa_order_cancelled',
                    'PwaOrder',
                    $order->id,
                    $order->total_amount,
                    'pwa',
                    [
                        'order_no'       => $order->order_no,
                        'previous_status' => $order->getOriginal('status'),
                        'reason'         => $request->input('reason', 'Dibatalkan oleh kasir'),
                    ],
                    'PWA Order Cancelled: ' . $order->order_no
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil dibatalkan.' .
                        ($order->getOriginal('status') === 'processing' ? ' Stok telah dikembalikan.' : ''),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('PWA Cancel Order Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print receipt for a PWA order
     */
    public function printReceipt($id)
    {
        $order = PwaOrder::with('items')->findOrFail($id);
        $paperSize = Setting::get('receipt_paper_size', '58mm');

        return view('pos.pwa-receipt', compact('order', 'paperSize'));
    }

    /**
     * Generate invoice number for PWA transaction
     */
    protected function generatePwaInvoice(PwaOrder $order): string
    {
        $prefix = Setting::get('invoice_prefix', 'INV');
        $date = now()->format('Ymd');

        $todayCount = DB::table('transactions')
            ->whereDate('created_at', now()->toDateString())
            ->lockForUpdate()
            ->count();

        $seq = str_pad($todayCount + 1, 5, '0', STR_PAD_LEFT);

        return "{$prefix}-PWA-{$date}-{$seq}";
    }
}
