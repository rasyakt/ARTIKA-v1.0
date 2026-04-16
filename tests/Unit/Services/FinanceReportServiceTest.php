<?php

use App\Models\User;
use App\Models\Role;
use App\Models\IdentityType;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\FinanceReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->identityType = IdentityType::forceCreate(['name' => 'KTP', 'label' => 'KTP']);

    $this->role = Role::forceCreate(['name' => 'Manager']);
    $this->user = User::factory()->create([
        'role_id' => $this->role->id,
        'identity_type_id' => $this->identityType->id,
    ]);

    $this->category = Category::forceCreate(['name' => 'Food', 'slug' => 'food']);
    $this->product = Product::forceCreate([
        'category_id' => $this->category->id,
        'name' => 'Burger',
        'barcode' => 'B001',
        'price' => 50000,
        'cost_price' => 30000,
        'unit' => 'pcs',
    ]);

    $this->service = app(FinanceReportService::class);
});

test('getFinancialSummary calculates Net Profit correctly', function () {
    // Scaffold 1 transaction (1 item)
    // Revenue: 50,000 | COGS: 30,000 | Gross Profit: 20,000
    $t = Transaction::forceCreate([
        'invoice_no' => 'INV-FIN-001',
        'user_id' => $this->user->id,
        'subtotal' => 50000,
        'total_amount' => 50000,
        'payment_method' => 'cash',
        'status' => 'completed',
        'created_at' => now(),
    ]);
    TransactionItem::forceCreate(['transaction_id' => $t->id, 'product_id' => $this->product->id, 'quantity' => 1, 'price' => 50000, 'subtotal' => 50000]);

    // Add 1 Expense of 5,000
    $expCat = ExpenseCategory::forceCreate(['name' => 'General']);
    Expense::forceCreate([
        'expense_category_id' => $expCat->id,
        'user_id' => $this->user->id,
        'amount' => 5000,
        'notes' => 'Listrik',
        'date' => now(),
    ]);

    $stats = $this->service->getFinancialSummary(now()->startOfDay(), now()->endOfDay());

    // Expected: 
    // Gross Revenue = 50,000
    // COGS = 30,000
    // Gross Profit = 20,000
    // Expense = 5,000
    // Net Profit = 15,000
    expect(floatval($stats['gross_revenue']))->toEqual(50000);
    expect(floatval($stats['cogs']))->toEqual(30000);
    expect(floatval($stats['gross_profit']))->toEqual(20000);
    expect(floatval($stats['total_expenses']))->toEqual(5000);
    expect(floatval($stats['net_profit']))->toEqual(15000);
});
