@php
    $paperSize = $paperSize ?? \App\Models\Setting::get('receipt_paper_size', '58mm');
    $storeName = \App\Models\Setting::get('store_name', 'ARTIKA Minimarket');
    $storeAddress = \App\Models\Setting::get('store_address', '');
    $siteLogo = \App\Models\Setting::get('site_logo', 'img/logo2.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Struk PWA - {{ $order->order_no }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    {!! \App\Helpers\ThemeHelper::getCssVariables(\App\Models\Setting::get('site_color_theme', 'brown')) !!}
    <style>
        :root {
            --receipt-width: {{ $paperSize === '80mm' ? '80mm' : '58mm' }};
            --receipt-padding: {{ $paperSize === '80mm' ? '4mm' : '3mm' }};
            --font-size-base: {{ $paperSize === '80mm' ? '10px' : '9px' }};
            --font-size-store: {{ $paperSize === '80mm' ? '14px' : '12px' }};
            --font-size-total: {{ $paperSize === '80mm' ? '12px' : '11px' }};
            --font-size-details: {{ $paperSize === '80mm' ? '9px' : '8px' }};
            --logo-width: {{ $paperSize === '80mm' ? '80px' : '65px' }};
        }

        @page { margin: 0; size: var(--receipt-width) auto; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Arial', sans-serif;
            width: 100%;
            max-width: var(--receipt-width);
            margin: 0 auto;
            padding: 5px 0;
            font-size: var(--font-size-base);
            font-weight: 500;
            line-height: 1.0;
            background: #f0f1f2;
            overflow-x: hidden;
            word-break: break-all;
            color: #000;
        }

        .receipt {
            width: 100%;
            background: white;
            padding: 8mm var(--receipt-padding);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 0 auto;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .logo-container { text-align: center; margin-bottom: 5px; }

        .logo {
            max-width: var(--logo-width);
            height: auto;
            filter: grayscale(100%);
        }

        .store-name {
            font-size: var(--font-size-store);
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .store-info {
            font-size: calc(var(--font-size-base) + 0.5px);
            margin-bottom: 2px;
        }

        /* PWA Order Badge */
        .pwa-badge {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: calc(var(--font-size-base) - 1px);
            font-weight: bold;
            margin: 5px 0;
            letter-spacing: 0.05em;
        }

        .order-info {
            margin: 5px 0;
            font-size: var(--font-size-base);
            font-weight: 500;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }

        .order-info div {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .order-info .label { font-weight: 500; }
        .order-info .value { font-weight: 700; text-align: right; }

        .customer-info {
            margin: 5px 0;
            padding: 4px 0;
            border-bottom: 1px dashed #000;
        }

        .customer-info div {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .items-table { width: 100%; margin: 10px 0; }

        .item-row { margin-bottom: 2px; width: 100%; }

        .item-main {
            display: flex;
            justify-content: space-between;
            font-weight: 800;
            width: 100%;
        }

        .item-name { flex: 1; padding-right: 5px; }
        .item-subtotal { white-space: nowrap; }

        .item-details {
            font-size: var(--font-size-details);
            color: #000;
            margin-top: 0;
            display: flex;
            justify-content: space-between;
        }

        .divider {
            border-top: 1px solid #000;
            margin: 4px 0;
            width: 100%;
        }

        .divider-dashed {
            border-top: 1px dashed #000;
            margin: 4px 0;
            width: 100%;
        }

        .totals { margin: 8px 0; }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .total-row.grand-total {
            font-size: var(--font-size-total);
            font-weight: 800;
            border-top: 1.5px solid #000;
            padding-top: 6px;
            margin-top: 6px;
        }

        .footer {
            text-align: center;
            margin-top: 8px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: var(--font-size-details);
        }

        /* Action Buttons */
        .action-buttons {
            position: fixed;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
            z-index: 100;
        }

        .action-buttons .btn-action {
            padding: 10px 16px;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print { background: var(--color-primary); }
        .btn-print:hover { background: var(--color-primary-dark); }

        .back-button {
            position: fixed;
            top: 10px;
            left: 10px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            font-size: 13px;
            z-index: 100;
        }

        .back-button:hover { background: #5a6268; }

        .paper-size-badge {
            position: fixed;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-family: system-ui, sans-serif;
            z-index: 100;
        }

        @media print {
            .no-print { display: none !important; }
            body {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            .receipt {
                width: 100% !important;
                box-shadow: none !important;
                padding: 2mm 5mm 2mm 1mm !important;
                min-height: auto !important;
            }
            * { color: #000 !important; background: transparent !important; box-shadow: none !important; }
        }

        @media screen and (max-width: 480px) {
            .action-buttons {
                top: auto; bottom: 0; left: 0; right: 0;
                flex-direction: row; justify-content: center;
                background: rgba(255,255,255,0.95);
                padding: 12px;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            }
            .action-buttons .btn-action { flex: 1; justify-content: center; }
            .back-button { position: relative; display: block; width: calc(100% - 20px); margin: 10px auto; text-align: center; }
            body { padding-bottom: 80px; }
        }
    </style>
</head>
<body>
    <a href="{{ route('pos.pwa-orders') }}" class="back-button no-print">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <div class="action-buttons no-print">
        <button class="btn-action btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> <span>Cetak Struk</span>
        </button>
    </div>

    <div class="paper-size-badge no-print">
        <i class="fas fa-ruler-horizontal"></i> {{ $paperSize }}
    </div>

    <div class="receipt" id="receiptContent">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <img src="{{ asset($siteLogo) }}" alt="Logo" class="logo">
            </div>
            <div class="store-name">{{ $storeName }}</div>
            <div class="store-info">{{ $storeAddress }}</div>
            <div class="pwa-badge">📱 PESANAN ONLINE (PWA)</div>
        </div>

        <!-- Order Info -->
        <div class="order-info">
            <div>
                <span class="label">No. Pesanan</span>
                <span class="value">{{ $order->order_no }}</span>
            </div>
            <div>
                <span class="label">Waktu:</span>
                <span class="value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($order->processedBy)
            <div>
                <span class="label">Kasir:</span>
                <span class="value">{{ $order->processedBy->name }}</span>
            </div>
            @endif
        </div>

        <!-- Customer Info -->
        <div class="customer-info">
            <div>
                <span class="label">Pemesan:</span>
                <span class="value">{{ $order->customer_name }}</span>
            </div>
            <div>
                <span class="label">WA:</span>
                <span class="value">{{ $order->customer_whatsapp }}</span>
            </div>
            <div>
                <span class="label">Lokasi:</span>
                <span class="value">{{ $order->delivery_location }}</span>
            </div>
        </div>

        <!-- Items -->
        <div class="items-table">
            @foreach($order->items as $item)
                <div class="item-row">
                    <div class="item-main" style="text-transform: uppercase; font-weight: 550;">
                        <span>{{ $item->product_name }}</span>
                    </div>
                    <div class="item-details">
                        <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                        <span>Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Summary -->
        <div style="margin: 5px 0; font-size: var(--font-size-details); font-weight: 670;">
            <div class="total-row">
                <span>Total Item:</span>
                <span>{{ $order->items->count() }}</span>
            </div>
            <div class="total-row">
                <span>Total Qty:</span>
                <span>{{ $order->items->sum('quantity') }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Payment Status -->
        <div style="margin: 5px 0; text-align: center; font-weight: bold; font-size: var(--font-size-total);">
            @if($order->status === 'completed')
                ✅ LUNAS
            @elseif($order->status === 'processing')
                ⏳ BELUM BAYAR
            @elseif($order->status === 'cancelled')
                ❌ DIBATALKAN
            @else
                ⏳ MENUNGGU
            @endif
        </div>

        @if($order->notes)
        <div class="divider-dashed"></div>
        <div style="margin: 5px 0; font-size: var(--font-size-details);">
            <strong>Catatan:</strong> {{ $order->notes }}
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 5px 0;">Terima kasih telah memesan!</p>
            <p style="margin: 5px 0;">{{ $storeName }}</p>
            <p style="margin: 10px 0 5px 0;">{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <script>
        // Auto-print if parameter is set
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const isMobile = /Android|webOS|iPhone|iPad|iPod/i.test(navigator.userAgent);
            if (urlParams.has('auto_print') && !isMobile) {
                setTimeout(() => window.print(), 500);
                window.onafterprint = () => window.close();
            }
        }
    </script>
</body>
</html>
