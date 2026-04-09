<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    private $categories;

    public function __construct()
    {
        // Cache existing categories
        $this->categories = Category::pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        })->toArray();
    }

    public function prepareForValidation($data, $index)
    {
        foreach ($data as $key => $value) {
            if ($value !== null && !is_array($value)) {
                $data[$key] = (string) $value;
            }
        }
        return $data;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Robust check: Skip rows where name is empty (Instruction rows or empty rows)
            // Barcode is now optional
            if (empty($row['nama_produk'])) {
                continue;
            }

            $categoryName = trim($row['kategori'] ?? '');
            $categoryId = null;

            if (!empty($categoryName)) {
                $lowerKey = strtolower($categoryName);
                if (isset($this->categories[$lowerKey])) {
                    $categoryId = $this->categories[$lowerKey];
                } else {
                    $newCategory = Category::create([
                        'name' => $categoryName,
                        'slug' => \Illuminate\Support\Str::slug($categoryName),
                        'color' => '#6B7280'
                    ]);
                    $categoryId = $newCategory->id;
                    $this->categories[$lowerKey] = $categoryId;
                }
            }

            $barcode = trim($row['barcode'] ?? '');
            if (empty($barcode)) {
                // Generate temporary unique barcode based on name and timestamp if completely missing
                // In a real scenario, we might want a more predictable SKU generator
                $barcode = 'ART-IMP-' . strtoupper(substr(md5($row['nama_produk'] . time()), 0, 8));
            }

            // Update or Create based on Barcode
            Product::updateOrCreate(
                ['barcode' => $barcode],
                [
                    'name' => $row['nama_produk'],
                    'category_id' => $categoryId,
                    'price' => $row['harga_jual'],
                    'cost_price' => $row['harga_modal'] ?? 0,
                    'description' => $row['deskripsi'] ?? null,
                ]
            );
        }
    }

    public function rules(): array
    {
        return [
            'barcode' => ['nullable', 'string', 'max:255'], // nullable to avoid error on empty rows before we skip them in collection()
            'nama_produk' => ['nullable', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'harga_jual' => ['nullable', 'numeric', 'min:0'],
            'harga_modal' => ['nullable', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }
}
