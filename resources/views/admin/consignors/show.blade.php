@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('admin.consignors.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Penitip
        </a>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
                    <i class="fa-solid fa-user-tie me-2"></i> {{ $consignor->name }}
                </h2>
                <p class="text-muted mb-0">{{ $consignor->phone ?? '' }} {{ $consignor->phone && $consignor->email ? '·' : '' }} {{ $consignor->email ?? '' }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.consignment.settlements.create', ['consignor_id' => $consignor->id]) }}"
                    class="btn btn-outline-success shadow-sm" style="border-radius: 12px; font-weight: 600;">
                    <i class="fa-solid fa-money-bill-transfer me-1"></i> Mulai Pembayaran
                </a>
                <button class="btn btn-outline-warning" style="border-radius: 10px; font-weight: 600;"
                    onclick="openEditModal({{ $consignor->id }}, {{ json_encode($consignor) }})">
                    <i class="fa-solid fa-pen me-1"></i> Edit
                </button>
            </div>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        @php
        $cards = [
            ['icon' => 'boxes-stacked', 'label' => 'Total Barang Dititipkan', 'value' => $summary['total_items'] . ' item', 'color' => '#1565c0', 'bg' => '#e3f2fd'],
            ['icon' => 'box-open', 'label' => 'Barang Aktif', 'value' => $summary['active_items'] . ' item', 'color' => '#2e7d32', 'bg' => '#e8f5e9'],
            ['icon' => 'chart-line', 'label' => 'Total Seluruh Penjualan', 'value' => 'Rp ' . number_format($summary['total_sales_amount'], 0, ',', '.'), 'color' => '#6a1b9a', 'bg' => '#f3e5f5'],
            ['icon' => 'store', 'label' => 'Komisi Toko', 'value' => 'Rp ' . number_format($summary['total_commission'], 0, ',', '.'), 'color' => '#e65100', 'bg' => '#fff3e0'],
            ['icon' => 'clock', 'label' => 'Menunggu Pembayaran', 'value' => 'Rp ' . number_format($summary['pending_settle'], 0, ',', '.'), 'color' => '#f9a825', 'bg' => '#fffde7'],
            ['icon' => 'circle-check', 'label' => 'Total Sudah Dibayarkan', 'value' => 'Rp ' . number_format($summary['total_settled'], 0, ',', '.'), 'color' => '#2e7d32', 'bg' => '#e8f5e9'],
        ];
        @endphp
        @foreach($cards as $card)
        <div class="col-md-4 col-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 14px; background: {{ $card['bg'] }};">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 46px; height: 46px; background: {{ $card['color'] }}20;">
                        <i class="fa-solid fa-{{ $card['icon'] }}" style="color: {{ $card['color'] }};"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: .8rem;">{{ $card['label'] }}</div>
                        <div class="fw-bold" style="color: {{ $card['color'] }}">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Info bank & komisi --}}
    <div class="card shadow-sm mb-4" style="border-radius: 14px; border: none;">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="fw-semibold mb-1" style="color: var(--color-primary-dark);"><i class="fa-solid fa-building-columns me-2"></i>Rekening Bank</div>
                    @if($consignor->bank_name)
                        <div>{{ $consignor->bank_name }} · {{ $consignor->bank_account }}</div>
                        <div class="text-muted">a/n {{ $consignor->bank_holder }}</div>
                    @else
                        <span class="text-muted">Belum diisi</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="fw-semibold mb-1" style="color: var(--color-primary-dark);">Komisi Toko Default</div>
                    <span class="badge rounded-pill fs-6 px-3 py-2" style="background: var(--color-primary-dark);">{{ number_format($consignor->commission_rate, 1) }}%</span>
                    <div class="text-muted mt-1" style="font-size:.8rem;">Penitip dapat {{ number_format(100 - $consignor->commission_rate, 1) }}% dari penjualan</div>
                </div>
                <div class="col-md-3">
                    <div class="fw-semibold mb-1" style="color: var(--color-primary-dark);">Alamat</div>
                    <div class="text-muted">{{ $consignor->address ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs: Barang & Settlement --}}
    <ul class="nav nav-pills mb-3 gap-2" id="consignorTab">
        <li class="nav-item">
            <a class="nav-link active" href="#tab-items" data-bs-toggle="pill" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-boxes-stacked me-1"></i> Barang Titipan ({{ $consignor->consignmentItems->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#tab-settlements" data-bs-toggle="pill" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-receipt me-1"></i> Riwayat Pembayaran ({{ $consignor->settlements->count() }})
            </a>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Tab Barang --}}
        <div class="tab-pane fade show active" id="tab-items">
            <div class="card shadow-sm" style="border-radius: 14px; border: none;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: var(--color-primary-dark); color: white;">
                                <tr>
                                    <th class="ps-4 py-3">Produk</th>
                                    <th class="text-center">Diterima</th>
                                    <th class="text-center">Terjual</th>
                                    <th class="text-center">Sisa</th>
                                    <th class="text-end">Total Penjualan</th>
                                    <th class="text-center">Komisi</th>
                                    <th class="text-end">Dibayarkan</th>
                                    <th class="text-center pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consignor->consignmentItems as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $item->product->name ?? '-' }}</div>
                                        <small class="text-muted">Diterima: {{ $item->received_at?->format('d M Y') }}</small>
                                        @if($item->expiry_date)
                                        <br><small class="text-danger"><i class="fa-solid fa-calendar-xmark me-1"></i>Exp: {{ $item->expiry_date?->format('d M Y') }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format($item->quantity_received, 0) }}</td>
                                    <td class="text-center fw-semibold" style="color: var(--color-success);">{{ number_format($item->quantity_sold, 0) }}</td>
                                    <td class="text-center">
                                        <span class="{{ $item->quantity_remaining < 5 ? 'text-danger fw-bold' : '' }}">
                                            {{ number_format($item->quantity_remaining, 0) }}
                                        </span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($item->sales_amount, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ number_format($item->effective_commission_rate, 1) }}%</span>
                                    </td>
                                    <td class="text-end fw-semibold" style="color: var(--color-primary-dark);">
                                        Rp {{ number_format($item->amount_to_pay, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center pe-4">
                                        @if($item->status === 'active')
                                            <span class="badge bg-success">Aktif</span>
                                        @elseif($item->status === 'settled')
                                            <span class="badge bg-info text-dark">Lunas & Selesai</span>
                                        @else
                                            <span class="badge bg-secondary">Kembali</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Belum ada barang dari penitip ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Settlement --}}
        <div class="tab-pane fade" id="tab-settlements">
            <div class="card shadow-sm" style="border-radius: 14px; border: none;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: var(--color-primary-dark); color: white;">
                                <tr>
                                    <th class="ps-4 py-3">Referensi</th>
                                    <th>Periode</th>
                                    <th class="text-end">Total Penjualan</th>
                                    <th class="text-end">Komisi Toko</th>
                                    <th class="text-end">Dibayarkan</th>
                                    <th class="text-center pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consignor->settlements as $s)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('admin.consignment.settlements.show', $s) }}" class="fw-semibold text-decoration-none" style="color: var(--color-primary-dark);">
                                            {{ $s->reference_no }}
                                        </a>
                                    </td>
                                    <td>{{ $s->period_start->format('d M Y') }} – {{ $s->period_end->format('d M Y') }}</td>
                                    <td class="text-end">Rp {{ number_format($s->total_sales_amount, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($s->commission_amount, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold" style="color: var(--color-primary-dark);">Rp {{ number_format($s->amount_to_pay, 0, ',', '.') }}</td>
                                    <td class="text-center pe-4">
                                        @if($s->status === 'paid')
                                            <span class="badge bg-success rounded-pill">Lunas</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat pembayaran.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Penitip --}}
<div class="modal fade" id="modalEditPenitip" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <form id="formEditPenitip" method="POST">
                @csrf @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Edit Penitip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    @include('admin.consignors._form', ['consignor' => $consignor, 'edit' => true])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" style="border-radius: 10px; font-weight: 600;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openEditModal(id, data) {
    const form = document.getElementById('formEditPenitip');
    form.action = `/admin/consignors/${id}`;
    const fields = ['name','phone','email','address','bank_name','bank_account','bank_holder','commission_rate','notes'];
    fields.forEach(f => {
        const el = form.querySelector(`[name="${f}"]`);
        if (el) el.value = data[f] ?? '';
    });
    const isActive = form.querySelector('[name="is_active"]');
    if (isActive) isActive.checked = data.is_active;
    new bootstrap.Modal(document.getElementById('modalEditPenitip')).show();
}
</script>
@endsection
