<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PwaOrder extends Model
{
    protected $fillable = [
        'order_no',
        'customer_name',
        'customer_whatsapp',
        'delivery_location',
        'subtotal',
        'total_amount',
        'status',
        'processed_by',
        'processed_at',
        'completed_at',
        'cancelled_at',
        'cancel_reason',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get all items for this PWA order
     */
    public function items(): HasMany
    {
        return $this->hasMany(PwaOrderItem::class);
    }

    /**
     * Get the cashier who processed this order
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the linked POS transaction (after LUNAS)
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Generate WhatsApp link from customer phone number
     */
    public function getWhatsappLinkAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->customer_whatsapp);

        // Convert leading 0 to 62 (Indonesia country code)
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, prepend it
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return 'https://wa.me/' . $phone;
    }

    /**
     * Generate a unique order number
     * Format: PWA-YYYYMMDD-XXXXX
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');

        $todayCount = DB::table('pwa_orders')
            ->whereDate('created_at', now()->toDateString())
            ->lockForUpdate()
            ->count();

        $seq = str_pad($todayCount + 1, 5, '0', STR_PAD_LEFT);

        return "PWA-{$date}-{$seq}";
    }

    /**
     * Scope: only pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: only processing orders
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope: active orders (pending or processing)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    /**
     * Scope: today's orders
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
