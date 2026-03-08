<?php

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\User;

test('it tracks quantity changes correctly', function () {
    $product = Product::forceCreate(['name' => 'Prod', 'price' => 10, 'cost_price' => 5, 'barcode' => '12399']);
    $user = User::factory()->create();

    $movement = StockMovement::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'type' => 'in',
        'quantity_before' => 10,
        'quantity_after' => 15,
        'quantity_change' => 5,
        'reason' => 'purchase',
        'reference' => 'PO-123',
    ]);

    expect($movement->product_id)->toBe($product->id);
    expect($movement->user_id)->toBe($user->id);
    expect($movement->type)->toBe('in');
    expect($movement->quantity_before)->toBe(10);
    expect($movement->quantity_after)->toBe(15);
    expect($movement->quantity_change)->toBe(5);
    expect($movement->reason)->toBe('purchase');
    expect($movement->reference)->toBe('PO-123');
});

test('it has product and user relationships', function () {
    $product = Product::forceCreate(['name' => 'Prod2', 'price' => 10, 'cost_price' => 5, 'barcode' => '12398']);
    $user = User::factory()->create();

    $movement = StockMovement::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'type' => 'out',
        'quantity_before' => 20,
        'quantity_after' => 18,
        'quantity_change' => -2,
        'reason' => 'sale',
    ]);

    expect($movement->product)->toBeInstanceOf(Product::class);
    expect($movement->user)->toBeInstanceOf(User::class);
});
