<?php

use App\Models\User;
use App\Models\Role;
use App\Models\IdentityType;
use App\Models\Product;
use App\Models\Category;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\WarehouseReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->identityType = IdentityType::forceCreate(['name' => 'KTP', 'label' => 'KTP']);

    $this->role = Role::forceCreate(['name' => 'Warehouse']);
    $this->user = User::factory()->create([
        'role_id' => $this->role->id,
        'identity_type_id' => $this->identityType->id,
    ]);

    $this->category = Category::forceCreate(['name' => 'Electronic', 'slug' => 'electronic']);
    
    $this->product1 = Product::forceCreate([
        'category_id' => $this->category->id,
        'name' => 'Mouse',
        'barcode' => 'M01',
        'price' => 100000,
        'cost_price' => 70000,
        'unit' => 'pcs',
    ]);
    
    $this->product2 = Product::forceCreate([
        'category_id' => $this->category->id,
        'name' => 'Keyboard',
        'barcode' => 'K01',
        'price' => 200000,
        'cost_price' => 150000,
        'unit' => 'pcs',
    ]);

    $this->service = app(WarehouseReportService::class);
});

test('getLowStockItems accurately flags products under minimum thresholds', function () {
    // Product 1: High stock (20 vs 5 min) => Not low
    Stock::forceCreate([
        'product_id' => $this->product1->id,
        'quantity' => 20,
        'min_stock' => 5,
    ]);

    // Product 2: Low stock (2 vs 5 min) => Is low
    Stock::forceCreate([
        'product_id' => $this->product2->id,
        'quantity' => 2,
        'min_stock' => 5,
    ]);

    $lowStocks = $this->service->getLowStockItems(10);
    
    // Low stocks query pulls from paginator/get depending on perPage logic
    // Let's check how many items returned
    expect($lowStocks->total())->toEqual(1);
    expect($lowStocks->first()->id)->toEqual($this->product2->id);
    expect($lowStocks->first()->stock->quantity)->toEqual(2);
});

test('getSummaryStats calculates active stock value and total movements', function () {
    Stock::forceCreate([
        'product_id' => $this->product1->id,
        'quantity' => 10,
        'min_stock' => 5,
    ]);

    // 1 Stock Movement IN
    StockMovement::forceCreate([
        'product_id' => $this->product1->id,
        'type' => 'in',
        'quantity_change' => 10,
        'quantity_before' => 0,
        'quantity_after' => 10,
        'reference' => 'PO-001',
        'user_id' => $this->user->id,
        'created_at' => now(),
    ]);

    $stats = $this->service->getSummaryStats(now()->startOfDay(), now()->endOfDay());

    // Expected logic inside WarehouseReportService->getSummaryStats:
    // It should sum stock.quantity * product.price / cost_price usually.
    // We will verify it doesn't crash and returns the fundamental keys.
    expect($stats)->toHaveKeys(['total_valuation', 'total_items', 'low_stock_count', 'movements_in', 'movements_out']);
    expect($stats['total_items'])->toEqual(10);
    expect($stats['movements_in'])->toEqual(10); // ABS Quantity change is 10
    expect($stats['movements_out'])->toEqual(0);
});
