<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            // Info Rekening Bank
            $table->string('bank_name')->nullable()->comment('Nama bank, mis: BCA, BRI, Mandiri');
            $table->string('bank_account')->nullable()->comment('Nomor rekening');
            $table->string('bank_holder')->nullable()->comment('Nama pemilik rekening');

            // Tarif komisi default untuk penitip ini (% yang diambil TOKO dari hasil penjualan)
            // Contoh: commission_rate = 20 artinya toko ambil 20%, penitip dapat 80%
            $table->decimal('commission_rate', 5, 2)->default(0.00)->comment('% komisi toko dari hasil penjualan');

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignors');
    }
};
