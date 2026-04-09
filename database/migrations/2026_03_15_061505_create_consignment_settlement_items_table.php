<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consignment_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('settlement_id')->constrained('consignment_settlements')->onDelete('cascade');
            $table->foreignId('consignment_item_id')->constrained('consignment_items');
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 14, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('amount_to_pay', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_settlement_items');
    }
};
