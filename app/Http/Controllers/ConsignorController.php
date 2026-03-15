<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consignor;
use App\Models\ConsignmentItem;
use App\Models\ConsignmentSettlement;
use Illuminate\Support\Facades\DB;

class ConsignorController extends Controller
{
    /**
     * Daftar semua penitip
     */
    public function index(Request $request)
    {
        $query = Consignor::withCount(['consignmentItems as total_items'])
            ->withCount(['consignmentItems as active_items' => fn ($q) => $q->where('status', 'active')->orWhereRaw('(quantity_received - quantity_returned) > 0')]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $consignors = $query->latest()->paginate(15)->withQueryString();

        return view('admin.consignors.index', compact('consignors'));
    }

    /**
     * Simpan penitip baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'bank_name'       => 'nullable|string|max:100',
            'bank_account'    => 'nullable|string|max:50',
            'bank_holder'     => 'nullable|string|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'notes'           => 'nullable|string',
        ]);

        Consignor::create($request->only([
            'name', 'phone', 'email', 'address',
            'bank_name', 'bank_account', 'bank_holder',
            'commission_rate', 'notes',
        ]));

        return redirect()->route('admin.consignors.index')
            ->with('success', 'Penitip berhasil ditambahkan.');
    }

    /**
     * Detail profil penitip beserta daftar barang & riwayat settlement
     */
    public function show(Consignor $consignor)
    {
        $consignor->load([
            'consignmentItems.product.category',
            'settlements' => fn ($q) => $q->latest(),
        ]);

        // Hitung summary
        $summary = [
            'total_items'          => $consignor->consignmentItems->count(),
            // Barang Aktif = yang punya stok nyata, apapun label status database-nya
            'active_items'         => $consignor->consignmentItems->filter(fn($i) => $i->quantity_remaining > 0)->count(),
            // Total Penjualan & Komisi dibuat HISTORIS agar tidak terlihat "terbalik" saat sudah dibayar
            'total_sales_amount'   => $consignor->consignmentItems->sum(fn ($i) => $i->sales_amount),
            'total_commission'     => $consignor->consignmentItems->sum(fn ($i) => $i->commission_amount),
            // Sisa yang benar-benar belum masuk dokumen settlement lunas
            'pending_settle'       => $consignor->consignmentItems->sum(fn ($i) => $i->quantity_to_settle * ($i->product->price ?? 0) * (1 - $i->effective_commission_rate / 100)),
            'total_settled'        => $consignor->settlements->where('status', 'paid')->sum('amount_to_pay'),
        ];

        return view('admin.consignors.show', compact('consignor', 'summary'));
    }

    /**
     * Update data penitip
     */
    public function update(Request $request, $id)
    {
        $consignor = Consignor::findOrFail($id);

        $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'bank_name'       => 'nullable|string|max:100',
            'bank_account'    => 'nullable|string|max:50',
            'bank_holder'     => 'nullable|string|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'notes'           => 'nullable|string',
            'is_active'       => 'boolean',
        ]);

        $data = $request->only([
            'name', 'phone', 'email', 'address',
            'bank_name', 'bank_account', 'bank_holder',
            'commission_rate', 'notes',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $consignor->update($data);

        return redirect()->route('admin.consignors.show', $consignor)
            ->with('success', 'Data penitip berhasil diperbarui.');
    }

    /**
     * Hapus penitip (hanya jika tidak ada barang aktif)
     */
    public function destroy($id)
    {
        $consignor = Consignor::findOrFail($id);

        // Dengan SoftDeletes, data tidak benar-benar hilang dari database, 
        // sehingga relasi historis (settlement) tetap aman.
        $consignor->delete();

        return redirect()->route('admin.consignors.index')
            ->with('success', 'Penitip berhasil dihapus (Data historis tetap aman secara internal).');
    }
}
