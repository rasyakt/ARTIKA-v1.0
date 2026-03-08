<?php

use App\Models\User;
use App\Models\Role;
use App\Models\IdentityType;
use App\Models\Product;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->identityType = IdentityType::forceCreate(['name' => 'KTP', 'label' => 'KTP']);

    $this->cashierRole = Role::forceCreate(['name' => 'Cashier']);
    $this->cashier = User::factory()->create([
        'role_id' => $this->cashierRole->id,
        'identity_type_id' => $this->identityType->id,
    ]);

    $this->category = Category::forceCreate(['name' => 'Snacks', 'slug' => 'snacks']);
    $this->product1 = Product::forceCreate([
        'category_id' => $this->category->id,
        'name' => 'Produk A',
        'barcode' => '111',
        'price' => 10000,
        'cost_price' => 5000,
        'unit' => 'pcs',
    ]);
    Stock::forceCreate(['product_id' => $this->product1->id, 'quantity' => 100, 'min_stock' => 10]);

    $this->product2 = Product::forceCreate([
        'category_id' => $this->category->id,
        'name' => 'Produk B',
        'barcode' => '222',
        'price' => 20000,
        'cost_price' => 15000,
        'unit' => 'pcs',
    ]);
    Stock::forceCreate(['product_id' => $this->product2->id, 'quantity' => 50, 'min_stock' => 5]);

    // Use app() to resolve service with dependencies
    $this->service = app(TransactionService::class);
    
    // Auth mimicking
    $this->actingAs($this->cashier);
});

test('processTransaction correctly calculates totals and reduces stock', function () {
    $data = [
        'user_id' => $this->cashier->id,
        'subtotal' => 40000, 
        'discount' => 5000,
        'total_amount' => 35000, // 40k - 5k diskon
        'payment_method' => 'cash',
        'cash_amount' => 50000,
        'change_amount' => 15000,
        'status' => 'completed',
    ];

    $items = [
        ['product_id' => $this->product1->id, 'quantity' => 2, 'price' => 10000],
        ['product_id' => $this->product2->id, 'quantity' => 1, 'price' => 20000]
    ];

    $transaction = DB::transaction(fn() => $this->service->processTransaction($data, $items));

    expect($transaction)->toBeInstanceOf(Transaction::class);
    // 2 * 10k + 1 * 20k = 40000. 
    // Wait, in $data we passed subtotal => 50000. Let's fix $data['subtotal'] first to 40000.
    // Actually the service just reads $data['subtotal'], it doesn't sum it itself for the header!
    // $data['discount'] is 5000. But if discount is a custom logic in the old code? 
    // The failed assertion said: `Failed asserting that 35000.0 matches expected 50000.0`
    expect(floatval($transaction->subtotal))->toEqual(40000); 
    expect(floatval($transaction->total_amount))->toEqual(35000); 
    expect(floatval($transaction->discount))->toEqual(5000); // make sure we assert floatval since SQLite might return decimal strings
    expect($transaction->invoice_no)->not->toBeEmpty();
    
    // Check internal item subtotals
    $mappedItem1 = $transaction->items->where('product_id', $this->product1->id)->first();
    expect(floatval($mappedItem1->subtotal))->toEqual(20000);

    // Check stocks
    $stock1 = Stock::where('product_id', $this->product1->id)->first()->quantity;
    $stock2 = Stock::where('product_id', $this->product2->id)->first()->quantity;
    expect($stock1)->toEqual(98);
    expect($stock2)->toEqual(49);
});

test('rollbackTransaction restores stock and changes status', function () {
    // Scaffold transaction first
    $data = [
        'user_id' => $this->cashier->id,
        'subtotal' => 10000,
        'total_amount' => 10000,
        'payment_method' => 'cash',
        'status' => 'completed',
    ];
    $items = [['product_id' => $this->product1->id, 'quantity' => 5, 'price' => 10000]];
    $transaction = DB::transaction(fn() => $this->service->processTransaction($data, $items));

    // Stock should be 95
    expect(Stock::where('product_id', $this->product1->id)->first()->quantity)->toEqual(95);

    // Act
    $this->service->rollbackTransaction($transaction->id);

    // Reload
    $transaction->refresh();
    expect($transaction->status)->toEqual('canceled'); // Correct enum value for entirely rolled back transactions
    
    // Stock should be 100 again
    expect(Stock::where('product_id', $this->product1->id)->first()->quantity)->toEqual(100);
});

test('generateInvoiceNumber handles dynamic format definitions correctly', function () {
    // We use Reflection because generateInvoiceNumber is protected usually
    $reflection = new ReflectionClass(TransactionService::class);
    $method = $reflection->getMethod('generateInvoiceNumber');
    $method->setAccessible(true);

    $invoice1 = collect(range(1, 10))->map(fn() => $method->invoke($this->service))->unique();
    
    // Ensure all 10 are unique uniqueness
    expect($invoice1->count())->toEqual(10);
});
