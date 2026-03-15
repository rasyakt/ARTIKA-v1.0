@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
                <i class="fa-solid fa-chart-bar me-2"></i> Laporan Konsinyasi
            </h2>
            <p class="text-muted mb-0">Ringkasan penjualan dan pembayaran barang titip jual per penitip.</p>
        </div>
        <a href="{{ route('admin.consignment.reports.export', request()->query()) }}"
            class="btn btn-outline-success shadow-sm" style="border-radius: 12px; font-weight: 600;">
            <i class="fa-solid fa-download me-1"></i> Export CSV
        </a>
    </div>

    {{-- Filter --}}
    <div class="card shadow-sm mb-4" style="border-radius: 14px; border: none;">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="period" class="form-select" id="periodSelect" style="border-radius: 10px;">
                        <option value="today"  {{ $period === 'today'  ? 'selected' : '' }}>Hari Ini</option>
                        <option value="week"   {{ $period === 'week'   ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="month"  {{ $period === 'month'  ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="year"   {{ $period === 'year'   ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
                <div class="col-md-2 custom-dates {{ in_array($period, ['custom']) ? '' : 'd-none' }}">
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}" style="border-radius: 10px;">
                </div>
                <div class="col-md-2 custom-dates {{ in_array($period, ['custom']) ? '' : 'd-none' }}">
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}" style="border-radius: 10px;">
                </div>
                <div class="col-md-3">
                    <select name="consignor_id" class="form-select" style="border-radius: 10px;">
                        <option value="">Semua Penitip</option>
                        @foreach($consignors as $c)
                            <option value="{{ $c->id }}" {{ $filterConsignorId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px;"><i class="fa-solid fa-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        @php
        $summaryCards = [
            ['label' => 'Total Unit Terjual',       'value' => number_format($summary['total_qty'], 0) . ' unit', 'icon' => 'boxes-stacked', 'color' => '#1565c0', 'bg' => '#e3f2fd'],
            ['label' => 'Total Nilai Penjualan',    'value' => 'Rp ' . number_format($summary['total_sales'], 0, ',', '.'), 'icon' => 'chart-line', 'color' => '#6a1b9a', 'bg' => '#f3e5f5'],
            ['label' => 'Komisi Toko',              'value' => 'Rp ' . number_format($summary['total_commission'], 0, ',', '.'), 'icon' => 'store', 'color' => '#e65100', 'bg' => '#fff3e0'],
            ['label' => 'Dikembalikan ke Penitip',  'value' => 'Rp ' . number_format($summary['total_to_pay'], 0, ',', '.'), 'icon' => 'money-bill-transfer', 'color' => '#2e7d32', 'bg' => '#e8f5e9'],
            ['label' => 'Menunggu Pembayaran',      'value' => 'Rp ' . number_format($summary['pending_pay'], 0, ',', '.'), 'icon' => 'clock', 'color' => '#f9a825', 'bg' => '#fffde7'],
        ];
        @endphp
        @foreach($summaryCards as $card)
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: {{ $card['bg'] }};">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 46px; height: 46px; background: {{ $card['color'] }}22;">
                        <i class="fa-solid fa-{{ $card['icon'] }}" style="color: {{ $card['color'] }};"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: .78rem;">{{ $card['label'] }}</div>
                        <div class="fw-bold" style="color: {{ $card['color'] }};">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4">
        {{-- Per Penitip --}}
        <div class="col-lg-7">
            <div class="card shadow-sm h-100" style="border-radius: 14px; border: none;">
                <div class="card-header border-0 pt-4 px-4 pb-2">
                    <h6 class="fw-bold mb-0" style="color: var(--color-primary-dark);">
                        <i class="fa-solid fa-users me-2"></i> Penjualan Per Penitip
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Penitip</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Penjualan</th>
                                    <th class="text-center">Komisi</th>
                                    <th class="text-end pe-4">Ke Penitip</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consignorStats as $stat)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('admin.consignors.show', $stat['consignor']) }}" class="text-decoration-none fw-semibold" style="color: var(--color-primary-dark);">
                                            {{ $stat['consignor']->name }}
                                        </a>
                                    </td>
                                    <td class="text-center">{{ number_format($stat['qty'], 0) }}</td>
                                    <td class="text-end">Rp {{ number_format($stat['sales'], 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ number_format($stat['rate'], 1) }}%</span>
                                    </td>
                                    <td class="text-end pe-4 fw-semibold" style="color: var(--color-primary-dark);">
                                        Rp {{ number_format($stat['to_pay'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data penjualan konsinyasi di periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Produk --}}
        <div class="col-lg-5">
            <div class="card shadow-sm h-100" style="border-radius: 14px; border: none;">
                <div class="card-header border-0 pt-4 px-4 pb-2">
                    <h6 class="fw-bold mb-0" style="color: var(--color-primary-dark);">
                        <i class="fa-solid fa-fire me-2" style="color: #e53935;"></i> Produk Konsinyasi Terlaris
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Produk</th>
                                    <th>Penitip</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end pe-4">Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $p)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $p->product_name }}</td>
                                    <td class="text-muted" style="font-size: .85rem;">{{ $p->consignor_name }}</td>
                                    <td class="text-center">{{ number_format($p->total_qty, 0) }}</td>
                                    <td class="text-end pe-4">Rp {{ number_format($p->total_sales, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">-</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Settlements --}}
        @if($recentSettlements->count() > 0)
        <div class="col-12">
            <div class="card shadow-sm" style="border-radius: 14px; border: none;">
                <div class="card-header border-0 pt-4 px-4 pb-2">
                    <h6 class="fw-bold mb-0" style="color: var(--color-primary-dark);">
                        <i class="fa-solid fa-receipt me-2"></i> Riwayat Pembayaran (Periode Ini)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Ref. No.</th>
                                    <th>Penitip</th>
                                    <th class="text-end">Dibayarkan</th>
                                    <th class="text-center pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSettlements as $s)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('admin.consignment.settlements.show', $s) }}" class="fw-semibold text-decoration-none" style="color: var(--color-primary-dark);">{{ $s->reference_no }}</a>
                                    </td>
                                    <td>{{ $s->consignor->name }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($s->amount_to_pay, 0, ',', '.') }}</td>
                                    <td class="text-center pe-4">
                                        @if($s->status === 'paid') <span class="badge bg-success rounded-pill">Lunas</span>
                                        @else <span class="badge bg-warning text-dark rounded-pill">Pending</span> @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.getElementById('periodSelect').addEventListener('change', function(){
    const show = this.value === 'custom';
    document.querySelectorAll('.custom-dates').forEach(el => el.classList.toggle('d-none', !show));
});
</script>
@endsection
