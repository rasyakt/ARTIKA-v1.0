<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consignor;
use App\Models\ConsignmentItem;
use App\Models\ConsignmentSettlement;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ConsignmentReportController extends Controller
{
    public function index(Request $request)
    {
        $period    = $request->input('period', 'month');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        [$startDate, $endDate] = $this->resolveDates($period, $startDate, $endDate);

        $consignors = Consignor::orderBy('name')->get();
        $filterConsignorId = $request->input('consignor_id');

        // ── Summary stats ──────────────────────────────────────────────────
        $summary = $this->getSummary($startDate, $endDate, $filterConsignorId);

        // ── Per-penitip breakdown ──────────────────────────────────────────
        $consignorStats = $this->getConsignorStats($startDate, $endDate, $filterConsignorId);

        // ── Top selling consignment products ──────────────────────────────
        $topProducts = $this->getTopProducts($startDate, $endDate, $filterConsignorId);

        // ── Recent settlements ─────────────────────────────────────────────
        $recentSettlements = ConsignmentSettlement::with('consignor')
            ->when($filterConsignorId, fn ($q) => $q->where('consignor_id', $filterConsignorId))
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.reports.consignment', compact(
            'startDate', 'endDate', 'period', 'consignors',
            'filterConsignorId', 'summary', 'consignorStats',
            'topProducts', 'recentSettlements'
        ));
    }

    public function export(Request $request)
    {
        $period    = $request->input('period', 'month');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        [$startDate, $endDate] = $this->resolveDates($period, $startDate, $endDate);

        $filterConsignorId = $request->input('consignor_id');
        $summary           = $this->getSummary($startDate, $endDate, $filterConsignorId);
        $consignorStats    = $this->getConsignorStats($startDate, $endDate, $filterConsignorId);
        $topProducts       = $this->getTopProducts($startDate, $endDate, $filterConsignorId);

        $filename = 'laporan-konsinyasi-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';
        $headers  = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
        ];

        $callback = function () use ($startDate, $endDate, $summary, $consignorStats, $topProducts) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

            $d = ';';
            fputcsv($file, ['LAPORAN KONSINYASI', $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y')], $d);
            fputcsv($file, [], $d);

            fputcsv($file, ['RINGKASAN'], $d);
            fputcsv($file, ['Total Terjual (unit)',    $summary['total_qty']], $d);
            fputcsv($file, ['Total Penjualan',         'Rp ' . number_format($summary['total_sales'], 0, ',', '.')], $d);
            fputcsv($file, ['Komisi Toko',             'Rp ' . number_format($summary['total_commission'], 0, ',', '.')], $d);
            fputcsv($file, ['Total Dibayarkan',        'Rp ' . number_format($summary['total_to_pay'], 0, ',', '.')], $d);
            fputcsv($file, ['Pending Settlement',      'Rp ' . number_format($summary['pending_pay'], 0, ',', '.')], $d);
            fputcsv($file, [], $d);

            fputcsv($file, ['PER PENITIP'], $d);
            fputcsv($file, ['Penitip', 'Qty Terjual', 'Total Penjualan', 'Komisi Toko', 'Dibayarkan', 'Status'], $d);
            foreach ($consignorStats as $stat) {
                fputcsv($file, [
                    $stat['consignor']->name,
                    $stat['qty'],
                    'Rp ' . number_format($stat['sales'], 0, ',', '.'),
                    'Rp ' . number_format($stat['commission'], 0, ',', '.'),
                    'Rp ' . number_format($stat['to_pay'], 0, ',', '.'),
                ], $d);
            }
            fputcsv($file, [], $d);

            fputcsv($file, ['PRODUK TERLARIS (KONSINYASI)'], $d);
            fputcsv($file, ['Produk', 'Penitip', 'Qty', 'Penjualan'], $d);
            foreach ($topProducts as $p) {
                fputcsv($file, [
                    $p->product_name,
                    $p->consignor_name,
                    $p->total_qty,
                    'Rp ' . number_format($p->total_sales, 0, ',', '.'),
                ], $d);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function resolveDates($period, $startDate, $endDate): array
    {
        if (!$startDate || !$endDate) {
            switch ($period) {
                case 'today':
                    return [Carbon::today(), Carbon::today()];
                case 'week':
                    return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
                case 'year':
                    return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
                default: // month
                    return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            }
        }
        return [Carbon::parse($startDate), Carbon::parse($endDate)];
    }

    private function getSummary(Carbon $start, Carbon $end, ?int $consignorId): array
    {
        $query = TransactionItem::query()
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('products.is_consignment', true)
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$start->startOfDay(), $end->endOfDay()]);

        if ($consignorId) {
            $query->where('products.consignor_id', $consignorId);
        }

        $raw = $query->selectRaw('
            SUM(transaction_items.quantity) as total_qty,
            SUM(transaction_items.quantity * transaction_items.price) as total_sales
        ')->first();

        $totalSales      = (float) ($raw->total_sales ?? 0);
        $totalQty        = (float) ($raw->total_qty ?? 0);

        // Estimasi komisi (rata-rata dari tarif penitip; detail di consignor stats)
        $totalCommission = 0;
        foreach ($this->getConsignorStats($start, $end, $consignorId) as $stat) {
            $totalCommission += $stat['commission'];
        }

        // 1. Ambil nilai pembayaran dari dokumen settlement yang masih berstatus 'pending' (draft)
        $documentedPendingPay = (float) ConsignmentSettlement::where('status', 'pending')
            ->when($consignorId, fn ($q) => $q->where('consignor_id', $consignorId))
            ->sum('amount_to_pay');

        // 2. Ambil nilai "Hutang Berjalan" = barang yang sudah laku di POS tapi belum dibuatkan dokumen settlement-nya sama sekali
        // Kita loop semua barang konsinyasi yang aktif/terjual milik penitip tsb
        $undocumentedPendingPay = 0;
        $activeConsignmentItems = ConsignmentItem::when($consignorId, fn($q) => $q->where('consignor_id', $consignorId))
            ->get();
            
        foreach ($activeConsignmentItems as $item) {
            $undocumentedPendingPay += $item->unsettled_amount;
        }

        return [
            'total_qty'        => $totalQty,
            'total_sales'      => $totalSales,
            'total_commission' => $totalCommission,
            'total_to_pay'     => $totalSales - $totalCommission,
            'pending_pay'      => $documentedPendingPay + $undocumentedPendingPay,
        ];
    }

    private function getConsignorStats(Carbon $start, Carbon $end, ?int $consignorId): array
    {
        $consignors = Consignor::when($consignorId, fn ($q) => $q->where('id', $consignorId))->get();
        $stats = [];

        foreach ($consignors as $consignor) {
            $products = $consignor->products()->where('is_consignment', true)->pluck('id');
            if ($products->isEmpty()) continue;

            $raw = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.status', 'completed')
                ->whereBetween('transactions.created_at', [$start->startOfDay(), $end->endOfDay()])
                ->whereIn('transaction_items.product_id', $products)
                ->selectRaw('SUM(quantity) as qty, SUM(quantity * price) as sales')
                ->first();

            $qty   = (float) ($raw->qty ?? 0);
            $sales = (float) ($raw->sales ?? 0);

            if ($qty <= 0) continue;

            $rate       = (float) $consignor->commission_rate;
            $commission = $sales * ($rate / 100);

            $stats[] = [
                'consignor'  => $consignor,
                'qty'        => $qty,
                'sales'      => $sales,
                'rate'       => $rate,
                'commission' => $commission,
                'to_pay'     => $sales - $commission,
            ];
        }

        return $stats;
    }

    private function getTopProducts(Carbon $start, Carbon $end, ?int $consignorId)
    {
        return TransactionItem::join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('consignors', 'products.consignor_id', '=', 'consignors.id')
            ->where('products.is_consignment', true)
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$start->startOfDay(), $end->endOfDay()])
            ->when($consignorId, fn ($q) => $q->where('products.consignor_id', $consignorId))
            ->selectRaw('
                products.name as product_name,
                consignors.name as consignor_name,
                SUM(transaction_items.quantity) as total_qty,
                SUM(transaction_items.quantity * transaction_items.price) as total_sales
            ')
            ->groupBy('products.id', 'products.name', 'consignors.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
    }
}
