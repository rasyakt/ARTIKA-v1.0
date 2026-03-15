<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsignmentItem;
use App\Models\Consignor;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseConsignmentController extends Controller
{
    /**
     * List semua barang konsinyasi (warehouse view)
     */
    public function index(Request $request)
    {
        $query = ConsignmentItem::with(['product', 'consignor'])
            ->whereHas('consignor');

        if ($request->filled('consignor_id')) {
            $query->where('consignor_id', $request->consignor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items      = $query->latest()->paginate(20)->withQueryString();
        $consignors = Consignor::where('is_active', true)->orderBy('name')->get();

        return view('warehouse.consignment.items.index', compact('items', 'consignors'));
    }

    /**
     * Simpan penerimaan barang konsinyasi baru
     */
    public function store(Request $request)
    {
        $rules = [
            'consignor_id'      => 'required|exists:consignors,id',
            'product_mode'      => 'required|in:new,existing',
            'quantity_received' => 'required|integer|min:1',
            'received_at'       => 'required|date',
            'expiry_date'       => 'nullable|date|after_or_equal:received_at',
            'commission_rate'   => 'nullable|numeric|min:0|max:100',
            'notes'             => 'nullable|string',
        ];

        if ($request->product_mode === 'new') {
            $rules['product_name'] = 'required|string|max:255';
            $rules['category_id']  = 'required|exists:categories,id';
            $rules['sell_price']   = 'required|numeric|min:0';
        } else {
            $rules['product_id'] = 'required|exists:products,id';
        }

        $request->validate($rules);

        return DB::transaction(function () use ($request) {
            $consignor = Consignor::findOrFail($request->consignor_id);

            if ($request->product_mode === 'new') {
                $barcode = $request->barcode;
                if (empty($barcode)) {
                    $lastId  = Product::max('id') ?? 0;
                    $barcode = '21' . str_pad($lastId + 1, 8, '0', STR_PAD_LEFT);
                }

                $product = Product::create([
                    'barcode'        => $barcode,
                    'name'           => $request->product_name,
                    'category_id'    => $request->category_id,
                    'price'          => $request->sell_price,
                    'cost_price'     => $request->cost_to_consignor ?? 0,
                    'is_consignment' => true,
                    'consignor_id'   => $consignor->id,
                ]);

                Stock::create(['product_id' => $product->id, 'quantity' => 0]);
            } else {
                $product = Product::findOrFail($request->product_id);
                $product->update([
                    'is_consignment' => true,
                    'consignor_id'   => $consignor->id,
                ]);
            }

            // Tambah stok
            $stock = Stock::firstOrCreate(
                ['product_id' => $product->id],
                ['quantity' => 0]
            );

            $quantityBefore = $stock->quantity;
            $stock->increment('quantity', $request->quantity_received);

            StockMovement::create([
                'product_id'      => $product->id,
                'user_id'         => Auth::id(),
                'type'            => 'in',
                'quantity_before' => $quantityBefore,
                'quantity_after'  => $quantityBefore + $request->quantity_received,
                'quantity_change' => $request->quantity_received,
                'reason'          => 'Konsinyasi Masuk (Gudang)',
                'reference'       => 'KSN-' . $consignor->name,
            ]);

            // Catat di consignment_items
            ConsignmentItem::create([
                'consignor_id'      => $consignor->id,
                'product_id'        => $product->id,
                'quantity_received' => $request->quantity_received,
                'quantity_returned' => 0,
                'cost_to_consignor' => $request->cost_to_consignor,
                'commission_rate'   => $request->commission_rate,
                'received_at'       => $request->received_at,
                'expiry_date'       => $request->expiry_date,
                'status'            => 'active',
                'notes'             => $request->notes,
            ]);

            return redirect()->route('warehouse.consignment.items.index')
                ->with('success', "Barang \"{$product->name}\" dari {$consignor->name} berhasil diterima dan stok diperbarui.");
        });
    }
}
