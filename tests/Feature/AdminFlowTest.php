<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\IdentityType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->identityType = IdentityType::forceCreate(['name' => 'KTP', 'label' => 'KTP']);

    $this->roles = [
        'superadmin' => Role::forceCreate(['name' => 'Superadmin']),
        'manager' => Role::forceCreate(['name' => 'Manager']),
    ];

    $this->users = [];
    foreach ($this->roles as $key => $role) {
        $this->users[$key] = clone User::factory()->create([
            'role_id' => $role->id,
            'identity_type_id' => $this->identityType->id,
        ]);
    }
});

test('admin can access products page', function () {
    $this->actingAs($this->users['superadmin'])->get(route('admin.products'))
         ->assertStatus(200);
});

test('admin can create a category', function () {
    $payload = [
        'name' => 'Electronics',
        'slug' => 'electronics',
    ];

    $response = $this->actingAs($this->users['superadmin'])->post(route('admin.categories.store'), $payload);
    
    // Adjust based on your category store response type (could be redirect or json)
    // Most typical laravel back() redirect is 302
    $response->assertStatus(302);

    $this->assertDatabaseHas('categories', [
        'name' => 'Electronics'
    ]);
});

test('manager can view reports but not create users', function () {
    // Manager viewing their own dashboard
    $this->actingAs($this->users['manager'])->get(route('manager.dashboard'))
         ->assertStatus(200);
         
    // Assuming manager can view index of something, like users, but fails on store
    // Ensure manager cannot access the admin user creation endpoint, yielding forbidden 403
    $this->actingAs($this->users['manager'])->post(route('admin.users.store'), [
        'name' => 'Fake User'
    ])->assertForbidden();
});
