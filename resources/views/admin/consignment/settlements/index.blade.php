@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
                <i class="fa-solid fa-receipt me-2"></i> Pembayaran Konsinyasi
            </h2>
            <p class="text-muted mb-0">Riwayat pembayaran hasil penjualan kepada penitip.</p>
        </div>
        <a href="{{ route('admin.consignment.settlements.create') }}" class="btn btn-success shadow-sm"
            style="border-radius: 12px; padding: 0.6rem 1.4rem; font-weight: 600; border: none;">
            <i class="fa-solid fa-plus me-1"></i> Mulai Pembayaran Baru
        </a>
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
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px;"><i class="fa-solid fa-search me-1"></i> Filter</button>
                    @if(request()->hasAny(['consignor_id','status']))
                        <a href="{{ route('admin.consignment.settlements.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;"><i class="fa-solid fa-xmark"></i></a>
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
                            <th class="ps-4 py-3">Ref. No.</th>
                            <th>Penitip</th>
                            <th>Periode</th>
                            <th class="text-center">Qty Terjual</th>
                            <th class="text-end">Total Penjualan</th>
                            <th class="text-end">Komisi Toko</th>
                            <th class="text-end fw-bold">Dibayarkan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settlements as $s)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.consignment.settlements.show', $s) }}" class="fw-semibold text-decoration-none" style="color: var(--color-primary-dark);">
                                    {{ $s->reference_no }}
                                </a>
                            </td>
                            <td>
                                @if($s->consignor?->trashed())
                                    <span class="text-muted fw-semibold">
                                        <i class="fa-solid fa-user-slash me-1"></i>{{ $s->consignor?->name }} (Terhapus)
                                    </span>
                                @else
                                    <a href="{{ route('admin.consignors.show', $s->consignor) }}" class="text-decoration-none text-dark fw-semibold">
                                        {{ $s->consignor?->name ?? 'N/A' }}
                                    </a>
                                @endif
                            </td>
                            <td class="text-muted">{{ $s->period_start->format('d M Y') }} – {{ $s->period_end->format('d M Y') }}</td>
                            <td class="text-center">{{ number_format($s->total_sold_qty, 0) }} unit</td>
                            <td class="text-end">Rp {{ number_format($s->total_sales_amount, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">Rp {{ number_format($s->commission_amount, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold" style="color: var(--color-primary-dark);">Rp {{ number_format($s->amount_to_pay, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($s->status === 'paid')
                                    <span class="badge bg-success rounded-pill px-3">Lunas</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('admin.consignment.settlements.show', $s) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fa-2x mb-2 d-block opacity-25"></i>
                                Belum ada riwayat pembayaran. Klik <strong>Mulai Pembayaran Baru</strong>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($settlements->hasPages())
        <div class="card-footer bg-transparent border-top-0 pb-3 px-3">{{ $settlements->links() }}</div>
        @endif
    </div>
</div>
@endsection
