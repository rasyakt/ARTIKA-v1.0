<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Consignor extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'bank_name',
        'bank_account',
        'bank_holder',
        'commission_rate',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function consignmentItems(): HasMany
    {
        return $this->hasMany(ConsignmentItem::class)->orderBy('received_at', 'asc')->orderBy('id', 'asc');
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(ConsignmentItem::class)->where('status', 'active');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(ConsignmentSettlement::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Total nilai penjualan semua barang penitip (unsettled).
     */
    public function getPendingSettleAmountAttribute(): float
    {
        return (float) $this->consignmentItems()
            ->where('status', 'active')
            ->get()
            ->sum(fn ($item) => $item->amount_to_pay);
    }

    /**
     * Total barang aktif yang sedang dititipkan.
     */
    public function getActiveItemsCountAttribute(): int
    {
        return $this->consignmentItems()->where('status', 'active')->count();
    }
}
