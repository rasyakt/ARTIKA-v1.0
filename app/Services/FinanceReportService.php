<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ReturnTransaction;
use App\Models\Expense;
use App\Models\SupplierPurchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceReportService
{
    /**
     * Get financial summary for a given date range.
     */
    public function getFinancialSummary($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        // 1. Gross Revenue (Total Sales) - Split into Retail and Consignment
        $salesData = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->select(
                DB::raw('SUM(CASE WHEN products.is_consignment = 1 THEN transaction_items.quantity * transaction_items.price ELSE 0 END) as consignment_revenue'),
                DB::raw('SUM(CASE WHEN products.is_consignment = 0 THEN transaction_items.quantity * transaction_items.price ELSE 0 END) as retail_revenue'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.price) as total_revenue')
            )
            ->first();

        $grossRevenue = (float) ($salesData->total_revenue ?? 0);
        $consignmentRevenue = (float) ($salesData->consignment_revenue ?? 0);
        $retailRevenue = (float) ($salesData->retail_revenue ?? 0);

        // 2. COGS (Cost of Goods Sold)
        // Retail COGS: items.quantity * products.cost_price
        $retailCogs = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->where('products.is_consignment', false)
            ->select(DB::raw('SUM(transaction_items.quantity * products.cost_price) as total_cogs'))
            ->value('total_cogs') ?? 0;

        // Consignment "Cost": items.quantity * (price - commission)
        // This is the amount we owe the consignor.
        // We calculate this by joining with the consignment logic or using the product's snapshot if commission is fixed.
        // For simplicity and performance, we'll use the product's cost_price which we keep in sync for consignment items.
        $consignmentCost = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->where('products.is_consignment', true)
            ->select(DB::raw('SUM(transaction_items.quantity * products.cost_price) as total_cost'))
            ->value('total_cost') ?? 0;

        $cogs = $retailCogs + $consignmentCost;

        // 3. Consignment Commission (Income from consignment)
        $consignmentCommission = $consignmentRevenue - $consignmentCost;

        // 4. Gross Profit
        // Gross Profit = (Retail Revenue - Retail Cogs) + Consignment Commission
        $grossProfit = ($retailRevenue - $retailCogs) + $consignmentCommission;

        // 5. Returns & Refunds
        $totalReturns = ReturnTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_refund');

        // 6. Operating Expenses
        $totalExpenses = Expense::whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        // 7. Stock Procurement
        $totalProcurement = SupplierPurchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('total_price');

        // 8. Net Profit
        $netProfit = $grossProfit - $totalReturns - $totalExpenses;

        // 9. Profit Margin
        $profitMargin = $grossRevenue > 0 ? ($netProfit / $grossRevenue) * 100 : 0;

        return [
            'gross_revenue' => $grossRevenue,
            'retail_revenue' => $retailRevenue,
            'consignment_revenue' => $consignmentRevenue,
            'consignment_commission' => $consignmentCommission,
            'cogs' => $cogs,
            'retail_cogs' => $retailCogs,
            'consignment_cost' => $consignmentCost,
            'gross_profit' => $grossProfit,
            'total_returns' => $totalReturns,
            'total_expenses' => $totalExpenses,
            'total_procurement' => $totalProcurement,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
        ];
    }

    /**
     * Get financial data grouped by day for the period.
     */
    public function getDailyFinanceData($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $dailyData = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->select(
                DB::raw('DATE(transactions.created_at) as date'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.price) as revenue'),
                DB::raw('SUM(CASE WHEN products.is_consignment = 1 THEN transaction_items.quantity * transaction_items.price ELSE 0 END) as consignment_revenue'),
                DB::raw('SUM(CASE WHEN products.is_consignment = 1 THEN transaction_items.quantity * products.cost_price ELSE 0 END) as consignment_cost'),
                DB::raw('SUM(CASE WHEN products.is_consignment = 0 THEN transaction_items.quantity * products.cost_price ELSE 0 END) as retail_cogs')
            )
            ->groupBy(DB::raw('DATE(transactions.created_at)'))
            ->get()
            ->keyBy('date');

        // 3. Expenses per day
        $dailyExpenses = Expense::whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('date'),
                DB::raw('SUM(amount) as cost')
            )
            ->groupBy('date')
            ->get()
            ->keyBy(function ($item) {
                // Ensure key is Y-m-d string regardless of model casting
                return is_string($item->date) ? substr($item->date, 0, 10) : $item->date->format('Y-m-d');
            });

        // 4. Procurement per day
        $dailyProcurement = SupplierPurchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->select(
                DB::raw('purchase_date'),
                DB::raw('SUM(total_price) as cost')
            )
            ->groupBy('purchase_date')
            ->get()
            ->keyBy(function ($item) {
                return is_string($item->purchase_date) ? substr($item->purchase_date, 0, 10) : $item->purchase_date->format('Y-m-d');
            });

        $result = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $row = $dailyData->get($dateStr);
            
            $revenue = (float) ($row->revenue ?? 0);
            $consignmentRevenue = (float) ($row->consignment_revenue ?? 0);
            $consignmentCost = (float) ($row->consignment_cost ?? 0);
            $retailCogs = (float) ($row->retail_cogs ?? 0);
            
            $consignmentCommission = $consignmentRevenue - $consignmentCost;
            $grossProfit = ($revenue - $consignmentRevenue - $retailCogs) + $consignmentCommission;
            
            $expense = floatval($dailyExpenses->get($dateStr)->cost ?? 0);
            $procurement = floatval($dailyProcurement->get($dateStr)->cost ?? 0);

            $result[] = [
                'date' => $dateStr,
                'revenue' => $revenue,
                'cogs' => $retailCogs + $consignmentCost,
                'consignment_commission' => $consignmentCommission,
                'expenses' => $expense,
                'procurement' => $procurement,
                'profit' => $grossProfit - $expense, // This already handles returns? No, returns are global in summary usually. 
                                                     // But for daily data we'll stick to simple profit.
            ];
            $currentDate->addDay();
        }

        return $result;
    }
}
