<?php

use App\Models\User;
use App\Models\Role;
use App\Models\IdentityType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->identityType = IdentityType::forceCreate(['name' => 'KTP', 'label' => 'KTP']);

    // Create roles using custom model
    $this->roles = [
        'superadmin' => Role::forceCreate(['name' => 'Superadmin']),
        'manager' => Role::forceCreate(['name' => 'Manager']),
        'warehouse' => Role::forceCreate(['name' => 'Warehouse']),
        'cashier' => Role::forceCreate(['name' => 'Cashier']),
    ];

    $this->users = [];
    foreach ($this->roles as $key => $role) {
        $user = User::factory()->create([
            'role_id' => $role->id,
            'identity_type_id' => $this->identityType->id,
            'username' => "{$key}_user",
            'password' => bcrypt('password123'),
        ]);
        
        $this->users[$key] = $user;
    }
});

test('login redirects users based on roles', function () {
    // Assert Superadmin -> admin.dashboard
    $this->post('/login', [
        'username' => 'superadmin_user',
        'password' => 'password123',
    ])->assertRedirect(route('admin.dashboard'));

    // Assert Manager -> manager.dashboard
    $this->post('/login', [
        'username' => 'manager_user',
        'password' => 'password123',
    ])->assertRedirect(route('manager.dashboard'));

    // Assert Warehouse -> warehouse.dashboard
    $this->post('/login', [
        'username' => 'warehouse_user',
        'password' => 'password123',
    ])->assertRedirect(route('warehouse.dashboard'));

    // Assert Cashier -> pos.index
    $this->post('/login', [
        'username' => 'cashier_user',
        'password' => 'password123',
    ])->assertRedirect(route('pos.index'));
});

test('invalid credentials returns errors', function () {
    $this->post('/login', [
        'username' => 'superadmin_user',
        'password' => 'wrongpassword',
    ])->assertSessionHasErrors('username');
    
    $this->assertGuest();
});

test('logout destroys session and redirects', function () {
    $this->actingAs($this->users['cashier'])->post('/logout')
         ->assertRedirect('/');

    $this->assertGuest();
});

test('role middleware prevents unauthorized access', function () {
    // Cashier cannot access Admin Dashboard
    $this->actingAs($this->users['cashier'])->get(route('admin.dashboard'))
         ->assertForbidden();

    // Manager cannot access POS
    $this->actingAs($this->users['manager'])->get(route('pos.index'))
         ->assertForbidden();

    // Warehouse cannot access Manager Dashboard
    $this->actingAs($this->users['warehouse'])->get(route('manager.dashboard'))
         ->assertForbidden();
});
