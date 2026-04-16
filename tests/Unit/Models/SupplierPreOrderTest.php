<?php

use App\Models\SupplierPreOrder;
use App\Models\Supplier;
use App\Models\User;

test('it creates a supplier pre order with a uuid', function () {
    $supplier = Supplier::forceCreate(['name' => 'Supp']);
    $user = User::factory()->create();

    $preOrder = SupplierPreOrder::create([
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
        'reference_number' => 'PO-TEST-001',
        'status' => 'pending',
        'expected_arrival_date' => now()->addDays(7),
        'total_amount' => 5000.00,
    ]);

    expect($preOrder->uuid)->not->toBeNull();
    expect($preOrder->reference_number)->toBe('PO-TEST-001');
    expect($preOrder->supplier_id)->toBe($supplier->id);
    expect($preOrder->user_id)->toBe($user->id);
    expect($preOrder->total_amount)->toEqual(5000.00);
});

test('it has corresponding relationships', function () {
    $supplier = Supplier::forceCreate(['name' => 'Supplier X', 'email' => 'supx@test.com', 'phone' => '1234', 'address' => 'Addr']);
    $user = User::factory()->create();

    $preOrder = SupplierPreOrder::create([
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
        'reference_number' => 'PO-TEST-002',
        'status' => 'pending',
    ]);

    expect($preOrder->supplier)->toBeInstanceOf(Supplier::class);
    expect($preOrder->user)->toBeInstanceOf(User::class);
    expect($preOrder->items)->toBeIterable();
});
