<?php

use App\Models\Transaction;
use App\Models\ReturnTransaction;
use App\Models\User;

test('it calculates total refunded correctly', function () {
    $user = User::factory()->create();
    $transaction = Transaction::forceCreate([
        'user_id' => $user->id,
        'invoice_no' => 'INV-001',
        'subtotal' => 1000,
        'total_amount' => 1000,
        'payment_method' => 'cash',
    ]);

    ReturnTransaction::forceCreate([
        'return_no' => 'RET-001',
        'transaction_id' => $transaction->id,
        'user_id' => $user->id,
        'total_refund' => 200,
        'items' => [['product_id' => 1, 'quantity' => 1]],
        'status' => 'approved',
    ]);

    ReturnTransaction::forceCreate([
        'return_no' => 'RET-002',
        'transaction_id' => $transaction->id,
        'user_id' => $user->id,
        'total_refund' => 300,
        'items' => [['product_id' => 1, 'quantity' => 1]],
        'status' => 'approved',
    ]);

    ReturnTransaction::forceCreate([
        'return_no' => 'RET-003',
        'transaction_id' => $transaction->id,
        'user_id' => $user->id,
        'total_refund' => 150,
        'items' => [['product_id' => 1, 'quantity' => 1]],
        'status' => 'pending', // Should not be included
    ]);

    expect($transaction->total_refunded)->toBe(500.0);
});

test('it identifies if fully returned', function () {
    $user = User::factory()->create();
    $transaction = Transaction::forceCreate([
        'user_id' => $user->id,
        'invoice_no' => 'INV-002',
        'subtotal' => 500,
        'total_amount' => 500,
        'status' => 'completed',
        'payment_method' => 'cash',
    ]);

    // Not fully returned yet
    expect($transaction->is_fully_returned)->toBeFalse();

    // Partial return
    ReturnTransaction::forceCreate([
        'return_no' => 'RET-004',
        'transaction_id' => $transaction->id,
        'user_id' => $user->id,
        'total_refund' => 200,
        'items' => [['product_id' => 1, 'quantity' => 1]],
        'status' => 'approved',
    ]);
    expect($transaction->fresh()->is_fully_returned)->toBeFalse();

    // Full return
    ReturnTransaction::forceCreate([
        'return_no' => 'RET-005',
        'transaction_id' => $transaction->id,
        'user_id' => $user->id,
        'total_refund' => 300,
        'items' => [['product_id' => 1, 'quantity' => 1]],
        'status' => 'approved',
    ]);
    expect($transaction->fresh()->is_fully_returned)->toBeTrue();
});

test('it calculates returned quantity for specific product', function () {
    $user = User::factory()->create();
    $transaction = Transaction::forceCreate([
        'user_id' => $user->id,
        'invoice_no' => 'INV-003',
        'subtotal' => 1000,
        'total_amount' => 1000,
        'payment_method' => 'cash',
    ]);

    ReturnTransaction::forceCreate([
        'return_no' => 'RET-006',
        'transaction_id' => $transaction->id,
        'user_id' => $user->id,
        'items' => [
            ['product_id' => 1, 'quantity' => 2],
            ['product_id' => 2, 'quantity' => 1],
        ],
        'total_refund' => 1000,
        'status' => 'approved',
    ]);

    ReturnTransaction::forceCreate([
        'return_no' => 'RET-007',
        'transaction_id' => $transaction->id,
        'user_id' => $user->id,
        'items' => [
            ['product_id' => 1, 'quantity' => 3],
        ],
        'total_refund' => 1000,
        'status' => 'approved',
    ]);

    ReturnTransaction::forceCreate([
        'return_no' => 'RET-008',
        'transaction_id' => $transaction->id,
        'user_id' => $user->id,
        'items' => [
            ['product_id' => 1, 'quantity' => 5],
        ],
        'total_refund' => 1000,
        'status' => 'pending', // Should not be counted
    ]);

    expect($transaction->getReturnedQuantity(1))->toBe(5);
    expect($transaction->getReturnedQuantity(2))->toBe(1);
    expect($transaction->getReturnedQuantity(3))->toBe(0);
});
