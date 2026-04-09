@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
                <i class="fa-solid fa-boxes-stacked me-2"></i> Barang Konsinyasi
            </h2>
            <p class="text-muted mb-0">Daftar semua barang titip jual yang sedang aktif di toko.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBarang"
            style="background: var(--color-primary-dark); border: none; border-radius: 12px; padding: 0.6rem 1.4rem; font-weight: 600;">
            <i class="fa-solid fa-truck-ramp-box me-1"></i> Terima Barang Konsinyasi
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Filter --}}
    <div class="card shadow-sm mb-4" style="border-radius: 14px; border: none;">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <select name="consignor_id" class="form-select" style="border-radius: 10px;">
                        <option value="">Semua Penitip</option>
                        @foreach($consignors as $c)
                            <option value="{{ $c->id }}" {{ request('consignor_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" style="border-radius: 10px;">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="settled" {{ request('status') === 'settled' ? 'selected' : '' }}>Lunas & Selesai</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px;"><i class="fa-solid fa-search me-1"></i> Filter</button>
                    @if(request()->hasAny(['consignor_id','status']))
                        <a href="{{ route('warehouse.consignment.items.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card shadow-sm" style="border-radius: 14px; border: none;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: var(--color-primary-dark); color: white;">
                        <tr>
                            <th class="ps-4 py-3">Produk</th>
                            <th>Penitip</th>
                            <th class="text-center">Diterima</th>
                            <th class="text-center">Terjual</th>
                            <th class="text-center">Sisa</th>
                            <th class="text-center">Kadaluarsa</th>
                            <th class="text-center pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $item->product->name ?? '-' }}</div>
                                <small class="text-muted">{{ $item->received_at?->format('d M Y') }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold" style="color: var(--color-primary-dark);">
                                    {{ $item->consignor?->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-center">{{ number_format($item->quantity_received, 0) }}</td>
                            <td class="text-center fw-semibold text-success">{{ number_format($item->quantity_sold, 0) }}</td>
                            <td class="text-center">
                                <span class="{{ $item->quantity_remaining < 5 && $item->status === 'active' ? 'text-danger fw-bold' : '' }}">
                                    {{ number_format($item->quantity_remaining, 0) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item->expiry_date)
                                    <span class="{{ $item->expiry_date->isPast() ? 'text-danger fw-bold' : ($item->expiry_date->diffInDays(now()) <= 7 ? 'text-warning fw-bold' : 'text-muted') }}">
                                        {{ $item->expiry_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                @if($item->status === 'active')
                                    <span class="badge bg-success">Aktif</span>
                                @elseif($item->status === 'settled')
                                    <span class="badge bg-info text-dark">Lunas & Selesai</span>
                                @else
                                    <span class="badge bg-secondary">Selesai</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-boxes-stacked fa-2x mb-2 d-block opacity-25"></i>
                                Belum ada barang konsinyasi.
            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
        <div class="card-footer bg-transparent border-top-0 pb-3 px-3">{{ $items->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Terima Barang --}}
<div class="modal fade" id="modalTambahBarang" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('warehouse.consignment.items.store') }}" method="POST" class="modal-content" style="border-radius: 16px; border: none;">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--color-primary-dark);">
                    <i class="fa-solid fa-truck-ramp-box me-2"></i> Terima Barang Konsinyasi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Penitip *</label>
                        <select name="consignor_id" class="form-select" required style="border-radius: 10px;" id="consignorSelectWH">
                            <option value="">— Pilih Penitip —</option>
                            @foreach($consignors as $c)
                                <option value="{{ $c->id }}" data-commission="{{ $c->commission_rate }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Produk *</label>
                        <div class="d-flex gap-3 align-items-center mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="product_mode" id="modeNewWH" value="new" checked>
                                <label class="form-check-label" for="modeNewWH">Produk Baru</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="product_mode" id="modeExistingWH" value="existing">
                                <label class="form-check-label" for="modeExistingWH">Produk Yang Sudah Ada</label>
                            </div>
                        </div>
                    </div>

                    {{-- Form Produk Baru --}}
                    <div id="sectionNewWH" class="col-12">
                        <div class="p-3" style="background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                            <div class="fw-semibold mb-2" style="color: var(--color-primary-dark);">Detail Produk Baru</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nama Produk *</label>
                                    <input type="text" name="product_name" class="form-control" placeholder="Nama produk" style="border-radius: 10px;">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Kategori *</label>
                                    <select name="category_id" class="form-select" style="border-radius: 10px;">
                                        <option value="">Pilih Kategori</option>
                                        @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Harga Jual *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="sell_price" class="form-control" placeholder="0" min="0" style="border-radius: 0 10px 10px 0;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Barcode (opsional)</label>
                                    <input type="text" name="barcode" class="form-control" placeholder="Auto-generate jika kosong" style="border-radius: 10px;">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Harga Modal Penitip</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="cost_to_consignor" class="form-control" placeholder="0" min="0" style="border-radius: 0 10px 10px 0;">
                                    </div>
                                    <small class="text-muted">Opsional, untuk catatan saja</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Produk Lama --}}
                    <div id="sectionExistingWH" class="col-12 d-none">
                        <label class="form-label fw-semibold">Pilih Produk Yang Sudah Ada *</label>
                        <select name="product_id" class="form-select" style="border-radius: 10px;">
                            <option value="">— Pilih Produk —</option>
                            @foreach(\App\Models\Product::orderBy('name')->get() as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (Rp {{ number_format($p->price, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Jumlah Diterima *</label>
                        <input type="number" name="quantity_received" class="form-control" required min="1" placeholder="unit" style="border-radius: 10px;">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Komisi Toko (%)
                            <span class="text-muted fw-normal" style="font-size:.75rem;">Override per barang</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="commission_rate" id="commissionRateWH" class="form-control" min="0" max="100" step="0.5" placeholder="Pakai tarif penitip" style="border-radius: 10px 0 0 10px;">
                            <span class="input-group-text" style="border-radius: 0 10px 10px 0;">%</span>
                        </div>
                        <small class="text-muted">Kosongkan = pakai komisi penitip</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Diterima *</label>
                        <input type="date" name="received_at" class="form-control" required value="{{ date('Y-m-d') }}" style="border-radius: 10px;">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Kedaluwarsa</label>
                        <input type="date" name="expiry_date" class="form-control" style="border-radius: 10px;">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan penerimaan barang" style="border-radius: 10px;"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: var(--color-primary-dark); border: none; border-radius: 10px; font-weight: 600;">
                    <i class="fa-solid fa-truck-ramp-box me-1"></i> Terima & Masukkan Stok
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('[name="product_mode"]').forEach(r => {
    r.addEventListener('change', function () {
        document.getElementById('sectionNewWH').classList.toggle('d-none', this.value !== 'new');
        document.getElementById('sectionExistingWH').classList.toggle('d-none', this.value !== 'existing');
    });
});
document.getElementById('consignorSelectWH').addEventListener('change', function () {
    const rate = this.options[this.selectedIndex]?.dataset.commission ?? '';
    const inp  = document.getElementById('commissionRateWH');
    if (inp && !inp.value) inp.placeholder = rate ? `Penitip: ${rate}%` : 'Kosong = pakai tarif penitip';
});
</script>
@endsection
