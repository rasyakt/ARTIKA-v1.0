<?php

use App\Models\User;
use App\Models\Role;
use App\Models\IdentityType;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\HeldTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup roles
    $this->cashierRole = Role::forceCreate(['name' => 'Cashier']);
    $this->adminRole = Role::forceCreate(['name' => 'Admin']);
    
    // Setup identity type
    $this->identityType = IdentityType::forceCreate(['name' => 'KTP', 'label' => 'KTP']);

    // Create cashier user
    $this->cashier = User::factory()->create([
        'role_id' => $this->cashierRole->id,
        'identity_type_id' => $this->identityType->id,
    ]);

    // Create a product with stock
    $this->category = Category::forceCreate(['name' => 'Snacks', 'slug' => 'snacks']);
    $this->product = Product::forceCreate([
        'category_id' => $this->category->id,
        'name' => 'Chitato',
        'barcode' => '123456',
        'price' => 5000,
        'cost_price' => 3000,
        'unit' => 'pcs',
    ]);
    
    Stock::forceCreate([
        'product_id' => $this->product->id,
        'quantity' => 100,
        'min_stock' => 10,
    ]);

    // Create payment method
    PaymentMethod::forceCreate([
        'name' => 'Cash',
        'slug' => 'cash',
        'is_active' => true,
        'proof_requirement' => 'none',
    ]);
});

test('pos page is accessible by cashier', function () {
    $response = $this->actingAs($this->cashier)->get('/pos');
    $response->assertStatus(200);
});

test('checkout reduces stock and creates transaction', function () {
    $payload = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'quantity' => 2,
                'price' => 5000,
            ]
        ],
        'subtotal' => 10000,
        'discount' => 0,
        'total_amount' => 10000,
        'payment_method' => 'cash',
        'cash_amount' => 10000,
        'change_amount' => 0,
    ];

    $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', $payload);

    $response->assertStatus(200)
             ->assertJson(['success' => true]);

    // Assert transaction created
    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->cashier->id,
        'total_amount' => 10000,
        'status' => 'completed',
    ]);

    // Assert stock reduced
    $this->assertDatabaseHas('stocks', [
        'product_id' => $this->product->id,
        'quantity' => 98,
    ]);

    // Assert audit log
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $this->cashier->id,
        'action' => 'transaction_created',
    ]);
});

test('cashier can hold and resume a transaction', function () {
    // Hold Transaction
    $payloadParams = [
        'items' => [['product_id' => $this->product->id, 'quantity' => 1, 'price' => 5000]],
        'subtotal' => 5000,
        'total' => 5000,
        'note' => 'Customer forgot wallet',
    ];

    $this->actingAs($this->cashier)->postJson('/pos/hold', $payloadParams)
         ->assertStatus(200)
         ->assertJson(['success' => true]);

    $this->assertDatabaseHas('held_transactions', [
        'user_id' => $this->cashier->id,
        'subtotal' => 5000,
        'total' => 5000,
    ]);

    $held = HeldTransaction::first();

    $this->actingAs($this->cashier)->getJson("/pos/held/{$held->id}/resume")
         ->assertStatus(200)
         ->assertJsonPath('data.total', '5000.00');

    // Assert it was deleted from hold
    $this->assertDatabaseMissing('held_transactions', [
        'id' => $held->id,
    ]);
});
