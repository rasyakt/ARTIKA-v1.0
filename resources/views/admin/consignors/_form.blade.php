{{-- Partial form fields untuk tambah / edit penitip --}}
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nama Penitip *</label>
        <input type="text" name="name" class="form-control" required
            value="{{ old('name', $consignor->name ?? '') }}"
            placeholder="Nama lengkap penitip / toko" style="border-radius: 10px;">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Komisi Toko (%) *
            <span class="text-muted fw-normal" style="font-size: .8rem;">— toko ambil berapa % dari penjualan</span>
        </label>
        <div class="input-group">
            <input type="number" name="commission_rate" class="form-control" required min="0" max="100" step="0.5"
                value="{{ old('commission_rate', $consignor->commission_rate ?? 0) }}"
                placeholder="cth: 20" style="border-radius: 10px 0 0 10px;">
            <span class="input-group-text" style="border-radius: 0 10px 10px 0;">%</span>
        </div>
        <small class="text-muted">0% = tidak ambil komisi (bayar penuh ke penitip)</small>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">No. Telepon</label>
        <input type="text" name="phone" class="form-control"
            value="{{ old('phone', $consignor->phone ?? '') }}"
            placeholder="08xxx" style="border-radius: 10px;">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control"
            value="{{ old('email', $consignor->email ?? '') }}"
            placeholder="email@penitip.com" style="border-radius: 10px;">
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Alamat</label>
        <textarea name="address" class="form-control" rows="2"
            placeholder="Alamat lengkap penitip" style="border-radius: 10px;">{{ old('address', $consignor->address ?? '') }}</textarea>
    </div>

    {{-- Info Bank --}}
    <div class="col-12">
        <div class="p-3 mb-1" style="background: #f0f7ff; border-radius: 12px; border: 1px dashed #90caf9;">
            <div class="fw-semibold mb-2" style="color: #1565c0;"><i class="fa-solid fa-building-columns me-2"></i>Informasi Rekening Bank (untuk transfer pembayaran)</div>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="bank_name" class="form-control"
                        value="{{ old('bank_name', $consignor->bank_name ?? '') }}"
                        placeholder="Nama Bank (BCA, BRI, dll)" style="border-radius: 10px;">
                </div>
                <div class="col-md-4">
                    <input type="text" name="bank_account" class="form-control"
                        value="{{ old('bank_account', $consignor->bank_account ?? '') }}"
                        placeholder="Nomor Rekening" style="border-radius: 10px;">
                </div>
                <div class="col-md-4">
                    <input type="text" name="bank_holder" class="form-control"
                        value="{{ old('bank_holder', $consignor->bank_holder ?? '') }}"
                        placeholder="Nama Pemilik Rekening" style="border-radius: 10px;">
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Catatan</label>
        <textarea name="notes" class="form-control" rows="2"
            placeholder="Catatan tambahan (opsional)" style="border-radius: 10px;">{{ old('notes', $consignor->notes ?? '') }}</textarea>
    </div>

    @if(isset($edit) && $edit)
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                {{ old('is_active', $consignor->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Penitip Aktif</label>
        </div>
    </div>
    @endif
</div>
