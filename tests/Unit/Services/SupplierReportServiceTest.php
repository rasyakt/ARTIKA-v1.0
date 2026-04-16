<?php

use App\Models\User;
use App\Models\Role;
use App\Models\IdentityType;
use App\Models\Supplier;
use App\Models\SupplierPreOrder;
use App\Services\SupplierReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->identityType = IdentityType::forceCreate(['name' => 'KTP', 'label' => 'KTP']);

    $this->managerRole = Role::forceCreate(['name' => 'Manager']);
    $this->manager = User::factory()->create([
        'role_id' => $this->managerRole->id,
        'identity_type_id' => $this->identityType->id,
    ]);

    $this->supplier = Supplier::forceCreate([
        'name' => 'PT Supplier Utama',
        'phone' => '08123456789',
        'address' => 'Jakarta'
    ]);

    $this->service = app(SupplierReportService::class);
});

test('getSummaryStats calculates order counts and total spend correctly', function () {
    // 1 Pending
    SupplierPreOrder::forceCreate([
        'uuid' => Str::uuid(),
        'reference_number' => 'PO-001',
        'supplier_id' => $this->supplier->id,
        'user_id' => $this->manager->id,
        'total_amount' => 500000,
        'status' => 'pending',
        'created_at' => now(),
    ]);

    // 1 Received
    SupplierPreOrder::forceCreate([
        'uuid' => Str::uuid(),
        'reference_number' => 'PO-002',
        'supplier_id' => $this->supplier->id,
        'user_id' => $this->manager->id,
        'total_amount' => 200000,
        'status' => 'received',
        'created_at' => now(),
    ]);

    $stats = $this->service->getSummaryStats(now()->startOfDay(), now()->endOfDay());

    expect($stats['total_orders'])->toEqual(2);
    expect($stats['pending_orders'])->toEqual(1);
    expect($stats['received_orders'])->toEqual(1);
    expect($stats['cancelled_orders'])->toEqual(0);
    expect(floatval($stats['total_spend']))->toEqual(200000); // only counts received
});

test('getSuppliersPerformance ranks suppliers by total spend', function () {
    SupplierPreOrder::forceCreate([
        'uuid' => Str::uuid(),
        'reference_number' => 'PO-003',
        'supplier_id' => $this->supplier->id,
        'user_id' => $this->manager->id,
        'total_amount' => 1000000,
        'status' => 'received',
        'created_at' => now(),
    ]);

    $performance = $this->service->getSuppliersPerformance(now()->startOfDay(), now()->endOfDay());

    expect($performance)->toHaveCount(1);
    expect($performance->first()->name)->toEqual('PT Supplier Utama');
    expect(floatval($performance->first()->pre_orders_sum_total_amount))->toEqual(1000000);
});
