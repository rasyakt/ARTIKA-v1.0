@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="mb-4">
        <a href="{{ route('admin.consignment.settlements.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Pembayaran
        </a>
        <h2 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
            <i class="fa-solid fa-money-bill-transfer me-2"></i> Mulai Pembayaran Baru
        </h2>
        <p class="text-muted">Hitung dan catat pembayaran hasil penjualan ke penitip.</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger rounded-3 mb-3">{{ session('error') }}</div>
    @endif

    {{-- Step 1: Pilih Penitip & Periode --}}
    <div class="card shadow-sm mb-4" style="border-radius: 14px; border: none;">
        <div class="card-header border-0 pt-4 px-4 pb-2">
            <h5 class="fw-semibold mb-0" style="color: var(--color-primary-dark);">
                <span class="badge rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="background: var(--color-primary-dark); width: 28px; height: 28px; font-size: .8rem;">1</span>
                Pilih Penitip & Periode
            </h5>
        </div>
        <div class="card-body px-4 pb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Penitip *</label>
                    <select name="consignor_id" class="form-select" required style="border-radius: 10px;">
                        <option value="">— Pilih Penitip —</option>
                        @foreach($consignors as $c)
                            <option value="{{ $c->id }}" {{ request('consignor_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Dari Tanggal *</label>
                    <input type="date" name="period_start" class="form-control" required value="{{ request('period_start', date('Y-m-01')) }}" style="border-radius: 10px;">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Sampai Tanggal *</label>
                    <input type="date" name="period_end" class="form-control" required value="{{ request('period_end', date('Y-m-d')) }}" style="border-radius: 10px;">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px; font-weight: 600;">
                        <i class="fa-solid fa-calculator me-1"></i> Hitung
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Step 2: Preview & Konfirmasi --}}
    @if($preview && $selectedConsignor)
    <form action="{{ route('admin.consignment.settlements.store') }}" method="POST">
        @csrf
        <input type="hidden" name="consignor_id" value="{{ $selectedConsignor->id }}">
        <input type="hidden" name="period_start" value="{{ request('period_start') }}">
        <input type="hidden" name="period_end" value="{{ request('period_end') }}">

        <div class="card shadow-sm mb-4" style="border-radius: 14px; border: none;">
            <div class="card-header border-0 pt-4 px-4 pb-2">
                <h5 class="fw-semibold mb-0" style="color: var(--color-primary-dark);">
                    <span class="badge rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="background: var(--color-primary-dark); width: 28px; height: 28px; font-size: .8rem;">2</span>
                    Preview Kalkulasi — {{ $selectedConsignor->name }}
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                {{-- Item Breakdown --}}
                @if(count($preview['item_breakdown']) > 0)
                <div class="table-responsive mb-4">
                    <table class="table align-middle" style="font-size: .9rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Qty Terjual</th>
                                <th class="text-end">Total Penjualan</th>
                                <th class="text-center">Komisi Toko</th>
                                <th class="text-end">Ke Penitip</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview['item_breakdown'] as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row['product']->name }}</td>
                                <td class="text-center">{{ number_format($row['qty'], 0) }}</td>
                                <td class="text-end">Rp {{ number_format($row['sales'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ number_format($row['commission_rate'], 1) }}%</span>
                                    = Rp {{ number_format($row['commission'], 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold" style="color: var(--color-primary-dark);">Rp {{ number_format($row['to_pay'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Ringkasan --}}
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="p-4" style="background: linear-gradient(135deg, var(--color-primary-dark) 0%, #5b2d8e 100%); border-radius: 14px; color: white;">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Penjualan</span>
                                <span class="fw-semibold">Rp {{ number_format($preview['total_sales_amount'], 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Komisi Toko</span>
                                <span class="fw-semibold text-warning">- Rp {{ number_format($preview['commission_amount'], 0, ',', '.') }}</span>
                            </div>
                            <hr style="border-color: rgba(255,255,255,.3);">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold fs-5">Dibayarkan ke Penitip</span>
                                <span class="fw-bold fs-5">Rp {{ number_format($preview['amount_to_pay'], 0, ',', '.') }}</span>
                            </div>
                            <div class="text-center mt-1 opacity-75" style="font-size: .8rem;">
                                {{ number_format($preview['total_sold_qty'], 0) }} unit terjual
                            </div>
                        </div>

                        {{-- Info Bank Penitip --}}
                        @if($selectedConsignor->bank_name)
                        <div class="p-3 mt-3" style="background: #e8f5e9; border-radius: 12px; border: 1px solid #a5d6a7;">
                            <div class="fw-semibold mb-1" style="color: #2e7d32;"><i class="fa-solid fa-building-columns me-1"></i> Transfer ke:</div>
                            <div>{{ $selectedConsignor->bank_name }} — {{ $selectedConsignor->bank_account }}</div>
                            <div class="text-muted">a/n {{ $selectedConsignor->bank_holder }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="alert alert-warning rounded-3">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Tidak ada penjualan barang konsinyasi dari <strong>{{ $selectedConsignor->name }}</strong> dalam periode ini.
                </div>
                @endif

                {{-- Catatan --}}
                <div class="mt-3">
                    <label class="form-label fw-semibold">Catatan Settlement</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Mis: Pembayaran periode Maret 2026" style="border-radius: 10px;"></textarea>
                </div>
            </div>
            @if(count($preview['item_breakdown']) > 0)
            <div class="card-footer border-0 bg-transparent pb-4 px-4">
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.consignment.settlements.create') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">Batal</a>
                    <button type="submit" class="btn btn-success shadow-sm" style="border-radius: 10px; font-weight: 600; padding: .6rem 1.8rem;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Settlement
                    </button>
                </div>
            </div>
            @endif
        </div>
    </form>
    @endif
</div>
@endsection
