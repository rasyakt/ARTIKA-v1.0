<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Pembayaran {{ $settlement->reference_no }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; }
        
        /* Layout Helpers */
        .w-100 { width: 100%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .primary-color { color: #85695a; }
        
        /* Branding & Header */
        .header-table { margin-bottom: 30px; border-bottom: 3px solid #85695a; padding-bottom: 20px; }
        .brand-name { font-size: 28px; font-weight: 800; color: #85695a; margin: 0; letter-spacing: -1px; }
        .brand-tagline { font-size: 10px; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        
        .doc-title-container { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
        .doc-title { font-size: 18px; font-weight: bold; margin: 0; color: #334155; text-align: center; }
        
        /* Info Sections */
        .info-grid { margin-bottom: 30px; width: 100%; border-collapse: collapse; }
        .info-col { width: 50%; vertical-align: top; padding-right: 20px; }
        .section-label { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; display: block; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; }
        
        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; border-radius: 8px; overflow: hidden; }
        .data-table th { background-color: #85695a; color: white; text-align: left; padding: 12px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; border: none; }
        .data-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .data-table tr:nth-child(even) { background-color: #fafafa; }
        
        /* Summary Section */
        .summary-wrapper { float: right; width: 45%; margin-top: 10px; }
        .summary-row { padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
        .summary-total { margin-top: 10px; padding: 15px; background: #85695a; color: white; border-radius: 8px; }
        .total-amount { font-size: 20px; font-weight: bold; }
        
        /* Footer & Signatures */
        .footer-section { margin-top: 60px; clear: both; }
        .signature-table { width: 100%; margin-top: 40px; }
        .sig-line { border-top: 1px solid #cbd5e1; width: 180px; margin: 50px auto 5px auto; }
        
        .status-stamp { border: 3px double #2e7d32; color: #2e7d32; padding: 5px 15px; font-weight: bold; font-size: 14px; text-transform: uppercase; transform: rotate(-10deg); display: inline-block; opacity: 0.8; margin-top: 20px; }
        .status-stamp.pending { border-color: #f57f17; color: #f57f17; }
        
        .note-box { background: #fffcf0; border-left: 4px solid #fbbf24; padding: 10px; font-size: 10px; color: #92400e; margin-top: 30px; }
    </style>
</head>
<body>
    <table class="w-100 header-table">
        <tr>
            <td style="width: 60%;">
                <h1 class="brand-name">{{ \App\Models\Setting::get('store_name', 'ARTIKA POS') }}</h1>
                <p class="brand-tagline">Premium Retail Solutions & Management</p>
                <p class="text-muted" style="margin-top: 8px; font-size: 9px;">
                    {{ \App\Models\Setting::get('store_address', 'Jl. Raya Utama No. 123, Kota Anda') }}<br>
                    Telp: {{ \App\Models\Setting::get('store_phone', '(021) 1234567') }} · Email: {{ \App\Models\Setting::get('store_email', 'hello@artikapos.com') }}
                </p>
            </td>
            <td class="text-right" style="width: 40%; vertical-align: top;">
                <div style="font-size: 14px; font-weight: bold; color: #64748b;">BUKTI PEMBAYARAN KONSINYASI</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Ref: {{ $settlement->reference_no }}</div>
                <div style="font-size: 11px; color: #94a3b8;">Tgl: {{ $settlement->created_at->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="doc-title-container">
        <h2 class="doc-title">RINGKASAN PELUNASAN KONSINYASI</h2>
        <div class="text-center text-muted" style="font-size: 10px; margin-top: 5px;">
            PERIODE PENJUALAN: {{ $settlement->period_start->format('d M Y') }} - {{ $settlement->period_end->format('d M Y') }}
        </div>
    </div>

    <table class="info-grid w-100">
        <tr>
            <td class="info-col">
                <span class="section-label">PENITIP / CONSIGNOR</span>
                <div style="font-size: 13px; font-weight: bold; margin-bottom: 2px;">{{ $settlement->consignor->name }}</div>
                @if($settlement->consignor->phone) <div class="text-muted">Telp: {{ $settlement->consignor->phone }}</div> @endif
                @if($settlement->consignor->email) <div class="text-muted">Email: {{ $settlement->consignor->email }}</div> @endif
            </td>
            <td class="info-col">
                <span class="section-label">DETAIL PEMBAYARAN BANK</span>
                @if($settlement->consignor->bank_name)
                    <div class="fw-bold">{{ $settlement->consignor->bank_name }}</div>
                    <div>No. Rek: {{ $settlement->consignor->bank_account }}</div>
                    <div>a/n {{ $settlement->consignor->bank_holder }}</div>
                @else
                    <div class="text-muted" style="font-style: italic;">Informasi rekening tidak tersedia</div>
                @endif
            </td>
        </tr>
    </table>

    <span class="section-label">RINCIAN PENJUALAN PRODUK</span>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 55%;">Nama Produk</th>
                <th class="text-center" style="width: 15%;">Unit</th>
                <th class="text-right" style="width: 30%;">Total Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($soldItems as $productId => $settlementItems)
            @php
                $firstItem = $settlementItems->first();
                $product   = $firstItem->consignmentItem->product;
                $totalQty  = $settlementItems->sum('quantity');
                $totalVal  = $settlementItems->sum(fn($i) => $i->quantity * $i->price);
            @endphp
            <tr>
                <td>
                    <div class="fw-bold">{{ $product->name ?? '-' }}</div>
                    <div class="text-muted" style="font-size: 8px; margin-top: 4px;">
                        @foreach($settlementItems as $si)
                            Batch {{ $si->consignmentItem->received_at->format('d/m/y') }} ({{ number_format($si->quantity, 0) }} unit)@if(!$loop->last), @endif
                        @endforeach
                    </div>
                </td>
                <td class="text-center">{{ number_format($totalQty, 0) }}</td>
                <td class="text-right">Rp {{ number_format($totalVal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="w-100">
        <div style="width: 50%; float: left;">
            @if($settlement->status === 'paid')
                <div class="status-stamp">LUNAS / PAID</div>
                <div class="text-muted" style="font-size: 8px; margin-top: 5px;">
                    Dibayar via: {{ $settlement->payment_method }}<br>
                    Ref: {{ $settlement->payment_reference ?? '-' }}
                </div>
            @else
                <div class="status-stamp pending">DRAFT / PENDING</div>
            @endif

            @if($settlement->notes)
                <div class="note-box">
                    <strong>Catatan:</strong> {{ $settlement->notes }}
                </div>
            @endif
        </div>
        
        <div class="summary-wrapper text-right">
            <div class="summary-row">
                <span style="float: left;" class="text-muted">Volume Terjual</span>
                <span class="fw-bold">{{ number_format($settlement->total_sold_qty, 0) }} Unit</span>
                <div style="clear: both;"></div>
            </div>
            <div class="summary-row">
                <span style="float: left;" class="text-muted">Subtotal Penjualan</span>
                <span class="fw-bold">Rp {{ number_format($settlement->total_sales_amount, 0, ',', '.') }}</span>
                <div style="clear: both;"></div>
            </div>
            <div class="summary-row" style="color: #e11d48;">
                <span style="float: left;" class="text-muted">Komisi Toko</span>
                <span>- Rp {{ number_format($settlement->commission_amount, 0, ',', '.') }}</span>
                <div style="clear: both;"></div>
            </div>
            <div class="summary-total">
                <div style="font-size: 9px; text-transform: uppercase; margin-bottom: 5px; opacity: 0.8;">Total Dana Penitip</div>
                <div class="total-amount">Rp {{ number_format($settlement->amount_to_pay, 0, ',', '.') }}</div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table class="signature-table text-center">
        <tr>
            <td style="width: 33%;">
                <div class="text-muted" style="margin-bottom: 10px;">Dibuat Oleh,</div>
                <div class="sig-line"></div>
                <div class="fw-bold">{{ $settlement->createdBy->name ?? 'Admin' }}</div>
            </td>
            <td style="width: 33%;"></td>
            <td style="width: 33%;">
                <div class="text-muted" style="margin-bottom: 10px;">Penitip / Penerima,</div>
                <div class="sig-line"></div>
                <div class="fw-bold">{{ $settlement->consignor->name }}</div>
            </td>
        </tr>
    </table>

    <div style="position: absolute; bottom: 0; left: 0; right: 0; padding-top: 20px; border-top: 1px solid #f1f5f9; text-align: center; font-size: 8px; color: #94a3b8;">
        Dihasilkan secara sistem pada {{ now()->format('d M Y, H:i:s') }} oleh ARTIKA POS Core Engine.<br>
        Ini adalah dokumen sah dan tidak memerlukan tanda tangan basah jika dikirim secara elektronik.
    </div>
</body>
</html>
