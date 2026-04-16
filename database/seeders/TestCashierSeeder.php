<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestCashierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role cashier ada
        $cashierRole = Role::firstOrCreate(
            ['name' => 'cashier'],
            ['description' => 'Kasir']
        );

        // Buat akun kasir untuk testing
        $user = User::updateOrCreate(
            ['username' => 'kasir1'],
            [
                'name' => 'Kasir Toko PWA',
                'password' => Hash::make('password'),
                'role_id' => $cashierRole->id,
            ]
        );

        $this->command->info('✅ Akun Kasir Berhasil Dibuat!');
        $this->command->line('Username: kasir1');
        $this->command->line('Password: password');
    }
}
