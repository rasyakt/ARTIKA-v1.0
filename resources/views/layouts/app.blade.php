@php /** @var \App\Models\User $user */ $user = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="en">

<head>
    {{-- Apply theme ASAP to prevent flash of wrong theme --}}
    <script>
        (function () {
            const saved = localStorage.getItem('artika-theme') || 'system';
            if (saved === 'dark' || (saved === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6F5849">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ARTIKA POS">
    <title>@yield('title', 'Dashboard') - {{ App\Models\Setting::get('system_name', 'ARTIKA POS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(App\Models\Setting::get('site_logo', 'img/logo2.png')) }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/img/icons/icon-192x192.png">
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    {!! \App\Helpers\ThemeHelper::getCssVariables(\App\Models\Setting::get('site_color_theme', 'brown')) !!}
    <style>
        /* SweetAlert2 Custom Theme ARTIKA */
        .artika-swal-popup {
            border-radius: 16px !important;
            padding: 1.5rem !important;
            border: 1px solid var(--brown-100) !important;
            font-family: 'Inter', system-ui, sans-serif !important;
        }

        .artika-swal-title {
            color: var(--brown-900) !important;
            font-weight: 700 !important;
            font-size: 1.25rem !important;
        }

        .artika-swal-confirm-btn {
            background: var(--color-primary-dark) !important;
            border-radius: 10px !important;
            padding: 0.6rem 1.5rem !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 6px -1px rgba(111, 88, 73, 0.2) !important;
            margin: 0.25rem !important;
        }

        .artika-swal-cancel-btn {
            background: var(--brown-50) !important;
            color: var(--color-primary-dark) !important;
            border: 1px solid var(--brown-100) !important;
            border-radius: 10px !important;
            padding: 0.6rem 1.5rem !important;
            font-weight: 600 !important;
            margin: 0.25rem !important;
        }

        .artika-swal-toast {
            border-radius: 12px !important;
            background: var(--color-white) !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }

        html {
            background: var(--gray-50);
        }

        body {
            zoom: 100%;
            background: var(--color-bg);
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            color: var(--color-text);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Theme Toggle Dropdown */
        .theme-toggle-btn {
            background: var(--color-primary);
            border: 2px solid var(--color-primary-light);
            border-radius: 10px;
            padding: 0.45rem 0.85rem;
            color: white;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .theme-toggle-btn:hover {
            background: var(--color-primary-dark);
        }

        .theme-menu {
            min-width: 160px;
            padding: 0.5rem;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            background: var(--card-bg, #fff);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .theme-option {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 0.9rem;
            border-radius: 8px;
            font-size: 0.85rem;
            color: var(--color-text, #333);
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .theme-option:hover {
            background: var(--gray-100, #f5f5f5);
        }

        .theme-option.active {
            background: var(--color-primary, #85695a) !important;
            color: white !important;
            border-color: var(--color-primary-dark) !important;
        }

        .theme-selector-group .btn {
            border-radius: 8px;
            padding: 0.4rem;
        }

        /* Fix for Bootstrap Modals & SweetAlert2 with CSS Zoom */
        /* body { zoom: 90% } causes fixed elements to be in the zoomed space.
           zoom: reset on direct body children (modal, backdrop) restores
           them to true viewport coordinates so they cover 100% of screen. */
        .modal-backdrop {
            zoom: reset;
        }
        .modal {
            zoom: reset;
        }
        .swal2-container {
            zoom: reset;
        }


        .main-navbar {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            height: 64px;
        }

        .sidebar {
            background: var(--card-bg);
            position: fixed;
            top: 64px;
            left: 0;
            bottom: 0;
            width: 260px;
            border-right: 1px solid var(--gray-200);
            padding: 1rem 0;
            overflow-y: auto;
            z-index: 1020;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            scrollbar-width: thin;
            scrollbar-color: var(--color-primary) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: var(--color-primary);
            border-radius: 10px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.25rem;
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.925rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin: 0.25rem 0.75rem;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }

        .sidebar-link i:first-child {
            margin-right: 1rem;
            font-size: 1.1rem;
            width: 1.5rem;
            text-align: center;
            color: var(--color-primary);
            transition: transform 0.3s ease;
        }

        .sidebar-link:hover {
            background: var(--brown-50);
            color: var(--color-primary-dark);
            transform: translateX(4px);
        }

        .sidebar-link:hover i:first-child {
            transform: scale(1.15);
        }

        .sidebar-link.active {
            background: linear-gradient(90deg, var(--color-secondary-light) 0%, var(--brown-50) 100%);
            color: var(--color-primary-dark);
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(133, 105, 90, 0.08);
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 4px;
            background: var(--color-primary);
            border-radius: 0 4px 4px 0;
        }

        /* Sidebar Dropdown Styles */
        .sidebar-dropdown {
            display: flex;
            flex-direction: column;
            margin-bottom: 0.25rem;
        }

        .sidebar-dropdown-toggle {
            cursor: pointer;
            user-select: none;
        }

        .dropdown-arrow {
            margin-left: auto;
            font-size: 0.7rem;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.5;
        }

        .sidebar-dropdown.active>.sidebar-link .dropdown-arrow {
            transform: rotate(90deg);
            opacity: 1;
        }

        .sidebar-submenu {
            display: none;
            list-style: none;
            padding: 0.25rem 0 0.5rem 2.85rem;
            margin: 0;
            position: relative;
        }

        /* Connecting line for submenu */
        .sidebar-submenu::before {
            content: '';
            position: absolute;
            left: 1.95rem;
            top: 0;
            bottom: 1rem;
            width: 1.5px;
            background: var(--brown-100);
            border-radius: 1px;
            opacity: 0.6;
        }

        .sidebar-dropdown.active .sidebar-submenu {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .submenu-link {
            display: flex;
            align-items: center;
            padding: 0.6rem 1rem;
            color: var(--gray-600);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 0.15rem 0.75rem 0.15rem 0;
            position: relative;
        }

        .submenu-link::before {
            content: '';
            position: absolute;
            left: -0.9rem;
            top: 50%;
            width: 0.6rem;
            height: 1.5px;
            background: var(--brown-100);
            opacity: 0.6;
        }

        .submenu-link:hover {
            background: var(--brown-50);
            color: var(--color-primary);
            padding-left: 1.25rem;
        }

        .submenu-link.active {
            color: var(--color-primary-dark);
            font-weight: 700;
            background: var(--color-secondary-light);
        }

        .submenu-link i {
            margin-right: 0.75rem;
            font-size: 0.85rem;
            width: 1rem;
            text-align: center;
            color: var(--color-primary);
            opacity: 0.8;
        }

        .sidebar-section-title {
            padding: 1.5rem 1.75rem 0.6rem;
            font-size: 0.65rem;
            font-weight: 800;
            text-uppercase;
            color: var(--gray-400);
            letter-spacing: 0.12em;
        }

        .main-content {
            padding: 0;
            background: var(--gray-50);
            margin-left: 260px;
            margin-top: 64px;
            min-height: calc(100vh - 64px);
            width: calc(100% - 260px);
            transition: all 0.3s ease;
        }

        .main-content.no-sidebar {
            margin-left: 0;
            width: 100%;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }

        .user-profile-link {
            background: none;
            border-radius: 12px;
            padding: 0.5rem 1rem;
            border: none;
            transition: all 0.25s;
            color: white !important;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .user-profile-link:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .user-name {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .profile-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--color-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            /* box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); */
            font-size: 1.1rem;
            line-height: normal;
        }

        /* Pagination Styling */
        .pagination {
            margin: 1rem 0;
        }

        .pagination .page-link {
            font-size: 0.875rem !important;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            margin: 0 0.25rem;
            border: 1px solid var(--color-secondary-light);
            color: var(--color-primary-dark);
        }

        .pagination .page-link svg {
            width: 0.875rem !important;
            height: 0.875rem !important;
            max-width: 0.875rem !important;
            max-height: 0.875rem !important;
        }

        .pagination .page-item.active .page-link {
            background: var(--color-primary-dark);
            border-color: var(--color-primary);
        }

        .pagination .page-link:hover {
            background: var(--brown-50);
            border-color: var(--color-primary);
            color: var(--color-primary);
        }

        /* Hamburger Menu Button */
        .hamburger-btn {
            display: none;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
            color: white;
            font-size: 1.25rem;
            line-height: 1;
        }

        .hamburger-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Offline Banner */
        .offline-banner {
            position: fixed;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--color-danger, #dc3545);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            z-index: 9999;
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.4);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: top 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .offline-banner.show {
            top: 20px;
        }

        .offline-banner i {
            font-size: 1.1rem;
            animation: pulse-danger 2s infinite;
        }

        @keyframes pulse-danger {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Mobile Responsive */
        @media (max-width: 1023px) {
            .hamburger-btn {
                display: block;
            }

            /* Show only avatar on mobile */
            .user-profile-link .user-name {
                display: none;
            }

            .user-profile-link {
                padding: 0.15rem;
            }

            .profile-avatar {
                width: 40px;
                height: 40px;
                font-size: 0.95rem;
            }

            .sidebar {
                position: fixed !important;
                left: -290px !important;
                top: 0 !important;
                bottom: 0 !important;
                width: 290px !important;
                height: auto !important; /* Spans top to bottom */
                z-index: 9999 !important;
                transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 15px 0 30px rgba(0, 0, 0, 0.1);
                overflow-y: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                background: var(--card-bg) !important;
                display: flex !important;
                flex-direction: column !important;
                overscroll-behavior: contain;
            }

            .sidebar.active {
                left: 0 !important;
            }

            .sidebar-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1.5rem 1.75rem;
                border-bottom: 1.5px solid var(--brown-50);
                margin-bottom: 0.75rem;
                background: var(--brown-50);
            }

            .sidebar-title {
                font-weight: 800;
                color: var(--color-primary-dark);
                font-size: 1.25rem;
                letter-spacing: -0.02em;
            }

            .sidebar-close {
                background: var(--white);
                border: none;
                width: 36px;
                height: 36px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--color-primary-dark);
                cursor: pointer;
                transition: all 0.3s;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            }

            .sidebar-close i {
                font-size: 1rem;
            }

            .sidebar-close:hover {
                background: var(--color-danger);
                color: #fff;
                transform: rotate(90deg);
            }

            .col-md-2.sidebar {
                display: block !important;
            }

            .col-md-10.main-content {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        @media (min-width: 1024px) {
            .sidebar-header {
                display: none;
            }
        }

        .main-navbar .container-fluid {
            height: 100%;
            display: flex;
            align-items: center;
        }


        /* ===================================================
           GLOBAL MOBILE RESPONSIVE - ARTIKA ADMIN
           Covers: admin, warehouse, manager, superadmin
        =================================================== */

        /* ─── Small Tablets (≤ 768px) ─── */
        @media (max-width: 768px) {

            /* 1. LAYOUT & SPACING */
            .container-fluid.py-4 {
                padding: 1rem 0.875rem !important;
            }

            /* 2. NAVBAR */
            .main-navbar .container-fluid {
                padding: 0 0.75rem;
            }

            .main-navbar .brand-text {
                font-size: 0.85rem;
            }

            /* 3. MAIN CONTENT (already handled by sidebar offset JS) */
            .main-content {
                padding-top: 64px;
            }

            /* 4. PAGE HEADERS — stack title / actions vertically */
            .d-flex.justify-content-between.align-items-center,
            .d-flex.justify-content-between.align-items-md-center {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .d-flex.justify-content-between.align-items-center>div:last-child,
            .d-flex.justify-content-between.align-items-md-center>div:last-child {
                width: 100%;
            }

            /* 5. TYPOGRAPHY */
            h2.fw-bold {
                font-size: 1.2rem !important;
            }

            h5.fw-bold {
                font-size: 1rem !important;
            }

            h4.fw-bold {
                font-size: 1.1rem !important;
            }

            /* 6. ACTION BUTTON GROUPS — wrap and fill */
            .d-flex.align-items-center.gap-2>.btn,
            .d-flex.align-items-center.gap-2>a.btn {
                flex: 1 1 auto;
                font-size: 0.83rem;
                padding: 0.45rem 0.85rem;
                white-space: nowrap;
            }

            /* 7. SEARCH + FILTER FORMS — stack vertically */
            .search-filter,
            form.d-flex.align-items-center.gap-3 {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.6rem !important;
                width: 100% !important;
            }

            .search-filter>*,
            form.d-flex.align-items-center.gap-3>* {
                width: 100% !important;
                min-width: 0 !important;
                flex: unset !important;
            }

            .search-container-capsule {
                width: 100% !important;
            }

            .category-select,
            .search-input {
                min-width: 0 !important;
                width: 100% !important;
            }

            /* 8. CARD PADDING */
            .card-body {
                padding: 1rem !important;
            }

            .card-body.p-4 {
                padding: 1rem !important;
            }

            .card-header,
            .card-header.p-4,
            .card-header.py-4.px-4 {
                padding: 0.875rem 1rem !important;
            }

            .card-footer {
                padding: 0.75rem 1rem !important;
            }

            /* 9. STATS CARDS — 2 columns */
            .row.g-4>.col-xl-3,
            .row.g-4>.col-md-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .row.g-4>.col-xl-3 .card-body {
                padding: 0.875rem !important;
            }

            .row.g-4>.col-xl-3 h4 {
                font-size: 1rem;
            }

            .row.g-4>.col-xl-3 p {
                font-size: 0.68rem !important;
            }

            .icon-box-premium {
                width: 38px !important;
                height: 38px !important;
                font-size: 0.9rem !important;
            }

            /* 10. TABLES */
            .table th,
            .table td {
                font-size: 0.8rem;
                padding: 0.5rem 0.65rem;
                white-space: nowrap;
                vertical-align: middle;
            }

            .table-responsive {
                -webkit-overflow-scrolling: touch;
                border: 0;
            }

            /* Text truncation utility for long names */
            .text-truncate-mobile {
                max-width: 140px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            /* 11. FORMS */
            .form-control,
            .form-select {
                font-size: 0.9rem;
            }

            .form-control-lg,
            .form-select-lg {
                font-size: 0.95rem !important;
            }

            .input-group>.btn {
                white-space: nowrap;
            }

            /* 12. CHARTS */
            .chart-container {
                height: 220px !important;
            }

            /* 13. MODALS */
            .modal-dialog {
                margin: 0.75rem;
                max-width: calc(100% - 1.5rem);
            }

            .modal-dialog.modal-dialog-centered {
                min-height: calc(100% - 1.5rem);
            }

            .modal-body.p-4,
            .modal-body {
                padding: 1rem !important;
            }

            .modal-header,
            .modal-footer {
                padding: 0.875rem 1rem !important;
            }

            /* 14. PAGINATION */
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }

            .pagination .page-link {
                padding: 0.35rem 0.6rem;
                font-size: 0.8rem !important;
            }

            /* 15. NAV PILLS (Settings tabs) — horizontal scrollable, no scrollbar */
            .nav-pills {
                scrollbar-width: none;
                -ms-overflow-style: none;
                padding-bottom: 0.25rem;
            }

            .nav-pills::-webkit-scrollbar {
                display: none;
            }

            /* 16. BADGES */
            .badge {
                font-size: 0.7rem;
                padding: 0.3em 0.5em;
            }

            /* 17. ALERTS & INFO BOXES */
            .alert {
                font-size: 0.875rem;
            }

            .mb-4.p-3 {
                padding: 0.75rem !important;
            }

            /* 18. FORM ROWS — col-md-6 should be full width */
            .row>.col-md-6:not(.col-6):not(.col-sm-6) {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        /* ─── Phones (≤ 575px) ─── */
        @media (max-width: 575px) {
            .container-fluid.py-4 {
                padding: 0.875rem 0.625rem !important;
            }

            /* Header title + subtitle stacked, action button full width */
            .d-flex.justify-content-between.align-items-center>*,
            .d-flex.justify-content-between.align-items-md-center>* {
                flex: 1 1 100%;
            }

            .d-flex.align-items-center.gap-2>.btn,
            .d-flex.align-items-center.gap-2>a.btn {
                flex: 1 1 100%;
                justify-content: center;
            }

            /* Charts shorter on phones */
            .chart-container {
                height: 180px !important;
            }

            /* Stats cards — still 2 columns on phones */
            .row.g-4>.col-xl-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .row.g-4>.col-xl-3 h4 {
                font-size: 0.9rem;
            }

            .row.g-4>.col-xl-3 p {
                font-size: 0.62rem !important;
            }

            /* Table cells even more compact but still scrollable */
            .table th,
            .table td {
                font-size: 0.75rem;
                padding: 0.4rem 0.5rem;
            }

            /* Report Hub cards — 2 per row */
            .row.g-4>.col-xl-3.col-md-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .report-card .card-body {
                padding: 0.875rem !important;
            }

            .report-icon {
                width: 52px !important;
                height: 52px !important;
                font-size: 1.5rem !important;
                margin-bottom: 12px !important;
            }

            /* Settings nav pills — horizontal, all visible */
            #settings-tabs .nav-link {
                font-size: 0.75rem !important;
                padding: 7px 11px !important;
            }
        }

        /* ─── Very small phones (≤ 390px) ─── */
        @media (max-width: 390px) {
            h2.fw-bold {
                font-size: 1.05rem !important;
            }

            /* Stats cards — full width 1 column on tiny phones */
            .row.g-4>.col-xl-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            /* Report cards — 1 column */
            .row.g-4>.col-xl-3.col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .text-truncate-mobile {
                max-width: 110px;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark main-navbar">
        <div class="container-fluid px-4">
            <!-- Hamburger Menu (Mobile) -->
            @if(in_array($user?->role?->name, ['superadmin', 'admin', 'manager', 'warehouse']))
                <button class="hamburger-btn me-3" id="hamburgerBtn" type="button">
                    ☰
                </button>
            @endif

            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <img src="{{ asset(App\Models\Setting::get('site_logo', 'img/logo2.png')) }}"
                    alt="{{ App\Models\Setting::get('system_name', 'ARTIKA Logo') }}"
                    style="height: 35px; width: auto;">
                <span
                    class="ms-2 fw-bold d-none d-sm-inline">{{ App\Models\Setting::get('system_name', 'ARTIKA') }}</span>
            </a>

            <div class="ms-auto d-flex align-items-center">
                <div class="dropdown">
                    <button class="nav-link user-profile-link dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <div class="profile-avatar me-3">{{ strtoupper(substr($user?->name ?? '', 0, 1)) }}</div>
                        <div class="d-flex flex-column text-start">
                            <span class="user-name line-height-1 mb-1">{{ $user?->name }}</span>
                            <span class="fw-700 text-uppercase opacity-75"
                                style="font-size: 0.75rem; letter-spacing: 0.05em;">{{ $user?->role?->name }}</span>
                        </div>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0 overflow-hidden"
                        style="min-width: 280px; border-radius: 16px;">
                        {{-- Header Profil --}}
                        <li>
                            <a href="{{ route('profile') }}"
                                class="dropdown-item p-3 bg-light border-bottom border-secondary-subtle d-flex align-items-center"
                                style="white-space: normal;">
                                <div class="profile-avatar bg-primary text-white me-3 d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 45px; height: 45px; border-radius: 50%; min-width: 45px;">
                                    {{ strtoupper(substr($user?->name ?? '', 0, 1)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="mb-0 fw-800 text-truncate text-dark">{{ $user?->name }}</h6>
                                    <div class="small text-muted text-truncate">{{ $user?->role?->name }}</div>
                                </div>
                                <i class="fa-solid fa-chevron-right ms-auto opacity-50" style="font-size: 0.8rem;"></i>
                            </a>
                        </li>

                        {{-- Section: Settings --}}
                        <div class="p-2">
                            <div class="dropdown-header text-uppercase fw-bold"
                                style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                {{ __('common.settings') ?? 'Pengaturan' }}
                            </div>

                            {{-- Theme Selection --}}
                            <div class="px-3 py-2">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small fw-600 text-muted"><i
                                            class="fa-solid fa-circle-half-stroke me-2"></i>Tema</span>
                                </div>
                                <div class="btn-group w-100 theme-selector-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary theme-option"
                                        data-theme="light" title="Light">
                                        <i class="fa-solid fa-sun"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary theme-option"
                                        data-theme="dark" title="Dark">
                                        <i class="fa-solid fa-moon"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary theme-option"
                                        data-theme="system" title="System">
                                        <i class="fa-solid fa-desktop"></i>
                                    </button>
                                </div>
                            </div>

                            <hr class="dropdown-divider mx-2">

                            {{-- Language Selection --}}
                            <div class="px-3 py-2">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small fw-600 text-muted"><i
                                            class="fa-solid fa-globe me-2"></i>Bahasa</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('language.change', 'en') }}"
                                        class="btn btn-sm {{ app()->getLocale() == 'en' ? 'btn-primary' : 'btn-outline-secondary' }} flex-fill py-2">English</a>
                                    <a href="{{ route('language.change', 'id') }}"
                                        class="btn btn-sm {{ app()->getLocale() == 'id' ? 'btn-primary' : 'btn-outline-secondary' }} flex-fill py-2">Indonesia</a>
                                </div>
                            </div>
                        </div>

                        <hr class="dropdown-divider m-0">


                        {{-- Logout --}}
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="dropdown-item py-3 px-3 d-flex align-items-center text-danger fw-bold">
                                    <i class="fa-solid fa-right-from-bracket me-3"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </li>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (only for admin, manager and warehouse) -->
            @if(in_array($user?->role?->name, ['superadmin', 'admin', 'manager', 'warehouse']))
                <div class="col-md-2 sidebar px-0" id="sidebar">
                    <!-- Mobile Sidebar Header -->
                    <div class="sidebar-header">
                        <span class="sidebar-title">{{ __('menu.menu') }}</span>
                        <button class="sidebar-close" id="sidebarClose">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    @if(in_array($user?->role?->name, ['superadmin', 'admin']))
                        <a href="{{ route('admin.dashboard') }}"
                            class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie"></i> {{ __('menu.dashboard') }}
                        </a>

                        <a href="{{ route('pos.pwa-orders') }}"
                            class="sidebar-link {{ request()->routeIs('pos.pwa-orders') ? 'active' : '' }}">
                            <i class="fa-solid fa-mobile-screen-button"></i> Pesanan PWA
                        </a>

                        <!-- Inventory Group -->
                        <div
                            class="sidebar-dropdown {{ request()->routeIs('admin.products*') || request()->routeIs('admin.categories*') || request()->routeIs('admin.promos*') ? 'active' : '' }}">
                            <div class="sidebar-link sidebar-dropdown-toggle">
                                <i class="fa-solid fa-box-archive"></i> {{ __('admin.inventory') ?? 'Inventory' }}
                                <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                            </div>
                            <ul class="sidebar-submenu">
                                <li>
                                    <a href="{{ route('admin.products') }}"
                                        class="submenu-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-box"></i> {{ __('menu.products') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.categories') }}"
                                        class="submenu-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-folder"></i> {{ __('menu.categories') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.units.index') }}"
                                        class="submenu-link {{ request()->routeIs('admin.units*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-scale-balanced"></i> {{ __('admin.units') ?? 'Unit Categories' }}
                                    </a>
                                </li>
                                @if(App\Models\Setting::get('admin_enable_promos', true))
                                    <li>
                                        <a href="{{ route('admin.promos.index') }}"
                                            class="submenu-link {{ request()->routeIs('admin.promos.index*') ? 'active' : '' }}">
                                            <i class="fa-solid fa-tags"></i> {{ __('admin.promos') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <!-- Warehouse Group -->
                        <div class="sidebar-dropdown {{ request()->routeIs('warehouse.*') ? 'active' : '' }}">
                            <div class="sidebar-link sidebar-dropdown-toggle">
                                <i class="fa-solid fa-warehouse"></i> {{ __('admin.warehouse_management') ?? 'Warehouse' }}
                                <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                            </div>
                            <ul class="sidebar-submenu">
                                <li>
                                    <a href="{{ route('warehouse.stock') }}"
                                        class="submenu-link {{ request()->routeIs('warehouse.stock*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-boxes-stacked"></i> {{ __('menu.stock_management') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.low-stock') }}"
                                        class="submenu-link {{ request()->routeIs('warehouse.low-stock*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-triangle-exclamation"></i> {{ __('menu.low_stock_alerts') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-movements') }}"
                                        class="submenu-link {{ request()->routeIs('warehouse.stock-movements*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-arrows-rotate"></i> {{ __('menu.stock_movements') }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Finance Group -->
                        <div
                            class="sidebar-dropdown {{ request()->routeIs('admin.expenses*') || request()->routeIs('admin.expense-categories*') ? 'active' : '' }}">
                            <div class="sidebar-link sidebar-dropdown-toggle">
                                <i class="fa-solid fa-wallet"></i> {{ __('menu.finance') ?? 'Finance' }}
                                <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                            </div>
                            <ul class="sidebar-submenu">
                                <li>
                                    <a href="{{ route('admin.expenses.index') }}"
                                        class="submenu-link {{ request()->routeIs('admin.expenses.index') ? 'active' : '' }}">
                                        <i class="fa-solid fa-file-invoice-dollar"></i> {{ __('menu.operational_expenses') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.expense-categories.index') }}"
                                        class="submenu-link {{ request()->routeIs('admin.expense-categories.index') ? 'active' : '' }}">
                                        <i class="fa-solid fa-tags"></i> {{ __('menu.expense_categories') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.returns.index') }}"
                                        class="submenu-link {{ request()->routeIs('admin.returns.index') ? 'active' : '' }}">
                                        <i class="fa-solid fa-rotate-left"></i> {{ __('admin.returns_management') }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- People Group -->
                        <div
                            class="sidebar-dropdown {{ request()->routeIs('admin.users*') || request()->routeIs('admin.suppliers*') ? 'active' : '' }}">
                            <div class="sidebar-link sidebar-dropdown-toggle">
                                <i class="fa-solid fa-user-group"></i> {{ __('admin.people') ?? 'People' }}
                                <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                            </div>
                            <ul class="sidebar-submenu">
                                <li>
                                    <a href="{{ route('admin.users') }}"
                                        class="submenu-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-users"></i> {{ __('menu.users') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.suppliers') }}"
                                        class="submenu-link {{ request()->routeIs('admin.suppliers*') && !request()->routeIs('admin.suppliers.pre_orders*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-truck"></i> {{ __('menu.suppliers') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.suppliers.pre_orders.index') }}"
                                        class="submenu-link {{ request()->routeIs('admin.suppliers.pre_orders*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-receipt"></i> {{ __('admin.pre_orders') ?? 'Pre-Orders' }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Konsinyasi Group -->
                        <div class="sidebar-dropdown {{ request()->routeIs('admin.consignors*') || request()->routeIs('admin.consignment*') ? 'active' : '' }}">
                            <div class="sidebar-link sidebar-dropdown-toggle">
                                <i class="fa-solid fa-handshake"></i> Konsinyasi (Titip Jual)
                                <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                            </div>
                            <ul class="sidebar-submenu">
                                <li>
                                    <a href="{{ route('admin.consignors.index') }}"
                                        class="submenu-link {{ request()->routeIs('admin.consignors*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-user-tie"></i> Daftar Penitip
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.consignment.items.index') }}"
                                        class="submenu-link {{ request()->routeIs('admin.consignment.items*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-boxes-stacked"></i> Barang Konsinyasi
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.consignment.settlements.index') }}"
                                        class="submenu-link {{ request()->routeIs('admin.consignment.settlements*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-money-bill-transfer"></i> Hutang & Pembayaran
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.consignment.reports.index') }}"
                                        class="submenu-link {{ request()->routeIs('admin.consignment.reports*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-chart-bar"></i> Laporan Konsinyasi
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Reports Group -->
                        @if(App\Models\Setting::get('admin_enable_reports', true))
                            <div
                                class="sidebar-dropdown {{ request()->routeIs('admin.reports*') || request()->routeIs('admin.audit*') ? 'active' : '' }}">
                                <div class="sidebar-link sidebar-dropdown-toggle">
                                    <i class="fa-solid fa-chart-line"></i> {{ __('menu.reports') }}
                                    <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                                </div>
                                <ul class="sidebar-submenu">
                                    <li>
                                        <a href="{{ route('admin.reports') }}"
                                            class="submenu-link {{ request()->is('admin/reports') ? 'active' : '' }}">
                                            <i class="fa-solid fa-th-large"></i> {{ __('admin.reports_hub') ?? 'Reports Hub' }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.reports.warehouse') }}"
                                            class="submenu-link {{ request()->routeIs('admin.reports.warehouse*') ? 'active' : '' }}">
                                            <i class="fa-solid fa-warehouse"></i> {{ __('admin.warehouse_report') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.reports.cashier') }}"
                                            class="submenu-link {{ request()->routeIs('admin.reports.cashier*') ? 'active' : '' }}">
                                            <i class="fa-solid fa-cash-register"></i> {{ __('admin.cashier_report') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.reports.finance') }}"
                                            class="submenu-link {{ request()->routeIs('admin.reports.finance*') ? 'active' : '' }}">
                                            <i class="fa-solid fa-file-invoice-dollar"></i> {{ __('admin.finance_report') }}
                                        </a>
                                    </li>
                                    @if(App\Models\Setting::get('admin_enable_audit_logs', true))
                                        <li>
                                            <a href="{{ route('admin.audit.index') }}"
                                                class="submenu-link {{ request()->routeIs('admin.audit.index*') ? 'active' : '' }}">
                                                <i class="fa-solid fa-clipboard-list"></i> {{ __('admin.logs_report') }}
                                            </a>
                                        </li>
                                    @endif

                                </ul>
                            </div>
                        @endif

                        <a href="{{ route('admin.settings') }}"
                            class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                            <i class="fa-solid fa-gear"></i> {{ __('menu.settings') ?? 'Settings' }}
                        </a>

                        @if($user?->role?->name === 'superadmin')
                            <div class="sidebar-group mt-3">
                                <span class="text-muted small px-3 text-uppercase fw-bold"
                                    style="font-size: 0.7rem; opacity: 0.6;">System Admin</span>
                                <a href="{{ route('superadmin.dashboard') }}"
                                    class="sidebar-link {{ request()->is('superadmin/dashboard') || request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                                    <i class="fa-solid fa-code"></i> Developer Tools
                                </a>
                                <a href="{{ route('superadmin.settings') }}"
                                    class="sidebar-link {{ request()->routeIs('superadmin.settings*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-gears"></i> Advanced Settings
                                </a>
                                <a href="{{ route('superadmin.payment-methods.index') }}"
                                    class="sidebar-link {{ request()->routeIs('superadmin.payment-methods.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-credit-card"></i> Kelola Pembayaran
                                </a>
                                <a href="{{ route('superadmin.faq') }}"
                                    class="sidebar-link {{ request()->routeIs('superadmin.faq*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-circle-question"></i> Kelola FAQ
                                </a>
                                <a href="{{ route('superadmin.identity-types.index') }}"
                                    class="sidebar-link {{ request()->routeIs('superadmin.identity-types.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-id-card"></i> Jenis ID (NIS/NIK/...)
                                </a>
                            </div>
                        @endif

                    @elseif($user?->role?->name === 'manager')
                        <a href="{{ route('manager.dashboard') }}"
                            class="sidebar-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie"></i> {{ __('menu.dashboard') }}
                        </a>

                        <!-- Reports Group (Read-only for Manager) -->
                        <div class="sidebar-dropdown {{ request()->routeIs('manager.reports*') ? 'active' : '' }}">
                            <div class="sidebar-link sidebar-dropdown-toggle">
                                <i class="fa-solid fa-chart-line"></i> {{ __('menu.reports') }}
                                <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                            </div>
                            <ul class="sidebar-submenu">
                                <li>
                                    <a href="{{ route('manager.reports') }}"
                                        class="submenu-link {{ request()->is('manager/reports') ? 'active' : '' }}">
                                        <i class="fa-solid fa-th-large"></i> {{ __('admin.reports_hub') ?? 'Reports Hub' }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('manager.reports.warehouse') }}"
                                        class="submenu-link {{ request()->routeIs('manager.reports.warehouse*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-warehouse"></i> {{ __('admin.warehouse_report') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('manager.reports.cashier') }}"
                                        class="submenu-link {{ request()->routeIs('manager.reports.cashier*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-cash-register"></i> {{ __('admin.cashier_report') }} /
                                        {{ __('admin.transaction_correction') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('manager.reports.finance') }}"
                                        class="submenu-link {{ request()->routeIs('manager.reports.finance*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-file-invoice-dollar"></i> {{ __('admin.finance_report') }}
                                    </a>
                                </li>
                                @if(App\Models\Setting::get('admin_enable_audit_logs', true))
                                    <li>
                                        <a href="{{ route('manager.audit.index') }}"
                                            class="submenu-link {{ request()->routeIs('manager.audit.index*') ? 'active' : '' }}">
                                            <i class="fa-solid fa-clipboard-list"></i> {{ __('admin.logs_report') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>

                    @elseif($user?->role?->name === 'warehouse')
                        <a href="{{ route('warehouse.dashboard') }}"
                            class="sidebar-link {{ request()->routeIs('warehouse.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie"></i> {{ __('menu.dashboard') }}
                        </a>
                        <a href="{{ route('warehouse.stock') }}"
                            class="sidebar-link {{ request()->routeIs('warehouse.stock') ? 'active' : '' }}">
                            <i class="fa-solid fa-warehouse"></i> {{ __('menu.stock_management') }}
                        </a>
                        <a href="{{ route('warehouse.low-stock') }}"
                            class="sidebar-link {{ request()->routeIs('warehouse.low-stock') ? 'active' : '' }}">
                            <i class="fa-solid fa-triangle-exclamation"></i> {{ __('menu.low_stock_alerts') }}
                        </a>
                        <a href="{{ route('warehouse.stock-movements') }}"
                            class="sidebar-link {{ request()->routeIs('warehouse.stock-movements') ? 'active' : '' }}">
                            <i class="fa-solid fa-arrows-rotate"></i> {{ __('menu.stock_movements') }}
                        </a>

                        {{-- Konsinyasi --}}
                        <div class="sidebar-dropdown {{ request()->routeIs('warehouse.consignment.*') ? 'active' : '' }}">
                            <div class="sidebar-link sidebar-dropdown-toggle">
                                <i class="fa-solid fa-handshake"></i> Konsinyasi (Titip Jual)
                                <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                            </div>
                            <ul class="sidebar-submenu">
                                <li>
                                    <a href="{{ route('warehouse.consignment.items.index') }}"
                                        class="submenu-link {{ request()->routeIs('warehouse.consignment.items*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-boxes-stacked"></i> Barang Konsinyasi
                                    </a>
                                </li>
                            </ul>
                        </div>

                        {{-- Pre-Order Supplier --}}
                        <div class="sidebar-dropdown {{ request()->routeIs('warehouse.pre-orders*') ? 'active' : '' }}">
                            <div class="sidebar-link sidebar-dropdown-toggle">
                                <i class="fa-solid fa-receipt"></i> Pre-Order Supplier
                                <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                            </div>
                            <ul class="sidebar-submenu">
                                <li>
                                    <a href="{{ route('warehouse.pre-orders.index') }}"
                                        class="submenu-link {{ request()->routeIs('warehouse.pre-orders*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-list"></i> Daftar Pre-Order
                                    </a>
                                </li>
                            </ul>
                        </div>

                        {{-- Pengaturan Gudang --}}
                        <div class="sidebar-dropdown {{ request()->routeIs('warehouse.categories*') || request()->routeIs('warehouse.units*') ? 'active' : '' }}">
                            <div class="sidebar-link sidebar-dropdown-toggle">
                                <i class="fa-solid fa-tags"></i> Pengaturan Gudang
                                <i class="fa-solid fa-chevron-right dropdown-arrow"></i>
                            </div>
                            <ul class="sidebar-submenu">
                                <li>
                                    <a href="{{ route('warehouse.categories.index') }}"
                                        class="submenu-link {{ request()->routeIs('warehouse.categories*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-layer-group"></i> Kategori Produk
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.units.index') }}"
                                        class="submenu-link {{ request()->routeIs('warehouse.units*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-ruler"></i> Satuan
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endif

                    <div class="mt-auto px-1 py-3">
                        <hr style="margin: 0.5rem 0; border-color: var(--brown-100); opacity: 0.1;">
                        @if(\App\Models\Setting::get('enable_faq', true))
                            <a href="{{ route('faq.index') }}"
                                class="sidebar-link {{ request()->routeIs('faq.index') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle-question"></i> Bantuan / FAQ
                            </a>
                        @endif

                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit"
                                class="sidebar-link text-danger border-0 bg-transparent w-100 text-start py-2 px-3"
                                style="transition: all 0.3s;">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> {{ __('menu.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-md-10 main-content">
            @else
                <div class="col-12 main-content no-sidebar">
            @endif

                    @yield('content')
                </div>

                <!-- Sidebar Overlay (Mobile) -->
                <div class="sidebar-overlay" id="sidebarOverlay"></div>

                <script>
                    // Hamburger menu functionality
                    const hamburgerBtn = document.getElementById('hamburgerBtn');
                    const sidebar = document.getElementById('sidebar');
                    const sidebarOverlay = document.getElementById('sidebarOverlay');
                    const sidebarClose = document.getElementById('sidebarClose');

                    if (hamburgerBtn) {
                        // Open sidebar
                        hamburgerBtn.addEventListener('click', function () {
                            sidebar.classList.add('active');
                            sidebarOverlay.classList.add('active');
                            document.body.style.overflow = 'hidden';
                        });

                        // Close sidebar - close button
                        sidebarClose.addEventListener('click', closeSidebar);

                        // Close sidebar - overlay click
                        sidebarOverlay.addEventListener('click', closeSidebar);

                        // Close sidebar function
                        function closeSidebar() {
                            sidebar.classList.remove('active');
                            sidebarOverlay.classList.remove('active');
                            document.body.style.overflow = '';
                        }

                        // Close sidebar when clicking a link (mobile/tablet)
                        if (window.innerWidth <= 1023) {
                            const sidebarLinks = sidebar.querySelectorAll('.sidebar-link:not(.sidebar-dropdown-toggle)');
                            sidebarLinks.forEach(link => {
                                link.addEventListener('click', closeSidebar);
                            });
                        }
                    }

                    // Sidebar Dropdown Toggle Logic
                    document.querySelectorAll('.sidebar-dropdown-toggle').forEach(toggle => {
                        toggle.addEventListener('click', function (e) {
                            e.preventDefault();
                            const parent = this.closest('.sidebar-dropdown');
                            const isActive = parent.classList.contains('active');

                            // Close other dropdowns (optional, but cleaner)
                            // document.querySelectorAll('.sidebar-dropdown').forEach(d => d.classList.remove('active'));

                            if (isActive) {
                                parent.classList.remove('active');
                            } else {
                                parent.classList.add('active');
                            }
                        });
                    });

                    // Ensure active dropdowns are open on load
                    document.addEventListener('DOMContentLoaded', function () {
                        const activeSubmenuLink = document.querySelector('.submenu-link.active');
                        if (activeSubmenuLink) {
                            const parentDropdown = activeSubmenuLink.closest('.sidebar-dropdown');
                            if (parentDropdown) {
                                parentDropdown.classList.add('active');
                            }
                        }

                        // ── Modal Teleport Fix ──────────────────────────────────────────
                        // Modals rendered inside .main-content (inside container-fluid > row)
                        // will have their position:fixed measured against the zoomed html
                        // root, causing the backdrop to not cover the full screen.
                        // Solution: move every .modal directly under <body> so it uses
                        // the true viewport as its containing block.
                        document.querySelectorAll('.modal').forEach(function (modal) {
                            if (modal.parentElement !== document.body) {
                                document.body.appendChild(modal);
                            }
                        });
                    });
                </script>

                {{-- Theme Toggle Script --}}
                <script>
                    (function () {
                        const themeOptions = document.querySelectorAll('.theme-option');
                        const themeIcon = document.getElementById('themeIcon');
                        const themeLabel = document.getElementById('themeLabel');
                        const htmlEl = document.documentElement;

                        function getEffectiveTheme(pref) {
                            if (pref === 'system') {
                                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                            }
                            return pref;
                        }

                        function applyTheme(pref) {
                            const effective = getEffectiveTheme(pref);
                            htmlEl.setAttribute('data-bs-theme', effective);
                            localStorage.setItem('artika-theme', pref);

                            // Update button icon & label if they exist
                            const themeIcon = document.getElementById('themeIcon');
                            const themeLabel = document.getElementById('themeLabel');
                            const icons = { light: 'fa-sun', dark: 'fa-moon', system: 'fa-desktop' };
                            const labels = { light: 'Light', dark: 'Dark', system: 'System' };

                            if (themeIcon) {
                                themeIcon.className = 'fa-solid ' + (icons[pref] || 'fa-sun');
                            }
                            if (themeLabel) {
                                themeLabel.textContent = labels[pref] || 'Light';
                            }

                            // Update active indicator
                            themeOptions.forEach(opt => {
                                if (opt.dataset.theme === pref) {
                                    opt.classList.add('active');
                                    opt.querySelector('.theme-check')?.classList.remove('d-none');
                                } else {
                                    opt.classList.remove('active');
                                    opt.querySelector('.theme-check')?.classList.add('d-none');
                                }
                            });
                        }

                        // Init
                        const saved = localStorage.getItem('artika-theme') || 'system';
                        applyTheme(saved);

                        // Click handlers
                        themeOptions.forEach(opt => {
                            opt.addEventListener('click', () => applyTheme(opt.dataset.theme));
                        });

                        // Listen for system preference changes
                        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                            if (localStorage.getItem('artika-theme') === 'system') {
                                applyTheme('system');
                            }
                        });
                    })();
                </script>

                @stack('scripts')

                <script>
                    // Professional Notification Helpers
                    const ArtikaToast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'artika-swal-toast'
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    function showToast(icon, title) {
                        ArtikaToast.fire({
                            icon: icon,
                            title: title
                        });
                    }

                    function confirmAction(options = {}) {
                        const defaults = {
                            title: 'Apakah Anda yakin?',
                            text: "Tindakan ini tidak dapat dibatalkan!",
                            icon: 'warning',
                            confirmButtonText: 'Ya, Lanjutkan!',
                            cancelButtonText: 'Batal'
                        };

                        const settings = { ...defaults, ...options };

                        return Swal.fire({
                            title: settings.title,
                            text: settings.text,
                            icon: settings.icon,
                            showCancelButton: true,
                            confirmButtonColor: 'var(--color-primary-dark)',
                            cancelButtonColor: 'var(--gray-100)',
                            confirmButtonText: settings.confirmButtonText,
                            cancelButtonText: settings.cancelButtonText,
                            customClass: {
                                popup: 'artika-swal-popup',
                                title: 'artika-swal-title',
                                confirmButton: 'artika-swal-confirm-btn',
                                cancelButton: 'artika-swal-cancel-btn'
                            },
                            buttonsStyling: false
                        });
                    }

                    // Flash Message Handling
                    document.addEventListener('DOMContentLoaded', function () {
                        @if(session('success') || session('status'))
                            showToast('success', @json(session('success') ?: session('status')));
                        @endif

                        @if(session('error'))
                            showToast('error', @json(session('error')));
                        @endif

                        @if(session('warning'))
                            showToast('warning', @json(session('warning')));
                        @endif

                        @if($errors->any())
                            showToast('error', @json($errors->first()));
                        @endif
            });

                    // Global Numeric Input Validation
                    document.addEventListener('keydown', function (e) {
                        if (e.target.tagName === 'INPUT' && e.target.type === 'number') {
                            // Block 'e', 'E', '-', '+', '.', ','
                            const blockedKeys = ['e', 'E', '-', '+', '.', ','];
                            if (blockedKeys.includes(e.key)) {
                                e.preventDefault();
                            }
                        }
                    });

                    // Prevent paste of non-numeric characters
                    document.addEventListener('paste', function (e) {
                        if (e.target.tagName === 'INPUT' && e.target.type === 'number') {
                            const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                            if (!/^\d+$/.test(pasteData)) {
                                e.preventDefault();
                                showToast('warning', 'Hanya angka bulat yang diperbolehkan');
                            }
                        }
                    });
                </script>

                {{-- PWA Service Worker Registration --}}
                <script>
                    if ('serviceWorker' in navigator) {
                        window.addEventListener('load', () => {
                            navigator.serviceWorker.register('/sw.js')
                                .then(reg => console.log('SW registered:', reg.scope))
                                .catch(err => console.log('SW registration failed:', err));
                        });
                    }
                </script>

                {{-- Offline Detector Banner --}}
                <div id="offlineBanner" class="offline-banner">
                    <i class="fa-solid fa-wifi" style="position: relative;">
                        <span style="position: absolute; width: 120%; height: 2px; background: white; top: 50%; left: -10%; transform: rotate(-45deg); display: block;"></span>
                    </i>
                    <span>Koneksi Internet Terputus</span>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const offlineBanner = document.getElementById('offlineBanner');

                        // Function to handle connection changes
                        function updateOnlineStatus() {
                            if (navigator.onLine) {
                                offlineBanner.classList.remove('show');
                                // Optional: You could show a quick success toast here
                                // showToast('success', 'Koneksi internet kembali stabil');
                            } else {
                                offlineBanner.classList.add('show');
                            }
                        }

                        // Listen to browser online/offline events
                        window.addEventListener('online', updateOnlineStatus);
                        window.addEventListener('offline', updateOnlineStatus);

                        // Check initial state on page load (just in case)
                        if (!navigator.onLine) {
                            updateOnlineStatus();
                        }
                    });
                </script>



    @stack('scripts')
</body>

</html>