<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsignmentItem;
use App\Models\Consignor;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ConsignmentItemController extends Controller
{
    /**
     * Daftar semua barang konsinyasi
     */
    public function index(Request $request)
    {
        $query = ConsignmentItem::with(['consignor', 'product.category'])
            ->latest();

        if ($request->filled('consignor_id')) {
            $query->where('consignor_id', $request->consignor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
                  ->orWhereHas('consignor', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        $items     = $query->paginate(15)->withQueryString();
        $consignors = Consignor::where('is_active', true)->orderBy('name')->get();

        return view('admin.consignment.items.index', compact('items', 'consignors'));
    }

    /**
     * Terima barang konsinyasi baru:
     * - Buat produk baru ATAU pakai produk yang sudah ada
     * - Tambah stok ke gudang
     * - Catat di consignment_items
     */
    public function store(Request $request)
    {
        $request->validate([
            'consignor_id'       => 'required|exists:consignors,id',
            'product_mode'       => 'required|in:new,existing',

            // Jika produk baru
            'product_name'       => 'nullable|required_if:product_mode,new|string|max:255',
            'category_id'        => 'nullable|required_if:product_mode,new|exists:categories,id',
            'sell_price'         => 'nullable|required_if:product_mode,new|numeric|min:0',
            'barcode'            => 'nullable|string|unique:products,barcode',
            
            // Jika produk lama
            'product_id'         => 'nullable|required_if:product_mode,existing|exists:products,id',

            // Selalu wajib
            'quantity_received'  => 'required|numeric|min:1',
            'commission_rate'    => 'nullable|numeric|min:0|max:100',
            'received_at'        => 'required|date',
            'expiry_date'        => 'nullable|date|after:today',
            'cost_to_consignor'  => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $consignor = Consignor::findOrFail($request->consignor_id);

            // ── Dapatkan / buat produk ──────────────────────────────────────
            if ($request->product_mode === 'new') {
                // Auto-generate barcode jika kosong
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

                // Buat stok awal
                Stock::create(['product_id' => $product->id, 'quantity' => 0]);

            } else {
                $product = Product::findOrFail($request->product_id);
                // Tandai produk sebagai konsinyasi
                $product->update([
                    'is_consignment' => true,
                    'consignor_id'   => $consignor->id,
                ]);
            }

            // ── Tambah stok ke gudang ───────────────────────────────────────
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
                'reason'          => 'Konsinyasi Masuk',
                'reference'       => 'KSN-' . $consignor->name,
            ]);

            // ── Catat di consignment_items ──────────────────────────────────
            ConsignmentItem::create([
                'consignor_id'      => $consignor->id,
                'product_id'        => $product->id,
                'quantity_received' => $request->quantity_received,
                'quantity_returned' => 0,
                'cost_to_consignor' => $request->cost_to_consignor,
                'commission_rate'   => $request->commission_rate, // null = pakai tarif penitip
                'received_at'       => $request->received_at,
                'expiry_date'       => $request->expiry_date,
                'status'            => 'active',
                'notes'             => $request->notes,
            ]);

            return redirect()->route('admin.consignment.items.index')
                ->with('success', "Barang konsinyasi \"{$product->name}\" dari {$consignor->name} berhasil dicatat.");
        });
    }

    /**
     * Update info barang konsinyasi (komisi, catatan, dll)
     */
    public function update(Request $request, $id)
    {
        $item = ConsignmentItem::findOrFail($id);

        $request->validate([
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'expiry_date'     => 'nullable|date',
            'notes'           => 'nullable|string',
        ]);

        $item->update($request->only(['commission_rate', 'expiry_date', 'notes']));

        return back()->with('success', 'Data barang konsinyasi diperbarui.');
    }

    /**
     * Kembalikan barang ke penitip (sebagian atau seluruhnya)
     */
    public function returnToConsignor(Request $request, $id)
    {
        $item = ConsignmentItem::findOrFail($id);

        $request->validate([
            'quantity_returned' => 'required|numeric|min:0.01|max:' . $item->quantity_remaining,
            'return_notes'      => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $item) {
            $qty = $request->quantity_returned;

            // Kurangi stok gudang
            $stock = Stock::where('product_id', $item->product_id)->first();
            if ($stock) {
                $before = $stock->quantity;
                $stock->decrement('quantity', $qty);
                StockMovement::create([
                    'product_id'      => $item->product_id,
                    'user_id'         => Auth::id(),
                    'type'            => 'out',
                    'quantity_before' => $before,
                    'quantity_after'  => max(0, $before - $qty),
                    'quantity_change' => $qty,
                    'reason'          => 'Retur Konsinyasi',
                    'reference'       => 'KSN-RETUR-' . $item->id,
                ]);
            }

            $item->increment('quantity_returned', $qty);

            // Jika semua barang sudah dikembalikan, ubah status
            if ($item->fresh()->quantity_remaining <= 0) {
                $item->update(['status' => 'returned']);
            }

            return back()->with('success', "{$qty} unit berhasil dikembalikan ke penitip.");
        });
    }

    /**
     * Hapus barang konsinyasi (hanya jika belum ada penjualan)
     */
    public function destroy($id)
    {
        $item = ConsignmentItem::findOrFail($id);

        if ($item->quantity_sold > 0) {
            return back()->with('error', 'Tidak bisa hapus barang yang sudah pernah terjual.');
        }

        // Kembalikan stok jika ada
        if ($item->quantity_received > 0) {
            $stock = Stock::where('product_id', $item->product_id)->first();
            if ($stock) {
                $stock->decrement('quantity', $item->quantity_received);
            }
        }

        $item->delete();
        return back()->with('success', 'Catatan barang konsinyasi dihapus.');
    }
}
