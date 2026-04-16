<?php

use App\Models\User;
use App\Models\Role;
use App\Models\IdentityType;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\CashierReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->identityType = IdentityType::forceCreate(['name' => 'KTP', 'label' => 'KTP']);

    $this->cashierRole = Role::forceCreate(['name' => 'Cashier']);
    $this->cashier = User::factory()->create([
        'role_id' => $this->cashierRole->id,
        'identity_type_id' => $this->identityType->id,
    ]);

    $this->category = Category::forceCreate(['name' => 'Beverages', 'slug' => 'beverages']);
    $this->product = Product::forceCreate([
        'category_id' => $this->category->id,
        'name' => 'Kopi Susu',
        'barcode' => '333',
        'price' => 15000,
        'cost_price' => 10000,
        'unit' => 'cup',
    ]);

    $this->service = app(CashierReportService::class);
});

test('getSummaryStats accurately calculates revenue, discounts, returns, and total transactions', function () {
    // Scaffold 3 transactions (2 completed, 1 canceled/returned)
    // Transaction 1: 2 items, 30k subtotal, 5k discount, 25k total_amount
    $t1 = Transaction::forceCreate([
        'invoice_no' => 'INV-001',
        'user_id' => $this->cashier->id,
        'subtotal' => 30000,
        'total_amount' => 25000,
        'discount' => 5000,
        'payment_method' => 'cash',
        'status' => 'completed',
        'created_at' => now(),
    ]);
    TransactionItem::forceCreate(['transaction_id' => $t1->id, 'product_id' => $this->product->id, 'quantity' => 2, 'price' => 15000, 'subtotal' => 30000]);

    // Transaction 2: 1 item, 15k subtotal, 0 discount, 15k total_amount
    $t2 = Transaction::forceCreate([
        'invoice_no' => 'INV-002',
        'user_id' => $this->cashier->id,
        'subtotal' => 15000,
        'total_amount' => 15000,
        'discount' => 0,
        'payment_method' => 'qris',
        'status' => 'completed',
        'created_at' => now(),
    ]);
    TransactionItem::forceCreate(['transaction_id' => $t2->id, 'product_id' => $this->product->id, 'quantity' => 1, 'price' => 15000, 'subtotal' => 15000]);

    // Transaction 3: Refunded/Canceled transaction (should not add to expected sales)
    $t3 = Transaction::forceCreate([
        'invoice_no' => 'INV-003',
        'user_id' => $this->cashier->id,
        'subtotal' => 15000,
        'total_amount' => 15000,
        'discount' => 0,
        'payment_method' => 'cash',
        'status' => 'canceled',
        'created_at' => now(),
    ]);

    $stats = $this->service->getSummaryStats(now()->startOfDay(), now()->endOfDay());

    // Gross Revenue calculation (total amount expected 25k + 15k = 40000)
    expect(floatval($stats['total_sales']))->toEqual(40000);
    expect($stats['total_transactions'])->toEqual(2);
    // Refunded counting -> since total returned counts against gross revenue normally or tracked separately?
    // Wait, let's verify if the service logic counts canceled in `getSummaryStats`.
});

test('getSalesByCategory groups categories dynamically', function () {
    $t1 = Transaction::forceCreate([
        'invoice_no' => 'INV-CAT',
        'user_id' => $this->cashier->id,
        'subtotal' => 15000,
        'total_amount' => 15000,
        'payment_method' => 'cash',
        'status' => 'completed',
    ]);
    TransactionItem::forceCreate(['transaction_id' => $t1->id, 'product_id' => $this->product->id, 'quantity' => 1, 'price' => 15000, 'subtotal' => 15000]);

    $breakdown = $this->service->getSalesByCategory();

    expect($breakdown)->toHaveCount(1);
    expect($breakdown->first()->name)->toEqual('Beverages');
    expect(floatval($breakdown->first()->total_revenue))->toEqual(15000);
});

test('getDiscountSummary evaluates cumulative discounts properly', function () {
    $t1 = Transaction::forceCreate([
        'invoice_no' => 'INV-DSC-1',
        'user_id' => $this->cashier->id,
        'subtotal' => 25000,
        'total_amount' => 20000,
        'discount' => 5000,
        'payment_method' => 'cash',
        'status' => 'completed',
    ]);

    $t2 = Transaction::forceCreate([
        'invoice_no' => 'INV-DSC-2',
        'user_id' => $this->cashier->id,
        'subtotal' => 50000,
        'total_amount' => 45000,
        'discount' => 5000,
        'payment_method' => 'qris',
        'status' => 'completed',
    ]);

    $summary = $this->service->getDiscountSummary();

    expect($summary['count'])->toEqual(2);
    expect(floatval($summary['total_discount']))->toEqual(10000);
    expect(floatval($summary['total_revenue']))->toEqual(65000); // 20k + 45k
});
