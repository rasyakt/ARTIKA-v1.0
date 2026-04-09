<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignor_id')->constrained('consignors')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // Stok yang diterima dari penitip (batch penerimaan)
            $table->decimal('quantity_received', 10, 2)->default(0)->comment('Jumlah unit diterima dari penitip');
            $table->decimal('quantity_returned', 10, 2)->default(0)->comment('Jumlah unit dikembalikan ke penitip');

            // Harga modal penitip (opsional, untuk info saja)
            $table->decimal('cost_to_consignor', 12, 2)->nullable()->comment('Harga modal penitip per unit (opsional)');

            // Komisi bisa di-override per barang (jika null, pakai dari consignor)
            $table->decimal('commission_rate', 5, 2)->nullable()->comment('Override komisi per barang. NULL = pakai tarif penitip');

            $table->date('received_at')->comment('Tanggal barang diterima dari penitip');
            $table->date('expiry_date')->nullable()->comment('Tanggal kedaluwarsa barang (jika ada)');

            // Status:
            // active   = sedang berjalan, bisa dijual
            // settled  = sudah disetelkan/dibayarkan ke penitip
            // returned = dikembalikan ke penitip
            $table->enum('status', ['active', 'settled', 'returned'])->default('active');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_items');
    }
};
