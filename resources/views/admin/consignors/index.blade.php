@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
                <i class="fa-solid fa-handshake me-2"></i> Daftar Penitip (Konsinyasi)
            </h2>
            <p class="text-muted mb-0">Kelola semua penitip barang titip jual di toko Anda.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPenitip"
            style="background: var(--color-primary-dark); border: none; border-radius: 12px; padding: 0.6rem 1.4rem; font-weight: 600;">
            <i class="fa-solid fa-plus me-1"></i> Tambah Penitip
        </button>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3"><i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Filter --}}
    <div class="card shadow-sm mb-4" style="border-radius: 16px; border: none;">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Cari nama, telepon, atau email penitip..." style="border-radius: 10px;">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" style="border-radius: 10px;">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px;">
                        <i class="fa-solid fa-search me-1"></i> Cari
                    </button>
                    @if(request()->hasAny(['search','status']))
                        <a href="{{ route('admin.consignors.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Penitip --}}
    <div class="card shadow-sm" style="border-radius: 16px; border: none;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: var(--color-primary-dark); color: white;">
                        <tr>
                            <th class="ps-4 py-3">#</th>
                            <th>Nama Penitip</th>
                            <th>Kontak</th>
                            <th class="text-center">Komisi Toko</th>
                            <th class="text-center">Barang Aktif</th>
                            <th class="text-end">Pending Dibayar</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consignors as $c)
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $c->name }}</div>
                                @if($c->bank_name)
                                    <small class="text-muted"><i class="fa-solid fa-building-columns me-1"></i>{{ $c->bank_name }} — {{ $c->bank_account }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $c->phone ?? '-' }}
                                @if($c->email)<br><small class="text-muted">{{ $c->email }}</small>@endif
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" style="background: var(--color-primary-dark); font-size: .8rem; padding: .35rem .7rem;">
                                    {{ number_format($c->commission_rate, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold">{{ $c->active_items ?? 0 }}</span> barang
                            </td>
                            <td class="text-end fw-semibold" style="color: var(--color-warning);">
                                Rp {{ number_format($c->pending_settle_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($c->is_active)
                                    <span class="badge bg-success rounded-pill">Aktif</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill border-0" type="button" data-bs-toggle="dropdown" 
                                            style="width: 32px; height: 32px; padding: 0;">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 12px; font-size: 0.9rem;">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('admin.consignors.show', $c) }}">
                                                <i class="fa-solid fa-eye me-2 text-primary"></i> Detail
                                            </a>
                                        </li>
                                        <li>
                                            <button class="dropdown-item py-2" onclick="openEditModal({{ $c->id }}, {{ json_encode($c) }})">
                                                <i class="fa-solid fa-pen me-2 text-warning"></i> Edit
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider opacity-50"></li>
                                        <li>
                                            <form action="{{ route('admin.consignors.delete', $c->id) }}" method="POST" class="delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="dropdown-item py-2 text-danger btn-delete-consignor" data-name="{{ $c->name }}">
                                                    <i class="fa-solid fa-trash me-2"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-handshake fa-2x mb-2 d-block opacity-25"></i>
                                Belum ada penitip. Klik <strong>Tambah Penitip</strong> untuk memulai.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($consignors->hasPages())
        <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
            {{ $consignors->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ============================================================ --}}
{{-- Modal Tambah Penitip --}}
<div class="modal fade" id="modalTambahPenitip" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.consignors.store') }}" method="POST" class="modal-content" style="border-radius: 16px; border: none;">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: var(--color-primary-dark);">
                        <i class="fa-solid fa-handshake me-2"></i> Tambah Penitip Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    @include('admin.consignors._form')
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--color-primary-dark); border: none; border-radius: 10px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                    </button>
                </div>
            </form>
    </div>
</div>

{{-- Modal Edit Penitip --}}
<div class="modal fade" id="modalEditPenitip" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formEditPenitip" method="POST" class="modal-content" style="border-radius: 16px; border: none;">
                @csrf @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: var(--color-primary-dark);">
                        <i class="fa-solid fa-pen me-2"></i> Edit Penitip
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    @include('admin.consignors._form', ['edit' => true])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" style="border-radius: 10px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
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

document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert2 Delete Confirmation
    const deleteButtons = document.querySelectorAll('.btn-delete-consignor');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.delete-form');
            const name = this.getAttribute('data-name');

            Swal.fire({
                title: 'Hapus Penitip?',
                text: `Anda akan menghapus "${name}". Pastikan tidak ada barang aktif milik penitip ini. Aksi ini tidak bisa dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-trash me-2"></i>Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'artika-swal-popup',
                    title: 'artika-swal-title',
                    confirmButton: 'artika-swal-confirm-btn',
                    cancelButton: 'artika-swal-cancel-btn'
                },
                reverseButtons: true,
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
