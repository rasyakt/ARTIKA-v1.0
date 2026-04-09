<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignment_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignor_id')->constrained('consignors')->onDelete('cascade');
            $table->string('reference_no')->unique()->comment('Nomor referensi settlement, mis: SET-2026-001');

            // Periode yang dihitung
            $table->date('period_start');
            $table->date('period_end');

            // Ringkasan keuangan
            $table->decimal('total_sold_qty', 10, 2)->default(0)->comment('Total unit terjual dalam periode');
            $table->decimal('total_sales_amount', 14, 2)->default(0)->comment('Total nilai penjualan (qty × harga jual)');
            $table->decimal('commission_amount', 14, 2)->default(0)->comment('Bagian komisi toko');
            $table->decimal('amount_to_pay', 14, 2)->default(0)->comment('Jumlah yang harus dibayar ke penitip');

            // Status pembayaran
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable()->comment('Cash, Transfer, dll');
            $table->string('payment_reference')->nullable()->comment('No. bukti transfer / kwitansi');

            $table->foreignId('created_by')->constrained('users')->comment('Admin yang membuat settlement');
            $table->foreignId('paid_by')->nullable()->constrained('users')->comment('Admin yang menandai lunas');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_settlements');
    }
};
