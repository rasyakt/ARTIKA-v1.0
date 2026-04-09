<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsignmentSettlement;
use App\Models\ConsignmentItem;
use App\Models\Consignor;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsignmentSettlementController extends Controller
{
    /**
     * Daftar semua settlement
     */
    public function index(Request $request)
    {
        $query = ConsignmentSettlement::with(['consignor', 'createdBy'])
            ->latest();

        if ($request->filled('consignor_id')) {
            $query->where('consignor_id', $request->consignor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $settlements = $query->paginate(15)->withQueryString();
        $consignors  = Consignor::where('is_active', true)->orderBy('name')->get();

        return view('admin.consignment.settlements.index', compact('settlements', 'consignors'));
    }

    /**
     * Form buat settlement baru (pilih penitip, preview kalkulasi)
     */
    public function create(Request $request)
    {
        $consignors = Consignor::where('is_active', true)->orderBy('name')->get();

        $preview        = null;
        $selectedConsignor = null;

        if ($request->filled('consignor_id') && $request->filled('period_start') && $request->filled('period_end')) {
            $selectedConsignor = Consignor::with('consignmentItems.product')->findOrFail($request->consignor_id);

            $periodStart = Carbon::parse($request->period_start)->startOfDay();
            $periodEnd   = Carbon::parse($request->period_end)->endOfDay();

            // Hitung penjualan per item konsinyasi dalam periode
            $preview = $this->calculateSettlement($selectedConsignor, $periodStart, $periodEnd);
        }

        return view('admin.consignment.settlements.create', compact('consignors', 'selectedConsignor', 'preview', 'request'));
    }

    /**
     * Simpan settlement
     */
    public function store(Request $request)
    {
        $request->validate([
            'consignor_id'   => 'required|exists:consignors,id',
            'period_start'   => 'required|date',
            'period_end'     => 'required|date|after_or_equal:period_start',
            'notes'          => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $consignor   = Consignor::findOrFail($request->consignor_id);
            $periodStart = Carbon::parse($request->period_start)->startOfDay();
            $periodEnd   = Carbon::parse($request->period_end)->endOfDay();

            $calc = $this->calculateSettlement($consignor, $periodStart, $periodEnd);

            if ($calc['total_sold_qty'] <= 0) {
                return back()->with('error', 'Tidak ada penjualan baru pada periode ini yang belum dibayarkan.');
            }

            $settlement = ConsignmentSettlement::create([
                'consignor_id'        => $consignor->id,
                'reference_no'        => ConsignmentSettlement::generateReferenceNo(),
                'period_start'        => $periodStart,
                'period_end'          => $periodEnd,
                'total_sold_qty'      => $calc['total_sold_qty'],
                'total_sales_amount'  => $calc['total_sales_amount'],
                'commission_amount'   => $calc['commission_amount'],
                'amount_to_pay'       => $calc['amount_to_pay'],
                'status'              => 'pending',
                'created_by'          => Auth::id(),
                'notes'               => $request->notes,
            ]);

            // SIMPAN RINCIAN PER BATCH (Anti-Ambiguitas)
            foreach ($calc['item_breakdown'] as $item) {
                $settlement->items()->create([
                    'consignment_item_id' => $item['batch_id'],
                    'quantity'            => $item['qty'],
                    'price'               => $item['price'],
                    'commission_rate'     => $item['commission_rate'],
                    'amount_to_pay'       => $item['to_pay'],
                ]);
            }

            return redirect()->route('admin.consignment.settlements.show', $settlement)
                ->with('success', "Settlement {$settlement->reference_no} berhasil dibuat.");
        });
    }

    /**
     * Detail settlement
     */
    public function show(ConsignmentSettlement $settlement)
    {
        $settlement->load(['consignor', 'createdBy', 'paidBy', 'items.consignmentItem.product']);

        // Kelompokkan item settlement berdasarkan produk untuk tampilan ringkas
        $soldItems = $settlement->items->groupBy(fn($item) => $item->consignmentItem->product_id);

        return view('admin.consignment.settlements.show', compact('settlement', 'soldItems'));
    }

    /**
     * Cetak PDF Settlement
     */
    public function printPdf(ConsignmentSettlement $settlement)
    {
        $settlement->load(['consignor', 'createdBy', 'paidBy', 'items.consignmentItem.product']);

        $soldItems = $settlement->items->groupBy(fn($item) => $item->consignmentItem->product_id);

        $pdf = Pdf::loadView('admin.consignment.settlements.pdf', compact('settlement', 'soldItems'));
        
        return $pdf->setPaper('a4', 'portrait')
                  ->stream("Settlement-{$settlement->reference_no}.pdf");
    }

    /**
     * Tandai settlement sudah dibayar
     */
    public function markAsPaid(Request $request, ConsignmentSettlement $settlement)
    {
        $request->validate([
            'payment_method'    => 'required|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        if ($settlement->status === 'paid') {
            return back()->with('error', 'Settlement ini sudah ditandai lunas sebelumnya.');
        }

        $settlement->update([
            'status'             => 'paid',
            'paid_at'            => now(),
            'payment_method'     => $request->payment_method,
            'payment_reference'  => $request->payment_reference,
            'paid_by'            => Auth::id(),
        ]);

        // Tandai batch terkait sebagai settled HANYA jika sudah benar-benar habis terjual
        foreach ($settlement->items as $sItem) {
            $batch = $sItem->consignmentItem;
            if ($batch->quantity_remaining <= 0) {
                $batch->update(['status' => 'settled']);
            }
        }

        return redirect()->route('admin.consignment.settlements.show', $settlement)
            ->with('success', "Settlement {$settlement->reference_no} telah ditandai LUNAS.");
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Hitung detail settlement untuk penitip dan periode tertentu.
     * Menggunakan alokasi FIFO per unit untuk sisa yang belum dibayar.
     */
    private function calculateSettlement(Consignor $consignor, Carbon $periodStart, Carbon $periodEnd): array
    {
        $totalQty    = 0;
        $totalSales  = 0;
        $totalCommission = 0;
        $itemBreakdown   = [];

        // Ambil ID produk konsinyasi milik penitip ini
        $productIds = $consignor->products()->where('is_consignment', true)->pluck('id');

        foreach ($productIds as $productId) {
            // 1. Hitung TOTAL qty terjual dalam periode ini
            $qtyInPeriod = (float) TransactionItem::whereHas('transaction', fn ($q) =>
                    $q->where('status', 'completed')
                        ->whereBetween('created_at', [$periodStart, $periodEnd])
                )
                ->where('product_id', $productId)
                ->sum('quantity');

            if ($qtyInPeriod <= 0) continue;

            // 2. Alokasikan qtyInPeriod tersebut ke batch-batch (FIFO) yang masih punya piutang
            $batches = ConsignmentItem::with('product')
                ->where('product_id', $productId)
                ->where('consignor_id', $consignor->id)
                ->whereIn('status', ['active', 'settled']) // Bisa saja sudah settled tapi ada penjualan baru (?) atau batch sebelumnya belum tuntas dibayar
                ->orderBy('received_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $remainingToAllocate = $qtyInPeriod;

            foreach ($batches as $batch) {
                $canSettle = $batch->quantity_to_settle; // Sisa terjual - pernah dibayar
                
                if ($canSettle <= 0) continue;

                $toSettle = min($canSettle, $remainingToAllocate);
                
                $price          = (float) ($batch->product->price ?? 0);
                $commissionRate = $batch->effective_commission_rate;
                $sales          = $toSettle * $price;
                $commission     = $sales * ($commissionRate / 100);
                $toPay          = $sales - $commission;

                $totalQty        += $toSettle;
                $totalSales      += $sales;
                $totalCommission += $commission;

                $itemBreakdown[] = [
                    'product'         => $batch->product,
                    'batch_id'        => $batch->id,
                    'qty'             => $toSettle,
                    'price'           => $price,
                    'sales'           => $sales,
                    'commission_rate' => $commissionRate,
                    'commission'      => $commission,
                    'to_pay'          => $toPay,
                ];

                $remainingToAllocate -= $toSettle;
                if ($remainingToAllocate <= 0) break;
            }
        }

        return [
            'total_sold_qty'     => $totalQty,
            'total_sales_amount' => $totalSales,
            'commission_amount'  => $totalCommission,
            'amount_to_pay'      => $totalSales - $totalCommission,
            'item_breakdown'     => $itemBreakdown,
        ];
    }
}
