@extends('layouts.app')
@section('title', 'Pesanan PWA')

@section('content')
@push('styles')
<style>
    /* Grid Layout for Cards */
    .pwa-orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.25rem;
        margin-top: 1rem;
    }

    .order-card-item {
        background: var(--card-bg);
        border-radius: 16px;
        border: 1px solid var(--gray-200);
        padding: 1.25rem;
        position: relative;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .order-card-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: var(--color-primary-light);
    }

    .order-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .order-meta {
        display: flex;
        flex-direction: column;
    }

    .order-no {
        font-family: monospace;
        font-size: 0.7rem;
        color: var(--gray-400);
        font-weight: 700;
    }

    .order-time {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--color-primary-dark);
    }

    /* Dropdown Action Menu */
    .order-actions-dropdown {
        position: relative;
    }

    .ellipsis-btn {
        background: var(--gray-100);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--gray-600);
        transition: all 0.2s;
    }

    .ellipsis-btn:hover { background: var(--gray-200); color: var(--color-primary); }

    .dropdown-menu-custom {
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid var(--gray-100);
        z-index: 100;
        min-width: 160px;
        padding: 8px;
        display: none;
    }

    .dropdown-menu-custom.active { display: block; }

    .dropdown-item-custom {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--gray-700);
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        border: none;
        background: none;
        text-align: left;
    }

    .dropdown-item-custom:hover { background: var(--gray-50); color: var(--color-primary); }
    .dropdown-item-custom.danger { color: #EF4444; }
    .dropdown-item-custom.danger:hover { background: #FEF2F2; }

    /* Card Contact & Location */
    .order-card-customer {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--primary-50);
        color: var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
    }

    .customer-info-wrap { flex: 1; min-width: 0; }
    .customer-name { font-weight: 700; font-size: 0.95rem; margin-bottom: 2px; }

    .order-card-footer {
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px dashed var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-total-price {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--color-primary-dark);
    }

    /* Modal Details */
    .order-detail-modal .modal-content {
        border-radius: 20px;
        border: none;
        overflow: hidden;
    }
    .order-item-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid var(--gray-100);
    }
    .order-item-row:last-child { border-bottom: none; }
    .order-item-name { font-weight: 600; }
    .order-item-sub { color: var(--gray-500); font-size: 0.85rem; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-badge.pending { background: #FEF3C7; color: #92400E; }
    .status-badge.processing { background: #DBEAFE; color: #1E40AF; }
    .status-badge.completed { background: #DCFCE7; color: #166534; }
    .status-badge.cancelled { background: #FEE2E2; color: #991B1B; }

    .pulse-dot { width: 6px; height: 6px; border-radius: 50%; animation: pulse 2s infinite; }
    .status-badge.pending .pulse-dot { background: #D97706; }
    .status-badge.processing .pulse-dot { background: #2563EB; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }

    .pwa-dashboard {
        padding: 1.5rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Header */
    .pwa-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .pwa-header h2 {
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        color: var(--color-primary-dark);
    }

    .badge-live {
        background: #EF4444;
        color: white;
        font-size: 0.65rem;
        padding: 2px 8px;
        border-radius: 6px;
        letter-spacing: 1px;
        animation: blink 2s infinite;
    }

    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

    .sound-toggle {
        background: var(--card-bg);
        border: 1px solid var(--gray-200);
        padding: 8px 16px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--gray-700);
        cursor: pointer;
        transition: all 0.2s;
    }

    .sound-toggle:hover { border-color: var(--color-primary); color: var(--color-primary); }
    .sound-toggle.muted { background: #FEF2F2; color: #EF4444; border-color: #FEE2E2; }

    /* Share Link */
    .pwa-share-link {
        background: var(--card-bg);
        border: 1px dashed var(--color-primary);
        padding: 1rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 2rem;
    }

    .link-text {
        font-family: monospace;
        color: var(--gray-600);
        font-size: 0.9rem;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .copy-btn {
        background: var(--color-primary);
        color: white;
        border: none;
        padding: 6px 14px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
    }

    /* Stats */
    .pwa-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background: var(--card-bg);
        padding: 1.25rem;
        border-radius: 16px;
        border: 1px solid var(--gray-200);
        display: flex;
        flex-direction: column;
        gap: 4px;
        position: relative;
        overflow: hidden;
    }

    .stat-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 4rem;
        opacity: 0.05;
        transform: rotate(-15deg);
    }

    .stat-value { font-size: 1.75rem; font-weight: 800; }
    .stat-label { font-size: 0.8rem; font-weight: 600; color: var(--gray-500); text-uppercase: uppercase; letter-spacing: 0.5px; }

    .stat-card.pending { border-left: 5px solid #F59E0B; }
    .stat-card.pending .stat-value { color: #D97706; }
    .stat-card.processing { border-left: 5px solid #3B82F6; }
    .stat-card.processing .stat-value { color: #2563EB; }
    .stat-card.completed { border-left: 5px solid #10B981; }
    .stat-card.completed .stat-value { color: #059669; }
    .stat-card.revenue { background: var(--color-primary-dark); border: none; color: white; }
    .stat-card.revenue .stat-label { color: rgba(255,255,255,0.7); }

    /* New Order Animation */
    @keyframes highlightCard { 
        0% { transform: scale(1.05); border-color: #F59E0B; background: #FFFBEB; }
        100% { transform: scale(1); border-color: var(--gray-200); background: var(--card-bg); }
    }
    .card-new { animation: highlightCard 3s ease-out; }
</style>
@endpush

<div class="pwa-dashboard">
    <!-- Header -->
    <div class="pwa-header">
        <h2>
            <i class="fas fa-mobile-alt"></i> Pesanan PWA
            <span class="badge-live" id="liveBadge">LIVE</span>
        </h2>
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('pos.index') }}" class="sound-toggle" title="Kembali ke POS">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke POS</span>
            </a>
            <button class="sound-toggle" id="soundToggle" onclick="toggleSound()">
                <i class="fas fa-volume-up" id="soundIcon"></i>
                <span id="soundLabel">Suara ON</span>
            </button>
        </div>
    </div>

    <!-- PWA Link Share -->
    <div class="pwa-share-link">
        <i class="fas fa-link" style="color: var(--color-primary); font-size: 1.1rem;"></i>
        <span class="link-text" id="pwaLink">{{ url('/pwa') }}</span>
        <button class="copy-btn" onclick="copyPwaLink()">
            <i class="fas fa-copy"></i> Salin Link
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="pwa-stats">
        <div class="stat-card pending">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value" id="statPending">{{ $stats['pending'] }}</div>
            <div class="stat-label">Menunggu</div>
        </div>
        <div class="stat-card processing">
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="stat-value" id="statProcessing">{{ $stats['processing'] }}</div>
            <div class="stat-label">Diproses</div>
        </div>
        <div class="stat-card completed">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value" id="statCompleted">{{ $stats['completed'] }}</div>
            <div class="stat-label">Selesai Hari Ini</div>
        </div>
        <div class="stat-card revenue">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value" id="statRevenue" style="font-size:1.3rem;">Rp{{ number_format($stats['revenue'],0,',','.') }}</div>
            <div class="stat-label">Pendapatan PWA</div>
        </div>
    </div>

    <!-- Orders Grid -->
    <div class="pwa-orders-grid" id="ordersGrid">
        @forelse($orders as $order)
            <!-- Handled by buildOrderCard in JS for consistency, but initially rendered here -->
            <div class="order-card-item {{ $order->status === 'pending' ? 'card-new' : '' }}" id="order-card-{{ $order->id }}">
                <div class="order-card-header">
                    <div class="order-meta">
                        <span class="order-time">{{ $order->created_at->format('H:i') }} • {{ $order->created_at->format('d M') }}</span>
                        <span class="order-no">{{ $order->order_no }}</span>
                    </div>
                    <div class="order-actions-dropdown">
                        <button class="ellipsis-btn" onclick="toggleOrderDropdown(event, {{ $order->id }})">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu-custom" id="dropdown-{{ $order->id }}">
                            <button class="dropdown-item-custom" onclick="showOrderDetail({{ $order->id }})">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </button>
                            @if($order->status === 'pending')
                                <button class="dropdown-item-custom" onclick="processOrder({{ $order->id }})">
                                    <i class="fas fa-play" style="color:#22C55E"></i> Proses Pesanan
                                </button>
                                <button class="dropdown-item-custom danger" onclick="cancelOrder({{ $order->id }})">
                                    <i class="fas fa-times"></i> Batalkan
                                </button>
                            @elseif($order->status === 'processing')
                                <button class="dropdown-item-custom" onclick="completeOrder({{ $order->id }})">
                                    <i class="fas fa-money-bill-wave" style="color:#3B82F6"></i> Tandai Lunas
                                </button>
                                <button class="dropdown-item-custom" onclick="printReceipt({{ $order->id }})">
                                    <i class="fas fa-print"></i> Cetak Struk
                                </button>
                                <button class="dropdown-item-custom danger" onclick="cancelOrder({{ $order->id }})">
                                    <i class="fas fa-times"></i> Batalkan
                                </button>
                            @elseif($order->status === 'completed')
                                <button class="dropdown-item-custom" onclick="printReceipt({{ $order->id }})">
                                    <i class="fas fa-print"></i> Cetak Struk
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="order-card-customer">
                    <div class="customer-avatar">{{ substr($order->customer_name, 0, 1) }}</div>
                    <div class="customer-info-wrap">
                        <div class="customer-name">{{ $order->customer_name }}</div>
                        <div style="font-size:0.75rem; color:#22C55E; font-weight:600;">
                            <i class="fab fa-whatsapp"></i> {{ $order->customer_whatsapp }}
                        </div>
                    </div>
                </div>

                <div>
                    <span class="location-chip">
                        <i class="fas fa-map-marker-alt"></i> {{ $order->delivery_location }}
                    </span>
                    @php 
                        $statusMap = [
                            'pending' => ['label' => 'Menunggu', 'class' => 'pending'],
                            'processing' => ['label' => 'Diproses', 'class' => 'processing'],
                            'completed' => ['label' => 'Lunas', 'class' => 'completed'],
                            'cancelled' => ['label' => 'Batal', 'class' => 'cancelled']
                        ];
                        $st = $statusMap[$order->status];
                    @endphp
                    <span class="status-badge {{ $st['class'] }}">
                        @if($order->status === 'pending' || $order->status === 'processing')
                            <span class="pulse-dot"></span>
                        @endif
                        {{ $st['label'] }}
                    </span>
                </div>

                <div class="order-card-footer">
                    <div class="order-total-price">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</div>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 4rem 1rem;" id="emptyState">
                <i class="fas fa-inbox" style="font-size: 3rem; color: var(--gray-200); margin-bottom: 1rem;"></i>
                <h3 style="color: var(--gray-500); font-weight: 700;">Belum ada pesanan PWA</h3>
                <p style="color: var(--gray-400);">Link PWA: {{ url('/pwa') }}</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade order-detail-modal" id="orderDetailModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="detailOrderNo">#ORD-000</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 mb-3 bg-light rounded-lg">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pelanggan</span>
                        <span class="font-weight-bold" id="detailCustomer">...</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">WhatsApp</span>
                        <span class="font-weight-bold text-success" id="detailWhatsapp">...</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Lokasi</span>
                        <span class="font-weight-bold" id="detailLocation">...</span>
                    </div>
                </div>

                <div class="detail-items-list" id="detailItemsList">
                    <!-- Items will be injected here -->
                </div>

                <div id="detailNotesBox" class="mt-3 p-2 bg-warning-50 rounded" style="display:none; border-left: 3px solid #F59E0B;">
                    <small class="text-muted d-block">Catatan:</small>
                    <span id="detailNotes" class="small font-italic"></span>
                </div>

                <div class="mt-4 p-3 rounded-lg" style="background: var(--color-primary-dark); color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold">TOTAL BAYAR</span>
                        <span class="h4 font-weight-bolder mb-0" id="detailTotal">Rp0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-block rounded-pill py-2" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const API_LIST = '{{ route("pos.pwa-orders.api.list") }}';
    // Store full order data for modal
    let cachedOrders = @json($orders);
    let lastOrderCount = cachedOrders.length;
    let soundEnabled = true;
    let audioCtx = null;

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.order-actions-dropdown')) {
            document.querySelectorAll('.dropdown-menu-custom').forEach(m => m.classList.remove('active'));
        }
    });

    function toggleOrderDropdown(e, id) {
        e.stopPropagation();
        const el = document.getElementById('dropdown-' + id);
        const isActive = el.classList.contains('active');
        document.querySelectorAll('.dropdown-menu-custom').forEach(m => m.classList.remove('active'));
        if (!isActive) el.classList.add('active');
    }

    async function showOrderDetail(id) {
        const order = cachedOrders.find(o => o.id == id);
        if (!order) return;

        document.getElementById('detailOrderNo').textContent = order.order_no;
        document.getElementById('detailCustomer').textContent = order.customer_name;
        document.getElementById('detailWhatsapp').textContent = order.customer_whatsapp;
        document.getElementById('detailLocation').textContent = order.delivery_location;
        document.getElementById('detailTotal').textContent = 'Rp' + formatNumber(order.total_amount);

        if (order.notes) {
            document.getElementById('detailNotesBox').style.display = 'block';
            document.getElementById('detailNotes').textContent = order.notes;
        } else {
            document.getElementById('detailNotesBox').style.display = 'none';
        }

        const itemsWrap = document.getElementById('detailItemsList');
        itemsWrap.innerHTML = order.items.map(item => `
            <div class="order-item-row">
                <div>
                    <div class="order-item-name">${item.product_name}</div>
                    <div class="order-item-sub">Rp${formatNumber(item.price)} x ${item.quantity}</div>
                </div>
                <div class="font-weight-bold">Rp${formatNumber(item.subtotal)}</div>
            </div>
        `).join('');

        const modalEl = document.getElementById('orderDetailModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    async function pollOrders() {
        try {
            const res = await fetch(API_LIST);
            const data = await res.json();
            if (!data.success) return;

            // Update stats
            document.getElementById('statPending').textContent = data.stats.pending;
            document.getElementById('statProcessing').textContent = data.stats.processing;
            document.getElementById('statCompleted').textContent = data.stats.completed;
            document.getElementById('statRevenue').textContent = 'Rp' + formatNumber(data.stats.revenue);

            // Check for sound
            if (data.orders.length > lastOrderCount) {
                const hasPending = data.orders.some(o => o.status === 'pending' && !cachedOrders.find(old => old.id === o.id));
                if (hasPending) playNotificationSound();
            }
            lastOrderCount = data.orders.length;
            cachedOrders = data.orders; // Sync cache

            // Rebuild grid
            const grid = document.getElementById('ordersGrid');
            if (data.orders.length === 0) {
                grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 4rem 1rem;" id="emptyState">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: var(--gray-200); margin-bottom: 1rem;"></i>
                    <h3 style="color: var(--gray-500); font-weight: 700;">Belum ada pesanan PWA</h3>
                    <p style="color: var(--gray-400);">Link PWA: {{ url('/pwa') }}</p>
                </div>`;
                return;
            }

            grid.innerHTML = data.orders.map(order => buildOrderCard(order)).join('');
        } catch (err) { console.error('Poll error:', err); }
    }

    setInterval(pollOrders, 5000);

    function buildOrderCard(order) {
        const time = new Date(order.created_at);
        const timeStr = time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const dateStr = time.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });

        const statusMap = {
            pending:    { label: 'Menunggu',  class: 'pending',    dot: true },
            processing: { label: 'Diproses',  class: 'processing', dot: true },
            completed:  { label: 'Lunas',     class: 'completed',  dot: false },
            cancelled:  { label: 'Batal',     class: 'cancelled', dot: false },
        };
        const st = statusMap[order.status] || statusMap.pending;

        // Action menu items
        let menuItems = `<button class="dropdown-item-custom" onclick="showOrderDetail(${order.id})"><i class="fas fa-eye"></i> Lihat Detail</button>`;
        if (order.status === 'pending') {
            menuItems += `
                <button class="dropdown-item-custom" onclick="processOrder(${order.id})"><i class="fas fa-play" style="color:#22C55E"></i> Proses Pesanan</button>
                <button class="dropdown-item-custom danger" onclick="cancelOrder(${order.id})"><i class="fas fa-times"></i> Batalkan</button>`;
        } else if (order.status === 'processing') {
            menuItems += `
                <button class="dropdown-item-custom" onclick="completeOrder(${order.id})"><i class="fas fa-money-bill-wave" style="color:#3B82F6"></i> Tandai Lunas</button>
                <button class="dropdown-item-custom" onclick="printReceipt(${order.id})"><i class="fas fa-print"></i> Cetak Struk</button>
                <button class="dropdown-item-custom danger" onclick="cancelOrder(${order.id})"><i class="fas fa-times"></i> Batalkan</button>`;
        } else if (order.status === 'completed') {
            menuItems += `<button class="dropdown-item-custom" onclick="printReceipt(${order.id})"><i class="fas fa-print"></i> Cetak Struk</button>`;
        }

        return `
            <div class="order-card-item ${order.status === 'pending' ? 'card-new' : ''}" id="order-card-${order.id}">
                <div class="order-card-header">
                    <div class="order-meta">
                        <span class="order-time">${timeStr} • ${dateStr}</span>
                        <span class="order-no">${order.order_no}</span>
                    </div>
                    <div class="order-actions-dropdown">
                        <button class="ellipsis-btn" onclick="toggleOrderDropdown(event, ${order.id})">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu-custom" id="dropdown-${order.id}">
                            ${menuItems}
                        </div>
                    </div>
                </div>
                <div class="order-card-customer">
                    <div class="customer-avatar">${escapeHtml(order.customer_name[0])}</div>
                    <div class="customer-info-wrap">
                        <div class="customer-name">${escapeHtml(order.customer_name)}</div>
                        <div style="font-size:0.75rem; color:#22C55E; font-weight:600;">
                            <i class="fab fa-whatsapp"></i> ${escapeHtml(order.customer_whatsapp)}
                        </div>
                    </div>
                </div>
                <div>
                    <span class="location-chip"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(order.delivery_location)}</span>
                    <span class="status-badge ${st.class}">${st.dot ? '<span class="pulse-dot"></span>' : ''} ${st.label}</span>
                </div>
                <div class="order-card-footer">
                    <div class="order-total-price">Rp${formatNumber(order.total_amount)}</div>
                </div>
            </div>`;
    }

    // ==========================
    //  ORDER ACTIONS
    // ==========================
    async function processOrder(id) {
        if (!confirm('Proses pesanan ini?\n\nStok akan dipotong otomatis dan struk akan dicetak.')) return;

        try {
            const res = await fetch(`/pos/pwa-orders/${id}/process`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Pesanan Diproses!',
                    text: 'Stok sudah dipotong. Mau cetak struk?',
                    showCancelButton: true,
                    confirmButtonText: '🖨️ Cetak Struk',
                    cancelButtonText: 'Nanti',
                    customClass: { popup: 'artika-swal-popup', confirmButton: 'artika-swal-confirm-btn', cancelButton: 'artika-swal-cancel-btn' },
                }).then(result => {
                    if (result.isConfirmed && data.receipt_url) {
                        window.open(data.receipt_url, '_blank');
                    }
                });
                pollOrders();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, customClass: { popup: 'artika-swal-popup' } });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memproses pesanan.', customClass: { popup: 'artika-swal-popup' } });
        }
    }

    async function completeOrder(id) {
        const result = await Swal.fire({
            icon: 'question',
            title: 'Tandai LUNAS?',
            text: 'Pembayaran sudah diterima? Pesanan akan masuk ke laporan penjualan harian.',
            showCancelButton: true,
            confirmButtonText: '💰 Ya, LUNAS',
            cancelButtonText: 'Batal',
            customClass: { popup: 'artika-swal-popup', confirmButton: 'artika-swal-confirm-btn', cancelButton: 'artika-swal-cancel-btn' },
        });

        if (!result.isConfirmed) return;

        try {
            const res = await fetch(`/pos/pwa-orders/${id}/complete`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'LUNAS! ✅',
                    html: `Pesanan sudah masuk laporan penjualan.<br><small>Invoice: <strong>${data.invoice_no}</strong></small>`,
                    customClass: { popup: 'artika-swal-popup', confirmButton: 'artika-swal-confirm-btn' },
                });
                pollOrders();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, customClass: { popup: 'artika-swal-popup' } });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menyelesaikan pesanan.', customClass: { popup: 'artika-swal-popup' } });
        }
    }

    async function cancelOrder(id) {
        const { value: reason } = await Swal.fire({
            icon: 'warning',
            title: 'Batalkan Pesanan?',
            input: 'text',
            inputLabel: 'Alasan pembatalan (opsional)',
            inputPlaceholder: 'Contoh: Stok habis',
            showCancelButton: true,
            confirmButtonText: '❌ Batalkan',
            cancelButtonText: 'Kembali',
            customClass: {
                popup: 'artika-swal-popup',
                confirmButton: 'artika-swal-confirm-btn',
                cancelButton: 'artika-swal-cancel-btn',
            },
            inputAttributes: { autocapitalize: 'off', autocomplete: 'off' },
        });

        if (reason === undefined) return; // user cancelled the modal

        try {
            const res = await fetch(`/pos/pwa-orders/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reason: reason || 'Dibatalkan oleh kasir' }),
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Dibatalkan',
                    text: data.message,
                    customClass: { popup: 'artika-swal-popup', confirmButton: 'artika-swal-confirm-btn' },
                });
                pollOrders();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, customClass: { popup: 'artika-swal-popup' } });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal membatalkan pesanan.', customClass: { popup: 'artika-swal-popup' } });
        }
    }

    function printReceipt(id) {
        window.open(`/pos/pwa-orders/${id}/receipt`, '_blank');
    }

    // ==========================
    //  SOUND NOTIFICATION
    // ==========================
    function playNotificationSound() {
        if (!soundEnabled) return;

        // Try to initialize or resume context
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        try {
            const playTone = (freq, time, duration) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0, audioCtx.currentTime + time);
                gain.gain.linearRampToValueAtTime(0.3, audioCtx.currentTime + time + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + time + duration);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(audioCtx.currentTime + time);
                osc.stop(audioCtx.currentTime + time + duration);
            };

            playTone(880, 0, 0.4);      // A5
            playTone(1108, 0.2, 0.4);   // C#6
            playTone(1318, 0.4, 0.6);   // E6
        } catch (e) {
            console.error('Sound error:', e);
        }
    }

    function toggleSound() {
        soundEnabled = !soundEnabled;
        const btn = document.getElementById('soundToggle');
        const icon = document.getElementById('soundIcon');
        const label = document.getElementById('soundLabel');

        if (soundEnabled) {
            btn.classList.remove('muted');
            icon.className = 'fas fa-volume-up';
            label.textContent = 'Suara ON';
        } else {
            btn.classList.add('muted');
            icon.className = 'fas fa-volume-mute';
            label.textContent = 'Suara OFF';
        }
    }

    // ==========================
    //  UTILITIES
    // ==========================
    function copyPwaLink() {
        const link = document.getElementById('pwaLink').textContent;
        navigator.clipboard.writeText(link).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Link Disalin!',
                text: 'Bagikan link ini ke siswa & guru.',
                timer: 2000,
                showConfirmButton: false,
                customClass: { popup: 'artika-swal-popup' },
            });
        });
    }

    function formatNumber(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush
@endsection
