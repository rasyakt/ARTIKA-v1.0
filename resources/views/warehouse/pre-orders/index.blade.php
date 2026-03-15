@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
                <i class="fa-solid fa-receipt me-2"></i> Pre-Order Supplier
            </h2>
            <p class="text-muted mb-0">Daftar pesanan barang ke supplier. Konfirmasi penerimaan saat barang tiba.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Stats Cards --}}
    @php
        $pendingCount = $preOrders->where('status', 'pending')->count();
        $shippedCount = $preOrders->where('status', 'shipped')->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3" style="border-radius: 14px; border: none;">
                <div style="font-size: 1.8rem; font-weight: 700; color: var(--color-warning);">{{ $preOrders->total() }}</div>
                <div class="text-muted small">Total Pre-Order</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3" style="border-radius: 14px; border: none;">
                <div style="font-size: 1.8rem; font-weight: 700; color: var(--color-primary-dark);">{{ $preOrders->where('status', 'pending')->count() }}</div>
                <div class="text-muted small">Menunggu</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3" style="border-radius: 14px; border: none;">
                <div style="font-size: 1.8rem; font-weight: 700; color: var(--bs-info);">{{ $preOrders->where('status', 'shipped')->count() }}</div>
                <div class="text-muted small">Dalam Pengiriman</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3" style="border-radius: 14px; border: none;">
                <div style="font-size: 1.8rem; font-weight: 700; color: var(--bs-success);">{{ $preOrders->where('status', 'received')->count() }}</div>
                <div class="text-muted small">Sudah Diterima</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm" style="border-radius: 14px; border: none;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: var(--color-primary-dark); color: white;">
                        <tr>
                            <th class="ps-4 py-3">Tanggal</th>
                            <th>Supplier</th>
                            <th>No. Referensi</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preOrders as $order)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $order->created_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                            </td>
                            <td class="fw-semibold">{{ $order->supplier->name }}</td>
                            <td><code>{{ $order->reference_number ?? '-' }}</code></td>
                            <td class="text-end">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @php
                                    $badgeMap = [
                                        'pending'   => 'bg-warning text-dark',
                                        'ordered'   => 'bg-info text-white',
                                        'shipped'   => 'bg-primary text-white',
                                        'received'  => 'bg-success text-white',
                                        'cancelled' => 'bg-danger text-white',
                                    ];
                                    $labelMap = [
                                        'pending'   => 'Menunggu',
                                        'ordered'   => 'Dipesan',
                                        'shipped'   => 'Dikirim',
                                        'received'  => 'Diterima',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span class="badge {{ $badgeMap[$order->status] ?? 'bg-secondary' }}" style="border-radius: 8px; padding: .4rem .75rem;">
                                    {{ $labelMap[$order->status] ?? ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('warehouse.pre-orders.show', $order->id) }}"
                                        class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                                        <i class="fa-solid fa-eye me-1"></i> Detail
                                    </a>
                                    @if($order->status !== 'received' && $order->status !== 'cancelled')
                                    <form action="{{ route('admin.suppliers.pre_orders.update_status', $order->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="received">
                                        <button type="button" class="btn btn-sm btn-success btn-receive-confirm" style="border-radius: 8px;">
                                            <i class="fa-solid fa-check-double me-1"></i> Terima
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fa-2x mb-2 d-block opacity-25"></i>
                                Belum ada pre-order supplier.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($preOrders->hasPages())
        <div class="card-footer bg-transparent border-top-0 pb-3 px-3">{{ $preOrders->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.btn-receive-confirm').forEach(button => {
    button.addEventListener('click', function () {
        confirmAction({
            title: 'Konfirmasi Penerimaan',
            text: 'Apakah barang sudah benar-benar diterima dan stok akan diperbarui otomatis?',
            icon: 'question',
            confirmButtonText: 'Ya, Terima Barang',
            confirmButtonColor: 'var(--color-success)'
        }).then((result) => {
            if (result.isConfirmed) this.closest('form').submit();
        });
    });
});
</script>
@endpush
@endsection
