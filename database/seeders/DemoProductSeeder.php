<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Kategori
        $categories = [
            ['name' => 'Makanan Ringan', 'slug' => 'makanan-ringan'],
            ['name' => 'Minuman', 'slug' => 'minuman'],
            ['name' => 'Alat Tulis', 'slug' => 'alat-tulis'],
            ['name' => 'Kebutuhan Harian', 'slug' => 'kebutuhan-harian'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], ['name' => $cat['name']]);
        }

        $catMakanan = Category::where('slug', 'makanan-ringan')->first();
        $catMinuman = Category::where('slug', 'minuman')->first();
        $catATK = Category::where('slug', 'alat-tulis')->first();

        // 2. Buat Produk
        $products = [
            // Makanan
            [
                'name' => 'Pocky Chocolate 47g',
                'barcode' => '8851019110115',
                'category_id' => $catMakanan->id,
                'price' => 8500,
                'cost_price' => 7200,
                'unit' => 'Pcs',
                'is_favorite' => true,
            ],
            [
                'name' => 'Taro Net Seaweed 65g',
                'barcode' => '8991001111652',
                'category_id' => $catMakanan->id,
                'price' => 10500,
                'cost_price' => 9000,
                'unit' => 'Pcs',
                'is_favorite' => false,
            ],
            // Minuman
            [
                'name' => 'Aqua 600ml',
                'barcode' => '8886008101053',
                'category_id' => $catMinuman->id,
                'price' => 4000,
                'cost_price' => 3200,
                'unit' => 'Botol',
                'is_favorite' => true,
            ],
            [
                'name' => 'Teh Pucuk Harum 350ml',
                'barcode' => '8993206213017',
                'category_id' => $catMinuman->id,
                'price' => 4500,
                'cost_price' => 3800,
                'unit' => 'Botol',
                'is_favorite' => true,
            ],
            // ATK
            [
                'name' => 'Standard Pen AE7 Black',
                'barcode' => '8992761010077',
                'category_id' => $catATK->id,
                'price' => 3500,
                'cost_price' => 2800,
                'unit' => 'Pcs',
                'is_favorite' => true,
            ],
            [
                'name' => 'Buku Tulis Sidu 38 Lembar',
                'barcode' => '8992716110012',
                'category_id' => $catATK->id,
                'price' => 4500,
                'cost_price' => 3900,
                'unit' => 'Pcs',
                'is_favorite' => false,
            ],
        ];

        foreach ($products as $pData) {
            $product = Product::updateOrCreate(
                ['barcode' => $pData['barcode']],
                $pData
            );

            // 3. Tambahkan Stok awal jika belum ada
            Stock::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity' => 50,
                    'min_stock' => 5,
                    'batch_no' => 'BATCH-' . strtoupper(Str::random(5)),
                    'expired_at' => now()->addYear(),
                ]
            );
        }

        $this->command->info('✅ Demo Produk dan Stok Berhasil Dibuat!');
    }
}
