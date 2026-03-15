<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Konsinyasi {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</title>
    <style>
        @page { margin: 1.2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; }
        
        .w-100 { width: 100%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .primary-color { color: #85695a; }
        
        /* Branding & Header */
        .header-table { margin-bottom: 25px; border-bottom: 3px solid #85695a; padding-bottom: 15px; }
        .brand-name { font-size: 26px; font-weight: 800; color: #85695a; margin: 0; letter-spacing: -1px; }
        .brand-tagline { font-size: 9px; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        
        .doc-title-container { background: #f8fafc; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
        .doc-title { font-size: 16px; font-weight: bold; margin: 0; color: #334155; text-align: center; text-transform: uppercase; }
        
        /* Summary Grid Table */
        .summary-table { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px 25px -8px; }
        .summary-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 10px; text-align: center; }
        .summary-label { font-size: 8px; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: block; font-weight: bold; }
        .summary-value { font-size: 12px; font-weight: 800; color: #1e293b; }
        .summary-value.highlight { color: #85695a; }

        /* Tables */
        .section-header { margin: 25px 0 10px 0; border-left: 4px solid #85695a; padding-left: 10px; }
        .section-title { font-size: 11px; font-weight: 800; color: #334155; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border-radius: 8px; overflow: hidden; }
        .data-table th { background-color: #85695a; color: white; text-align: left; padding: 12px 10px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; border: none; }
        .data-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 10px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        
        /* Signature */
        .signature-section { margin-top: 50px; }
        .sig-box { width: 220px; text-align: center; }
        .sig-line { border-top: 1px solid #94a3b8; margin-top: 60px; margin-bottom: 5px; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding-top: 15px; border-top: 1px solid #f1f5f9; text-align: center; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <table class="w-100 header-table">
        <tr>
            <td style="width: 65%;">
                <h1 class="brand-name">{{ \App\Models\Setting::get('store_name', 'ARTIKA POS') }}</h1>
                <p class="brand-tagline">Premium Retail Solutions & Management</p>
                <p class="text-muted" style="margin-top: 6px; font-size: 9px; line-height: 1.4;">
                    {{ \App\Models\Setting::get('store_address', 'Jl. Raya Utama No. 123, Kota Anda') }}<br>
                    Telp: {{ \App\Models\Setting::get('store_phone', '(021) 1234567') }} · Email: {{ \App\Models\Setting::get('store_email', 'hello@artikapos.com') }}
                </p>
            </td>
            <td class="text-right" style="width: 35%; vertical-align: top;">
                <div style="font-size: 14px; font-weight: bold; color: #64748b; letter-spacing: 1px;">LAPORAN KONSINYASI</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</div>
                <div style="font-size: 9px; color: #cbd5e1; margin-top: 2px;">Dihasilkan: {{ now()->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <div class="doc-title-container">
        <h2 class="doc-title">RINGKASAN AKTIVITAS PENJUALAN</h2>
    </div>

    <table class="summary-table">
        <tr>
            <td style="width: 20%;">
                <div class="summary-card">
                    <span class="summary-label">Unit Terjual</span>
                    <div class="summary-value">{{ number_format($summary['total_qty'], 0) }}</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="summary-card">
                    <span class="summary-label">Total Penjualan</span>
                    <div class="summary-value">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="summary-card">
                    <span class="summary-label">Komisi Toko</span>
                    <div class="summary-value" style="color: #e11d48;">Rp {{ number_format($summary['total_commission'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="summary-card" style="background: #fdf4ff; border-color: #f5d0fe;">
                    <span class="summary-label" style="color: #701a75;">Hak Penitip</span>
                    <div class="summary-value highlight">Rp {{ number_format($summary['total_to_pay'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="summary-card">
                    <span class="summary-label">Piutang Berjalan</span>
                    <div class="summary-value">Rp {{ number_format($summary['pending_pay'], 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-header">
        <h3 class="section-title">Penjualan Per Penitip</h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Nama Penitip</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 18%;">Subtotal Sales</th>
                <th class="text-center" style="width: 12%;">Komisi (%)</th>
                <th class="text-right" style="width: 25%;">Dana Penitip (Net)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consignorStats as $stat)
            <tr>
                <td class="fw-bold">{{ $stat['consignor']->name }}</td>
                <td class="text-center">{{ number_format($stat['qty'], 0) }}</td>
                <td class="text-right">Rp {{ number_format($stat['sales'], 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($stat['rate'], 1) }}%</td>
                <td class="text-right fw-bold" style="color: #85695a;">Rp {{ number_format($stat['to_pay'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-header">
        <h3 class="section-title">Daftar Produk Terlaris</h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 45%;">Nama Produk</th>
                <th style="width: 25%;">Pemilik / Penitip</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 20%;">Total Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $p)
            <tr>
                <td class="fw-bold">{{ $p->product_name }}</td>
                <td class="text-muted">{{ $p->consignor_name }}</td>
                <td class="text-center">{{ number_format($p->total_qty, 0) }}</td>
                <td class="text-right">Rp {{ number_format($p->total_sales, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <table class="w-100">
            <tr>
                <td class="sig-box">
                    <div class="text-muted">Disusun Oleh,</div>
                    <div class="sig-line"></div>
                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                    <div class="text-muted small" style="font-size: 8px;">Staff Administrasi</div>
                </td>
                <td></td>
                <td class="sig-box">
                    <div class="text-muted">Mengetahui,</div>
                    <div class="sig-line"></div>
                    <div class="fw-bold">Manager Operasional</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dihasilkan secara otomatis oleh ARTIKA POS Core Engine pada {{ now()->format('d M Y, H:i') }}.<br>
        Dokumen ini adalah laporan internal yang sah dan diproses secara real-time dari data transaksi POS.
    </div>
</body>
</html>
