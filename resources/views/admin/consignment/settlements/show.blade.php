@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('admin.consignment.settlements.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Pembayaran
        </a>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
                    <i class="fa-solid fa-receipt me-2"></i> Detail Pembayaran: {{ $settlement->reference_no }}
                </h2>
                <p class="text-muted mb-0">{{ $settlement->period_start->format('d M Y') }} – {{ $settlement->period_end->format('d M Y') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.consignment.settlements.pdf', $settlement) }}" target="_blank" class="btn btn-outline-secondary" style="border-radius: 10px;">
                    <i class="fa-solid fa-file-pdf me-1"></i> Cetak PDF
                </a>
                @if($settlement->status === 'pending')
                <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBayar"
                    style="border-radius: 10px; font-weight: 600; border: none;">
                    <i class="fa-solid fa-money-bill-wave me-1"></i> Tandai Sudah Dibayar
                </button>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-3 mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-3 mb-3">{{ session('error') }}</div>
    @endif

    {{-- Status Banner --}}
    @if($settlement->status === 'paid')
    <div class="alert border-0 mb-4 d-flex align-items-center gap-3" style="background: #e8f5e9; border-radius: 14px;">
        <i class="fa-solid fa-circle-check fa-2x" style="color: #2e7d32;"></i>
        <div>
            <div class="fw-bold" style="color: #2e7d32;">Pembayaran Lunas</div>
            <div class="text-muted" style="font-size: .9rem;">
                Dibayar via <strong>{{ $settlement->payment_method }}</strong>
                {{ $settlement->paid_at?->format('d M Y, H:i') }}
                @if($settlement->payment_reference) · Ref: {{ $settlement->payment_reference }} @endif
                · oleh {{ $settlement->paidBy?->name ?? '-' }}
            </div>
        </div>
    </div>
    @else
    <div class="alert border-0 mb-4 d-flex align-items-center gap-3" style="background: #fffde7; border-radius: 14px;">
        <i class="fa-solid fa-clock fa-2x" style="color: #f9a825;"></i>
        <div class="fw-semibold" style="color: #e65100;">Menunggu Pembayaran</div>
    </div>
    @endif

    <div class="row g-4">
        {{-- Ringkasan --}}
        <div class="col-md-5">
            <div class="card shadow-sm h-100" style="border-radius: 14px; border: none;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3" style="color: var(--color-primary-dark);">Ringkasan</h5>

                    <div class="mb-3">
                        <div class="text-muted" style="font-size: .85rem;">Penitip</div>
                        <div class="fw-semibold fs-5">{{ $settlement->consignor->name }}</div>
                        @if($settlement->consignor->bank_name)
                        <div class="text-muted">{{ $settlement->consignor->bank_name }} — {{ $settlement->consignor->bank_account }}</div>
                        <div class="text-muted">a/n {{ $settlement->consignor->bank_holder }}</div>
                        @endif
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Unit Terjual</span>
                        <span class="fw-semibold">{{ number_format($settlement->total_sold_qty, 0) }} unit</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Penjualan</span>
                        <span class="fw-semibold">Rp {{ number_format($settlement->total_sales_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Komisi Toko</span>
                        <span class="fw-semibold text-warning">- Rp {{ number_format($settlement->commission_amount, 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold fs-5">Dibayarkan</span>
                        <span class="fw-bold fs-5" style="color: var(--color-primary-dark);">Rp {{ number_format($settlement->amount_to_pay, 0, ',', '.') }}</span>
                    </div>

                    <div class="mt-3 text-muted" style="font-size: .8rem;">
                        Dibuat oleh: {{ $settlement->createdBy?->name ?? '-' }} · {{ $settlement->created_at->format('d M Y, H:i') }}
                    </div>
                    @if($settlement->notes)
                    <div class="mt-2 p-2 rounded-3" style="background: #f8fafc; font-size: .85rem;">
                        <i class="fa-solid fa-note-sticky me-1 text-muted"></i> {{ $settlement->notes }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Detail Penjualan --}}
        <div class="col-md-7">
            <div class="card shadow-sm" style="border-radius: 14px; border: none;">
                <div class="card-header border-0 pt-4 px-4 pb-2">
                    <h5 class="fw-semibold mb-0" style="color: var(--color-primary-dark);">Detail Penjualan Per Produk</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($soldItems as $productId => $settlementItems)
                                @php
                                    $firstItem = $settlementItems->first();
                                    $product   = $firstItem->consignmentItem->product;
                                    $totalQty  = $settlementItems->sum('quantity');
                                    $totalVal  = $settlementItems->sum('amount_to_pay');
                                    $totalSales = $settlementItems->sum(fn($i) => $i->quantity * $i->price);
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $product->name ?? '-' }}</div>
                                        <div class="text-muted small">
                                            @foreach($settlementItems as $si)
                                                Batch {{ $si->consignmentItem->received_at->format('d/m/y') }} ({{ number_format($si->quantity, 0) }} unit)@if(!$loop->last), @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace">{{ number_format($totalQty, 0) }}</td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">Detail transaksi tidak ditemukan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Bayar --}}
<div class="modal fade" id="modalBayar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <form action="{{ route('admin.consignment.settlements.pay', $settlement) }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: var(--color-primary-dark);">
                        <i class="fa-solid fa-money-bill-wave me-2"></i> Konfirmasi Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="p-3 mb-3 rounded-3 text-center" style="background: #e8f5e9;">
                        <div class="text-muted" style="font-size: .85rem;">Jumlah yang dibayarkan ke</div>
                        <div class="fw-bold fs-4" style="color: #2e7d32;">{{ $settlement->consignor->name }}</div>
                        <div class="fw-bold fs-3" style="color: var(--color-primary-dark);">Rp {{ number_format($settlement->amount_to_pay, 0, ',', '.') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Metode Pembayaran *</label>
                        <select name="payment_method" class="form-select" required style="border-radius: 10px;">
                            <option value="">— Pilih —</option>
                            <option value="Cash">Cash / Tunai</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Cek">Cek</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. Bukti Pembayaran (opsional)</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="No. kwitansi / bukti transfer" style="border-radius: 10px;">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" style="border-radius: 10px; font-weight: 600;">
                        <i class="fa-solid fa-circle-check me-1"></i> Tandai Lunas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
