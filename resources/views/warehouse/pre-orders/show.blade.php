@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Back + Header --}}
    <div class="mb-4">
        <a href="{{ route('warehouse.pre-orders.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Pre-Order
        </a>
        <h2 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
            <i class="fa-solid fa-receipt me-2"></i> Detail Pre-Order
        </h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Action Buttons --}}
    @if($preOrder->status !== 'received' && $preOrder->status !== 'cancelled')
    <div class="d-flex gap-2 mb-4">
        <form action="{{ route('admin.suppliers.pre_orders.update_status', $preOrder->id) }}" method="POST" id="receive-form">
            @csrf
            <input type="hidden" name="status" value="received">
            <button type="button" class="btn btn-success shadow-sm btn-receive-preorder" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-check-double me-1"></i> Konfirmasi Penerimaan Barang
            </button>
        </form>
    </div>
    @elseif($preOrder->status === 'received')
    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('admin.suppliers.pre_orders.print_faktur', $preOrder->id) }}" target="_blank"
            class="btn btn-primary shadow-sm" style="border-radius: 10px; font-weight: 600;">
            <i class="fa-solid fa-print me-1"></i> Cetak Faktur
        </a>
    </div>
    @endif

    <div class="row g-4">
        {{-- Info Card --}}
        <div class="col-md-4">
            <div class="card shadow-sm" style="border-radius: 14px; border: none;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4" style="color: var(--color-primary-dark);">Informasi Pesanan</h5>

                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">Status</label>
                        @php
                            $badgeMap = ['pending'=>'bg-warning text-dark','ordered'=>'bg-info text-white','shipped'=>'bg-primary text-white','received'=>'bg-success text-white','cancelled'=>'bg-danger text-white'];
                            $labelMap = ['pending'=>'Menunggu','ordered'=>'Dipesan','shipped'=>'Dikirim','received'=>'Diterima','cancelled'=>'Dibatalkan'];
                        @endphp
                        <span class="badge {{ $badgeMap[$preOrder->status] ?? 'bg-secondary' }}" style="border-radius: 8px; padding:.5rem .75rem;">
                            {{ $labelMap[$preOrder->status] ?? ucfirst($preOrder->status) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">Supplier</label>
                        <div class="fw-semibold">{{ $preOrder->supplier->name }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">Nomor Referensi</label>
                        <div class="fw-semibold"><code>{{ $preOrder->reference_number ?? '-' }}</code></div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">Estimasi Kedatangan</label>
                        <div class="fw-semibold">
                            {{ $preOrder->expected_arrival_date ? $preOrder->expected_arrival_date->format('d M Y') : '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">Dibuat Oleh</label>
                        <div class="fw-semibold">{{ $preOrder->user->name }}</div>
                    </div>

                    <div class="mb-0">
                        <label class="small text-muted d-block mb-1">Catatan</label>
                        <div>{{ $preOrder->notes ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="col-md-8">
            <div class="card shadow-sm" style="border-radius: 14px; border: none;">
                <div class="card-header border-0 py-4 px-4">
                    <h5 class="fw-bold mb-0" style="color: var(--color-primary-dark);">Daftar Barang Pesanan</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: var(--brown-50);">
                                <tr>
                                    <th class="px-4 py-3" style="color: var(--color-primary-dark);">Produk</th>
                                    <th class="py-3 text-center" style="color: var(--color-primary-dark);">Satuan</th>
                                    <th class="py-3 text-center" style="color: var(--color-primary-dark);">Qty</th>
                                    <th class="py-3 text-center" style="color: var(--color-primary-dark);">Pcs/Unit</th>
                                    <th class="py-3" style="color: var(--color-primary-dark);">Harga Beli</th>
                                    <th class="py-3 text-end pe-4" style="color: var(--color-primary-dark);">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preOrder->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                        <small class="text-muted">{{ $item->product->barcode }}</small>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge" style="background: var(--brown-50); color: var(--color-primary); border: 1px solid var(--brown-100); padding:.4rem .8rem; border-radius: 20px;">
                                            {{ $item->unit_name }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-center fw-semibold">{{ $item->quantity }}</td>
                                    <td class="py-3 text-center">{{ $item->pcs_per_unit }}</td>
                                    <td class="py-3">
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        <div class="small text-muted">per Pcs (HPP)</div>
                                    </td>
                                    <td class="py-3 text-end pe-4">
                                        <div class="fw-bold" style="color: var(--color-primary-dark);">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                                        <small class="text-muted">{{ $item->quantity * $item->pcs_per_unit }} Pcs total</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background: var(--brown-50);">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-end fw-bold" style="color: var(--color-primary-dark);">Total Nilai Pesanan</td>
                                    <td class="py-3 text-end pe-4 fw-bold" style="color: var(--color-primary-dark); font-size: 1.1rem;">
                                        Rp {{ number_format($preOrder->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelector('.btn-receive-preorder')?.addEventListener('click', function () {
    confirmAction({
        title: 'Konfirmasi Penerimaan Barang',
        text: 'Pastikan semua barang sudah dihitung dan sesuai. Stok akan diperbarui secara otomatis.',
        icon: 'question',
        confirmButtonText: 'Ya, Terima Barang',
        confirmButtonColor: 'var(--color-success)'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('receive-form').submit();
    });
});
</script>
@endpush
@endsection
