@php
    $storeName = App\Models\Setting::get('store_name', 'ARTIKA Minimarket');
    $storeLogo = App\Models\Setting::get('site_logo', 'img/logo2.png');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#6F5849">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="description" content="Pesan makanan & minuman dari {{ $storeName }} langsung dari HP kamu!">
    <title>{{ $storeName }} — Pesan Online</title>
    <link rel="icon" type="image/png" href="{{ asset($storeLogo) }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/img/icons/icon-192x192.png">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #6F5849;
            --primary-dark: #5A4638;
            --primary-light: #8B7265;
            --primary-50: #F5F0ED;
            --primary-100: #E8DFD9;
            --accent: #D4A574;
            --accent-light: #E8C9A8;
            --success: #22C55E;
            --success-dark: #16A34A;
            --danger: #EF4444;
            --danger-dark: #DC2626;
            --warning: #F59E0B;
            --info: #3B82F6;
            --bg: #FAF8F6;
            --bg-card: #FFFFFF;
            --bg-glass: rgba(255, 255, 255, 0.85);
            --text: #1F1B18;
            --text-muted: #78716C;
            --text-light: #A8A29E;
            --border: #E7E5E4;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.08);
            --radius: 16px;
            --radius-sm: 10px;
            --radius-full: 9999px;
            --nav-height: 60px;
            --cart-bar-height: 70px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ======================== */
        /*   STORE STATUS BANNER    */
        /* ======================== */
        .store-status-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 8px 16px;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .store-status-banner.open {
            background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
            color: white;
        }

        .store-status-banner.closed {
            background: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%);
            color: white;
        }

        .store-status-banner .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.5); }
        }

        /* ======================== */
        /*     NAVIGATION BAR       */
        /* ======================== */
        .top-nav {
            position: fixed;
            top: 32px;
            left: 0;
            right: 0;
            height: var(--nav-height);
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            z-index: 999;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 12px;
        }

        .nav-logo {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            object-fit: cover;
        }

        .nav-title {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--primary-dark);
            flex: 1;
        }

        .nav-user-btn {
            background: var(--primary);
            color: white;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .nav-user-btn:hover { background: var(--primary-dark); transform: scale(1.05); }

        .nav-cart-btn {
            background: none;
            border: 2px solid var(--primary);
            color: var(--primary);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
        }

        .nav-cart-btn:hover { background: var(--primary); color: white; }

        .cart-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            min-width: 18px;
            height: 18px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            transition: transform 0.3s;
        }

        .cart-badge.bounce { animation: badge-bounce 0.4s ease; }

        @keyframes badge-bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }

        /* ======================== */
        /*      MAIN CONTENT        */
        /* ======================== */
        .main {
            padding-top: calc(32px + var(--nav-height) + 16px);
            padding-bottom: calc(var(--cart-bar-height) + 24px);
            min-height: 100vh;
        }

        .section-padding { padding: 0 16px; }

        /* ======================== */
        /*    LOGIN SCREEN (MODAL)  */
        /* ======================== */
        .login-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            padding: 36px 28px;
            width: 100%;
            max-width: 400px;
            box-shadow: var(--shadow-xl);
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-logo {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            object-fit: cover;
            display: block;
            margin: 0 auto 16px;
        }

        .login-title {
            text-align: center;
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 4px;
        }

        .login-subtitle {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: 0.01em;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--text);
            background: var(--bg);
            transition: all 0.3s;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(111, 88, 73, 0.1);
        }

        .form-input::placeholder { color: var(--text-light); }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:hover { transform: translateY(-1px); box-shadow: var(--shadow-lg); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* ======================== */
        /*      SEARCH BAR          */
        /* ======================== */
        .search-wrapper {
            padding: 0 16px;
            margin-bottom: 16px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 0 16px;
            transition: all 0.3s;
            box-shadow: var(--shadow-sm);
        }

        .search-box:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(111, 88, 73, 0.08);
        }

        .search-box i { color: var(--text-muted); font-size: 0.95rem; }

        .search-box input {
            flex: 1;
            border: none;
            outline: none;
            padding: 14px 12px;
            font-size: 0.9rem;
            font-family: inherit;
            background: none;
            color: var(--text);
        }

        .search-box input::placeholder { color: var(--text-light); }

        /* ======================== */
        /*   CATEGORY CHIPS         */
        /* ======================== */
        .category-scroll {
            padding: 0 16px;
            margin-bottom: 20px;
            overflow-x: auto;
            display: flex;
            gap: 8px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .category-scroll::-webkit-scrollbar { display: none; }

        .category-chip {
            flex-shrink: 0;
            padding: 8px 18px;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
            border: 2px solid var(--border);
            background: white;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .category-chip:hover { border-color: var(--primary-light); color: var(--primary); }

        .category-chip.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* ======================== */
        /*      PRODUCT GRID        */
        /* ======================== */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            padding: 0 16px;
        }

        @media (min-width: 640px) { .product-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; } }
        @media (min-width: 1024px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }

        .product-card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .product-card.out-of-stock { opacity: 0.55; pointer-events: none; }

        .product-img-wrap {
            aspect-ratio: 1;
            background: var(--primary-50);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .product-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .product-card:hover .product-img-wrap img { transform: scale(1.05); }

        .product-img-placeholder {
            font-size: 2.5rem;
            color: var(--primary-light);
            opacity: 0.4;
        }

        .stock-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            padding: 3px 8px;
            border-radius: var(--radius-full);
            font-size: 0.65rem;
            font-weight: 700;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(4px);
        }

        .stock-badge.low { color: var(--warning); }
        .stock-badge.out { color: var(--danger); background: rgba(239,68,68,0.1); }

        .product-info {
            padding: 12px;
        }

        .product-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 6px;
            min-height: 2.1em;
        }

        .product-category-label {
            font-size: 0.65rem;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .product-price {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .product-price small {
            font-size: 0.65rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .add-to-cart-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            gap: 6px;
        }

        .add-to-cart-btn:hover { background: var(--primary-dark); }

        .add-to-cart-btn:active { transform: scale(0.97); }

        .add-to-cart-btn.added {
            background: var(--success);
            animation: addPop 0.3s ease;
        }

        @keyframes addPop {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Qty controls (shown when item is in cart) */
        .qty-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-top: 10px;
            background: var(--primary-50);
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .qty-btn {
            width: 40px;
            height: 38px;
            border: none;
            background: none;
            color: var(--primary);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .qty-btn:hover { background: var(--primary-100); }
        .qty-btn.remove { color: var(--danger); }

        .qty-value {
            flex: 1;
            text-align: center;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--primary-dark);
        }

        /* ======================== */
        /*  CART BOTTOM BAR         */
        /* ======================== */
        .cart-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--cart-bar-height);
            background: white;
            border-top: 1px solid var(--border);
            box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
            z-index: 998;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 12px;
            transform: translateY(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .cart-bar.visible { transform: translateY(0); }

        .cart-bar-info {
            flex: 1;
        }

        .cart-bar-count {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .cart-bar-total {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .cart-bar-btn {
            padding: 12px 28px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-bar-btn:hover { box-shadow: var(--shadow-lg); transform: translateY(-1px); }

        /* ======================== */
        /*    CART SLIDE PANEL      */
        /* ======================== */
        .cart-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1500;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .cart-overlay.active { opacity: 1; visibility: visible; }

        .cart-panel {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            max-height: 90vh;
            background: white;
            border-radius: 24px 24px 0 0;
            z-index: 1501;
            transform: translateY(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .cart-panel.active { transform: translateY(0); }

        .cart-panel-header {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cart-panel-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .cart-panel-close {
            background: var(--primary-50);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-muted);
            transition: all 0.3s;
        }

        .cart-panel-close:hover { background: var(--danger); color: white; }

        .cart-panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px 20px;
            overscroll-behavior: contain;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .cart-item:last-child { border-bottom: none; }

        .cart-item-info {
            flex: 1;
            min-width: 0;
        }

        .cart-item-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cart-item-price {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .cart-item-subtotal {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary-dark);
            white-space: nowrap;
        }

        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 0;
            background: var(--primary-50);
            border-radius: 8px;
            overflow: hidden;
        }

        .cart-item-qty button {
            width: 32px;
            height: 32px;
            border: none;
            background: none;
            color: var(--primary);
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-item-qty button:hover { background: var(--primary-100); }

        .cart-item-qty span {
            width: 28px;
            text-align: center;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .cart-item-remove {
            background: none;
            border: none;
            color: var(--danger);
            font-size: 0.85rem;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .cart-item-remove:hover { background: rgba(239,68,68,0.1); }

        /* Location + Notes */
        .cart-form-section {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            background: var(--bg);
        }

        .cart-form-section .form-group { margin-bottom: 14px; }
        .cart-form-section .form-group:last-child { margin-bottom: 0; }

        .location-datalist-wrap {
            position: relative;
        }

        .location-datalist-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .location-datalist-wrap input {
            padding-left: 38px;
        }

        /* Checkout area */
        .cart-checkout-section {
            padding: 16px 20px 24px;
            border-top: 1px solid var(--border);
            background: white;
        }

        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .cart-summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 16px;
            padding-top: 8px;
            border-top: 2px solid var(--border);
        }

        .checkout-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .checkout-btn:hover { box-shadow: 0 8px 20px rgba(34,197,94,0.3); transform: translateY(-1px); }
        .checkout-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

        .checkout-btn.closed-state {
            background: linear-gradient(135deg, #9CA3AF 0%, #6B7280 100%);
        }

        /* ======================== */
        /*    EMPTY STATE           */
        /* ======================== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 3.5rem;
            color: var(--primary-100);
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* ======================== */
        /*   LOADING SKELETON       */
        /* ======================== */
        .skeleton {
            background: linear-gradient(90deg, var(--primary-50) 25%, #f0ebe7 50%, var(--primary-50) 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.5s infinite;
            border-radius: var(--radius-sm);
        }

        @keyframes skeleton-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .skeleton-card { height: 240px; border-radius: var(--radius); }

        /* ======================== */
        /*      SUCCESS MODAL       */
        /* ======================== */
        .success-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            z-index: 3000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s;
        }

        .success-card {
            background: white;
            border-radius: 24px;
            padding: 40px 28px;
            width: 100%;
            max-width: 380px;
            box-shadow: var(--shadow-xl);
            text-align: center;
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s both;
        }

        .success-icon i { font-size: 2rem; color: white; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn { from { transform: scale(0); } to { transform: scale(1); } }

        /* Spinner */
        /* Profile Panel Styles */
        .profile-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1500;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        .profile-overlay.active { opacity: 1; visibility: visible; }

        .profile-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: 85%;
            max-width: 380px;
            height: 100vh;
            background: white;
            z-index: 1501;
            transform: translateX(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 20px rgba(0,0,0,0.1);
        }
        .profile-panel.active { transform: translateX(0); }

        .profile-header {
            padding: 24px 20px;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .profile-user-info { display: flex; align-items: center; gap: 12px; }
        .profile-avatar {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
        }
        .profile-name { font-weight: 700; font-size: 1.05rem; margin-bottom: 2px; }
        .profile-wa { font-size: 0.8rem; opacity: 0.8; }

        .profile-body { flex: 1; overflow-y: auto; padding: 20px; }
        .profile-section-title {
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .history-list { display: flex; flex-direction: column; gap: 12px; }
        .history-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px;
            transition: all 0.3s;
        }
        .history-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .history-no { font-weight: 700; font-size: 0.85rem; color: var(--primary-dark); }
        .history-status {
            padding: 2px 8px;
            border-radius: var(--radius-full);
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-pending { background: #E8F5FF; color: var(--info); }
        .status-processing { background: #FFF4E5; color: var(--warning); }
        .status-completed { background: #E8F9EE; color: var(--success); }
        .status-cancelled { background: #FEE7E7; color: var(--danger); }

        .history-total { font-weight: 800; font-size: 0.95rem; display: block; margin-bottom: 4px; }
        .history-time { font-size: 0.7rem; color: var(--text-light); }

        .profile-footer { padding: 16px 20px; border-top: 1px solid var(--border); }
        .btn-outline-danger {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--danger);
            background: none;
            color: var(--danger);
            border-radius: var(--radius-sm);
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-outline-danger:hover { background: var(--danger); color: white; }

        .spinner-sm {
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            display: inline-block;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>

<body>
    <!-- Store Status Banner -->
    <div class="store-status-banner closed" id="statusBanner">
        <div class="pulse-dot"></div>
        <span id="statusText">Memeriksa status toko...</span>
    </div>

    <!-- Top Navigation -->
    <nav class="top-nav">
        <img src="{{ asset($storeLogo) }}" alt="{{ $storeName }}" class="nav-logo">
        <span class="nav-title">{{ $storeName }}</span>
        <button class="nav-cart-btn" onclick="openCart()" id="navCartBtn" style="display:none;">
            <i class="fas fa-shopping-bag"></i>
            <span class="cart-badge" id="navCartBadge" style="display:none;">0</span>
        </button>
        <button class="nav-user-btn" id="navUserBtn" onclick="showUserMenu()" title="Profil">
            <i class="fas fa-user"></i>
        </button>
    </nav>

    <!-- Main Content -->
    <main class="main" id="mainContent">
        <!-- Search -->
        <div class="search-wrapper">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari produk..." autocomplete="off">
            </div>
        </div>

        <!-- Category Chips -->
        <div class="category-scroll" id="categoryScroll">
            <button class="category-chip active" data-id="all" onclick="filterCategory('all', this)">Semua</button>
            @foreach($categories as $cat)
                <button class="category-chip" data-id="{{ $cat->id }}" onclick="filterCategory('{{ $cat->id }}', this)">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <!-- Product Grid -->
        <div class="product-grid" id="productGrid">
            <!-- Loading skeletons -->
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display:none;">
            <i class="fas fa-box-open"></i>
            <h3>Tidak ada produk</h3>
            <p>Produk yang kamu cari tidak ditemukan.</p>
        </div>
    </main>

    <!-- Cart Bottom Bar -->
    <div class="cart-bar" id="cartBar">
        <div class="cart-bar-info">
            <div class="cart-bar-count"><span id="cartBarCount">0</span> item</div>
            <div class="cart-bar-total">Rp<span id="cartBarTotal">0</span></div>
        </div>
        <button class="cart-bar-btn" onclick="openCart()">
            <i class="fas fa-shopping-bag"></i>
            Lihat Keranjang
        </button>
    </div>

    <!-- Cart Overlay + Panel -->
    <div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
    <div class="cart-panel" id="cartPanel">
        <div class="cart-panel-header">
            <span class="cart-panel-title"><i class="fas fa-shopping-bag"></i> Keranjang</span>
            <button class="cart-panel-close" onclick="closeCart()"><i class="fas fa-times"></i></button>
        </div>
        <div class="cart-panel-body" id="cartPanelBody">
            <div class="empty-state" style="padding: 40px 10px;">
                <i class="fas fa-shopping-bag"></i>
                <h3>Keranjang kosong</h3>
                <p>Tambahkan produk terlebih dahulu.</p>
            </div>
        </div>

        <!-- Location + Notes Form -->
        <div class="cart-form-section" id="cartFormSection" style="display:none;">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-map-marker-alt"></i> Lokasi Pengantaran</label>
                <div class="location-datalist-wrap">
                    <i class="fas fa-location-dot"></i>
                    <input type="text" class="form-input" id="locationInput" list="locationPresets"
                           placeholder="Pilih atau ketik lokasi..." autocomplete="off">
                    <datalist id="locationPresets">
                        <option value="Ruang Guru">
                        <option value="Ruang TU">
                        <option value="Ruang Kepala Sekolah">
                        <option value="Perpustakaan">
                        <option value="Lab Komputer">
                        <option value="Lab IPA">
                        <option value="Kelas 7A">
                        <option value="Kelas 7B">
                        <option value="Kelas 7C">
                        <option value="Kelas 7D">
                        <option value="Kelas 7E">
                        <option value="Kelas 7F">
                        <option value="Kelas 8A">
                        <option value="Kelas 8B">
                        <option value="Kelas 8C">
                        <option value="Kelas 8D">
                        <option value="Kelas 8E">
                        <option value="Kelas 8F">
                        <option value="Kelas 9A">
                        <option value="Kelas 9B">
                        <option value="Kelas 9C">
                        <option value="Kelas 9D">
                        <option value="Kelas 9E">
                        <option value="Kelas 9F">
                        <option value="Kantin">
                        <option value="Gedung Serbaguna">
                        <option value="Mushalla">
                        <option value="Pos Satpam">
                    </datalist>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-sticky-note"></i> Catatan (opsional)</label>
                <input type="text" class="form-input" id="notesInput" placeholder="Contoh: Tolong minta sedotan...">
            </div>
        </div>

        <!-- Checkout Section -->
        <div class="cart-checkout-section" id="cartCheckoutSection" style="display:none;">
            <div class="cart-summary-row">
                <span>Total Item</span>
                <span id="cartSummaryItems">0 item</span>
            </div>
            <div class="cart-summary-total">
                <span>Total</span>
                <span>Rp<span id="cartSummaryTotal">0</span></span>
            </div>
            <button class="checkout-btn" id="checkoutBtn" onclick="checkout()">
                <i class="fas fa-paper-plane"></i>
                Kirim Pesanan
            </button>
        </div>
    </div>
    <!-- Profile Overlay + Panel -->
    <div class="profile-overlay" id="profileOverlay" onclick="closeProfile()"></div>
    <div class="profile-panel" id="profilePanel">
        <div class="profile-header">
            <div class="profile-user-info">
                <div class="profile-avatar" id="profileAvatar">A</div>
                <div>
                    <div class="profile-name" id="profileName">Nama Pengguna</div>
                    <div class="profile-wa" id="profileWa">08123456789</div>
                </div>
            </div>
            <button onclick="closeProfile()" style="background:none;border:none;color:white;font-size:1.2rem;"><i class="fas fa-times"></i></button>
        </div>
        <div class="profile-body">
            <div class="profile-section-title">
                <i class="fas fa-history"></i> Riwayat Pesanan Saya
            </div>
            <div id="historyList" class="history-list">
                <!-- History items will be injected here -->
                <div style="text-align:center;padding:40px 0;color:var(--text-light);">
                    <i class="fas fa-history" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.3;"></i>
                    Belum ada riwayat pesanan
                </div>
            </div>
        </div>
        <div class="profile-footer">
            <button class="btn-outline-danger" onclick="logoutPwa()">
                <i class="fas fa-sign-out-alt"></i> Ganti Akun / Keluar
            </button>
        </div>
    </div>

    <!-- Login Overlay (shown if no user data in localStorage) -->
    <div class="login-overlay" id="loginOverlay" style="display:none;">
        <div class="login-card">
            <img src="{{ asset($storeLogo) }}" alt="Logo" class="login-logo">
            <h2 class="login-title">Selamat Datang! 👋</h2>
            <p class="login-subtitle">Isi data kamu untuk mulai pesan dari {{ $storeName }}</p>
            <form onsubmit="saveUserData(event)">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-input" id="loginName" placeholder="Contoh: Ahmad Fauzi" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input type="tel" class="form-input" id="loginWhatsapp" placeholder="Contoh: 08123456789" required
                           pattern="[0-9]{10,15}" title="Masukkan nomor HP 10-15 digit">
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-arrow-right"></i>
                    Mulai Belanja
                </button>
            </form>
        </div>
    </div>

    <script>
        // ==========================
        //  STATE MANAGEMENT
        // ==========================
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const API = {
            products:    '{{ route("pwa.api.products") }}',
            storeStatus: '{{ route("pwa.api.store-status") }}',
            createOrder: '{{ route("pwa.api.orders.create") }}',
            orderHistory: '{{ route("pwa.api.orders.history") }}',
        };

        let cart = [];
        let allProducts = [];
        let storeOpen = false;
        let currentCategory = 'all';
        let searchDebounce = null;

        // ==========================
        //  INITIALIZATION
        // ==========================
        document.addEventListener('DOMContentLoaded', () => {
            checkUserLogin();
            checkStoreStatus();
            loadProducts();

            // Periodic store status check (every 30s)
            setInterval(checkStoreStatus, 30000);

            // Search debounce
            document.getElementById('searchInput').addEventListener('input', (e) => {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => loadProducts(e.target.value), 300);
            });

            // Restore location from localStorage
            const savedLocation = localStorage.getItem('pwa_location');
            if (savedLocation) {
                document.getElementById('locationInput').value = savedLocation;
            }
        });

        // ==========================
        //  USER LOGIN (LocalStorage)
        // ==========================
        function checkUserLogin() {
            const name = localStorage.getItem('pwa_name');
            const wa = localStorage.getItem('pwa_whatsapp');

            if (!name || !wa) {
                document.getElementById('loginOverlay').style.display = 'flex';
                return;
            }

            // Update nav user button
            const initials = name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
            const btn = document.getElementById('navUserBtn');
            btn.innerHTML = initials;
            btn.title = name;
            document.getElementById('navCartBtn').style.display = 'flex';

            // Also update profile panel data
            document.getElementById('profileAvatar').textContent = initials[0];
            document.getElementById('profileName').textContent = name;
            document.getElementById('profileWa').textContent = wa;
        }

        function showUserMenu() {
            const name = localStorage.getItem('pwa_name');
            if (!name) {
                document.getElementById('loginOverlay').style.display = 'flex';
                return;
            }
            openProfile();
        }

        function openProfile() {
            document.getElementById('profileOverlay').classList.add('active');
            document.getElementById('profilePanel').classList.add('active');
            document.body.style.overflow = 'hidden';
            loadOrderHistory();
        }

        function closeProfile() {
            document.getElementById('profileOverlay').classList.remove('active');
            document.getElementById('profilePanel').classList.remove('active');
            document.body.style.overflow = '';
        }

        async function loadOrderHistory() {
            const wa = localStorage.getItem('pwa_whatsapp');
            const list = document.getElementById('historyList');

            try {
                const res = await fetch(`${API.orderHistory}?whatsapp=${wa}`);
                const data = await res.json();

                if (data.success && data.data.length > 0) {
                    list.innerHTML = data.data.map(order => `
                        <div class="history-card">
                            <div class="history-card-header">
                                <span class="history-no">${order.order_no}</span>
                                <span class="history-status status-${order.status}">${order.status}</span>
                            </div>
                            <span class="history-total">Rp${formatNumber(order.total_amount)}</span>
                            <div class="history-time">
                                <i class="far fa-clock"></i> ${new Date(order.created_at).toLocaleString('id-ID', {
                                    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
                                })}
                            </div>
                        </div>
                    `).join('');
                } else {
                    list.innerHTML = `<div style="text-align:center;padding:40px 0;color:var(--text-light);">
                        <i class="fas fa-history" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.3;"></i>
                        Belum ada riwayat pesanan
                    </div>`;
                }
            } catch (err) {
                console.error('History error:', err);
                list.innerHTML = `<div style="text-align:center;padding:40px 0;color:var(--danger);">
                    Gagal memuat riwayat
                </div>`;
            }
        }

        function logoutPwa() {
            if (confirm('Keluar dari akun ini? Kamu harus mengisi data lagi saat nanti memesan.')) {
                localStorage.removeItem('pwa_name');
                localStorage.removeItem('pwa_whatsapp');
                // Keep location for convenience if they just want to switch account
                window.location.reload();
            }
        }

        function saveUserData(e) {
            e.preventDefault();
            const name = document.getElementById('loginName').value.trim();
            const wa = document.getElementById('loginWhatsapp').value.trim();

            if (!name || !wa) return;

            localStorage.setItem('pwa_name', name);
            localStorage.setItem('pwa_whatsapp', wa);

            document.getElementById('loginOverlay').style.display = 'none';
            checkUserLogin();
        }

        function showUserMenu() {
            const name = localStorage.getItem('pwa_name') || '';
            const wa = localStorage.getItem('pwa_whatsapp') || '';

            // Simple prompt to edit or logout
            const action = prompt(
                `👤 ${name}\n📱 ${wa}\n\nKetik "ubah" untuk ganti data, atau "keluar" untuk logout:`,
                ''
            );

            if (action && action.toLowerCase() === 'ubah') {
                document.getElementById('loginName').value = name;
                document.getElementById('loginWhatsapp').value = wa;
                document.getElementById('loginOverlay').style.display = 'flex';
            } else if (action && action.toLowerCase() === 'keluar') {
                localStorage.removeItem('pwa_name');
                localStorage.removeItem('pwa_whatsapp');
                location.reload();
            }
        }

        // ==========================
        //  STORE STATUS
        // ==========================
        async function checkStoreStatus() {
            try {
                const res = await fetch(API.storeStatus);
                const data = await res.json();
                storeOpen = data.open;

                const banner = document.getElementById('statusBanner');
                const text = document.getElementById('statusText');

                if (data.open) {
                    banner.className = 'store-status-banner open';
                    text.textContent = '🟢 Toko Buka — Silakan pesan!';
                } else {
                    banner.className = 'store-status-banner closed';
                    text.textContent = '🔴 Toko Tutup — Kasir belum login';
                }

                // Update checkout button state
                updateCheckoutState();
            } catch (err) {
                console.error('Status check failed:', err);
            }
        }

        // ==========================
        //  PRODUCTS
        // ==========================
        async function loadProducts(search = '') {
            try {
                const params = new URLSearchParams();
                if (search) params.set('q', search);
                if (currentCategory !== 'all') params.set('category_id', currentCategory);

                const res = await fetch(API.products + '?' + params.toString());
                const data = await res.json();
                allProducts = data.data || [];
                renderProducts();
            } catch (err) {
                console.error('Load products failed:', err);
                document.getElementById('productGrid').innerHTML = '';
                document.getElementById('emptyState').style.display = 'block';
            }
        }

        function renderProducts() {
            const grid = document.getElementById('productGrid');
            const empty = document.getElementById('emptyState');

            if (allProducts.length === 0) {
                grid.innerHTML = '';
                empty.style.display = 'block';
                return;
            }

            empty.style.display = 'none';

            grid.innerHTML = allProducts.map(p => {
                const inCart = cart.find(c => c.id === p.id);
                const qty = inCart ? inCart.qty : 0;
                const isOutOfStock = p.stock <= 0;

                return `
                    <div class="product-card ${isOutOfStock ? 'out-of-stock' : ''}" id="pcard-${p.id}">
                        <div class="product-img-wrap">
                            ${p.image
                                ? `<img src="${p.image}" alt="${p.name}" loading="lazy">`
                                : `<i class="fas fa-box product-img-placeholder"></i>`}
                            ${isOutOfStock
                                ? '<span class="stock-badge out">Habis</span>'
                                : (p.stock <= 5 ? `<span class="stock-badge low">Sisa ${p.stock}</span>` : '')}
                        </div>
                        <div class="product-info">
                            <div class="product-category-label">${p.category}</div>
                            <div class="product-name">${p.name}</div>
                            <div class="product-price">Rp${formatNumber(p.price)} <small>/${p.unit}</small></div>
                            ${qty > 0 ? `
                                <div class="qty-controls">
                                    <button class="qty-btn ${qty === 1 ? 'remove' : ''}" onclick="updateQty(${p.id}, -1)">
                                        ${qty === 1 ? '<i class="fas fa-trash-alt"></i>' : '−'}
                                    </button>
                                    <span class="qty-value">${qty}</span>
                                    <button class="qty-btn" onclick="updateQty(${p.id}, 1)" ${qty >= p.stock ? 'disabled style="opacity:0.3"' : ''}>+</button>
                                </div>
                            ` : `
                                <button class="add-to-cart-btn" onclick="addToCart(${p.id})" ${isOutOfStock ? 'disabled' : ''}>
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            `}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function filterCategory(catId, el) {
            currentCategory = catId;
            document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            loadProducts(document.getElementById('searchInput').value);
        }

        // ==========================
        //  CART MANAGEMENT
        // ==========================
        function addToCart(productId) {
            const product = allProducts.find(p => p.id === productId);
            if (!product || product.stock <= 0) return;

            const existing = cart.find(c => c.id === productId);
            if (existing) {
                if (existing.qty < product.stock) existing.qty++;
            } else {
                cart.push({ id: product.id, name: product.name, price: product.price, qty: 1, stock: product.stock });
            }

            renderProducts();
            updateCartUI();
        }

        function updateQty(productId, delta) {
            const item = cart.find(c => c.id === productId);
            if (!item) return;

            item.qty += delta;

            if (item.qty <= 0) {
                cart = cart.filter(c => c.id !== productId);
            } else {
                const product = allProducts.find(p => p.id === productId);
                if (product && item.qty > product.stock) item.qty = product.stock;
            }

            renderProducts();
            updateCartUI();
        }

        function removeFromCart(productId) {
            cart = cart.filter(c => c.id !== productId);
            renderProducts();
            updateCartUI();
        }

        function updateCartUI() {
            const totalItems = cart.reduce((s, c) => s + c.qty, 0);
            const totalPrice = cart.reduce((s, c) => s + (c.price * c.qty), 0);

            // Cart bar
            const bar = document.getElementById('cartBar');
            if (totalItems > 0) {
                bar.classList.add('visible');
                document.getElementById('cartBarCount').textContent = totalItems;
                document.getElementById('cartBarTotal').textContent = formatNumber(totalPrice);
            } else {
                bar.classList.remove('visible');
            }

            // Nav badge
            const badge = document.getElementById('navCartBadge');
            if (totalItems > 0) {
                badge.style.display = 'flex';
                badge.textContent = totalItems;
                badge.classList.remove('bounce');
                void badge.offsetWidth; // force reflow
                badge.classList.add('bounce');
            } else {
                badge.style.display = 'none';
            }

            // Cart panel contents
            renderCartPanel(totalItems, totalPrice);
        }

        function renderCartPanel(totalItems, totalPrice) {
            const body = document.getElementById('cartPanelBody');
            const formSection = document.getElementById('cartFormSection');
            const checkoutSection = document.getElementById('cartCheckoutSection');

            if (cart.length === 0) {
                body.innerHTML = `<div class="empty-state" style="padding:40px 10px;">
                    <i class="fas fa-shopping-bag"></i><h3>Keranjang kosong</h3><p>Tambahkan produk terlebih dahulu.</p>
                </div>`;
                formSection.style.display = 'none';
                checkoutSection.style.display = 'none';
                return;
            }

            formSection.style.display = 'block';
            checkoutSection.style.display = 'block';

            body.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">Rp${formatNumber(item.price)} × ${item.qty}</div>
                    </div>
                    <div class="cart-item-qty">
                        <button onclick="updateQty(${item.id}, -1)">${item.qty === 1 ? '🗑' : '−'}</button>
                        <span>${item.qty}</span>
                        <button onclick="updateQty(${item.id}, 1)" ${item.qty >= item.stock ? 'disabled style="opacity:0.3"' : ''}>+</button>
                    </div>
                    <div class="cart-item-subtotal">Rp${formatNumber(item.price * item.qty)}</div>
                </div>
            `).join('');

            document.getElementById('cartSummaryItems').textContent = totalItems + ' item';
            document.getElementById('cartSummaryTotal').textContent = formatNumber(totalPrice);

            updateCheckoutState();
        }

        function updateCheckoutState() {
            const btn = document.getElementById('checkoutBtn');
            if (!btn) return;

            if (!storeOpen) {
                btn.disabled = true;
                btn.className = 'checkout-btn closed-state';
                btn.innerHTML = '<i class="fas fa-store-slash"></i> Toko Tutup — Tidak bisa pesan';
            } else {
                btn.disabled = false;
                btn.className = 'checkout-btn';
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Pesanan';
            }
        }

        function openCart() {
            document.getElementById('cartOverlay').classList.add('active');
            document.getElementById('cartPanel').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeCart() {
            document.getElementById('cartOverlay').classList.remove('active');
            document.getElementById('cartPanel').classList.remove('active');
            document.body.style.overflow = '';
        }

        // ==========================
        //  CHECKOUT
        // ==========================
        async function checkout() {
            if (!storeOpen) return alert('Toko sedang tutup!');
            if (cart.length === 0) return;

            const name = localStorage.getItem('pwa_name');
            const wa = localStorage.getItem('pwa_whatsapp');
            const location = document.getElementById('locationInput').value.trim();
            const notes = document.getElementById('notesInput').value.trim();

            if (!name || !wa) {
                document.getElementById('loginOverlay').style.display = 'flex';
                return;
            }

            if (!location) {
                document.getElementById('locationInput').focus();
                document.getElementById('locationInput').style.borderColor = 'var(--danger)';
                setTimeout(() => { document.getElementById('locationInput').style.borderColor = ''; }, 2000);
                return alert('Mohon isi lokasi pengantaran!');
            }

            // Save location for next time
            localStorage.setItem('pwa_location', location);

            const btn = document.getElementById('checkoutBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-sm"></span> Mengirim pesanan...';

            try {
                const res = await fetch(API.createOrder, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        customer_name: name,
                        customer_whatsapp: wa,
                        delivery_location: location,
                        notes: notes || null,
                        items: cart.map(c => ({ product_id: c.id, quantity: c.qty })),
                    }),
                });

                const data = await res.json();

                if (data.success) {
                    // Clear cart
                    cart = [];
                    updateCartUI();
                    closeCart();
                    document.getElementById('notesInput').value = '';

                    // Show success
                    showSuccessModal(data.order_no, data.total);

                    // Reload products to get fresh stock
                    loadProducts();
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                console.error('Checkout error:', err);
                alert('Gagal mengirim pesanan. Periksa koneksi internet Anda.');
            } finally {
                updateCheckoutState();
            }
        }

        function showSuccessModal(orderNo, total) {
            const overlay = document.createElement('div');
            overlay.className = 'success-overlay';
            overlay.innerHTML = `
                <div class="success-card">
                    <div class="success-icon"><i class="fas fa-check"></i></div>
                    <h2 style="font-size:1.2rem;font-weight:800;color:var(--text);margin-bottom:8px;">Pesanan Terkirim! 🎉</h2>
                    <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:20px;">
                        Pesanan kamu sedang diproses oleh kasir.<br>Tunggu sebentar ya!
                    </p>
                    <div style="background:var(--primary-50);border-radius:12px;padding:16px;margin-bottom:20px;">
                        <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">No. Pesanan</div>
                        <div style="font-size:1.1rem;font-weight:800;color:var(--primary-dark);margin-bottom:8px;">${orderNo}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">Total</div>
                        <div style="font-size:1.1rem;font-weight:800;color:var(--primary-dark);">Rp${formatNumber(total)}</div>
                    </div>
                    <button class="btn-primary" onclick="this.closest('.success-overlay').remove()">
                        <i class="fas fa-check"></i> OK, Mengerti
                    </button>
                </div>
            `;
            document.body.appendChild(overlay);
        }

        // ==========================
        //  UTILITIES
        // ==========================
        function formatNumber(n) {
            return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
</body>
</html>
