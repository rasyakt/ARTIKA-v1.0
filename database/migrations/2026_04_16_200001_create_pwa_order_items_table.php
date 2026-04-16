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
        Schema::create('pwa_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pwa_order_id')->constrained('pwa_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->string('product_name'); // snapshot at order time
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // snapshot at order time
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('pwa_order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pwa_order_items');
    }
};
