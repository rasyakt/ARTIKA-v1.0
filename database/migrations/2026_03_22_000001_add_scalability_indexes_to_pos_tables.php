<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * SCALABILITY INDEXES — POS Performance
 *
 * Adds critical indexes missing from the original schema:
 * 1. FULLTEXT index on products(name, barcode)       → fast product search
 * 2. Regular index on products(is_favorite)           → fast favorite query
 * 3. Composite index on transactions(user_id, created_at) → fast history per cashier per date
 */
return new class extends Migration {
    public function up(): void
    {
        // 1. FULLTEXT index for fast product name + barcode search
        //    Replaces slow LIKE '%keyword%' full table scan
        DB::statement('ALTER TABLE products ADD FULLTEXT INDEX idx_products_fulltext (name, barcode)');

        // 2. Index for is_favorite — speeds up quick button retrieval
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_favorite', 'idx_products_is_favorite');
        });

        // 3. Composite index: history queries always filter by user_id AND created_at
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_transactions_user_date');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products DROP INDEX idx_products_fulltext');

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_is_favorite');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_user_date');
        });
    }
};
