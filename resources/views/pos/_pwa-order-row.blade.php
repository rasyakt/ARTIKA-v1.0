@php /** @var \App\Models\PwaOrder $order */ @endphp
<tr class="{{ $order->status === 'pending' ? 'new-order' : '' }}">
    <td>
        <div class="time-display">
            <div>{{ $order->created_at->format('H:i') }}</div>
            <div class="time-ago">{{ $order->created_at->format('d M') }}</div>
            <div style="font-size:0.68rem;color:var(--gray-400);margin-top:2px;">{{ $order->order_no }}</div>
        </div>
    </td>
    <td>
        <div class="customer-name">{{ $order->customer_name }}</div>
        <a href="{{ $order->whatsapp_link }}" target="_blank" class="customer-wa-link">
            <i class="fab fa-whatsapp"></i> {{ $order->customer_whatsapp }}
        </a>
    </td>
    <td>
        <span class="location-chip">
            <i class="fas fa-map-marker-alt"></i>
            {{ $order->delivery_location }}
        </span>
        @if($order->notes)
            <div class="order-notes"><i class="fas fa-comment-dots"></i> {{ $order->notes }}</div>
        @endif
    </td>
    <td>
        <ul class="order-items-list">
            @foreach($order->items as $item)
                <li>
                    <span class="item-detail">{{ $item->product_name }}</span>
                    <span class="item-qty">{{ $item->quantity }}x</span>
                    <span class="item-sub">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </li>
            @endforeach
        </ul>
    </td>
    <td>
        <div class="order-total">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</div>
    </td>
    <td>
        @switch($order->status)
            @case('pending')
                <span class="status-badge pending"><span class="pulse-dot"></span> Menunggu</span>
                @break
            @case('processing')
                <span class="status-badge processing"><span class="pulse-dot"></span> Diproses</span>
                @break
            @case('completed')
                <span class="status-badge completed">Lunas</span>
                @break
            @case('cancelled')
                <span class="status-badge cancelled">Dibatalkan</span>
                @break
        @endswitch
    </td>
    <td>
        <div class="action-btns">
            @if($order->status === 'pending')
                <button class="btn-action btn-proses" onclick="processOrder({{ $order->id }})">
                    <i class="fas fa-check"></i> Proses
                </button>
                <button class="btn-action btn-batal" onclick="cancelOrder({{ $order->id }})">
                    <i class="fas fa-times"></i> Batal
                </button>
            @elseif($order->status === 'processing')
                <button class="btn-action btn-lunas" onclick="completeOrder({{ $order->id }})">
                    <i class="fas fa-money-bill-wave"></i> Lunas
                </button>
                <button class="btn-action btn-receipt" onclick="printReceipt({{ $order->id }})">
                    <i class="fas fa-print"></i>
                </button>
                <button class="btn-action btn-batal" onclick="cancelOrder({{ $order->id }})">
                    <i class="fas fa-times"></i> Batal
                </button>
            @elseif($order->status === 'completed')
                <button class="btn-action btn-receipt" onclick="printReceipt({{ $order->id }})">
                    <i class="fas fa-print"></i> Struk
                </button>
            @else
                <span style="font-size:0.75rem;color:var(--gray-400);">—</span>
            @endif
        </div>
    </td>
</tr>
