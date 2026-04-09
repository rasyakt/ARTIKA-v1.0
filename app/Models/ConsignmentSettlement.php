<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class ConsignmentSettlement extends Model
{
    use Auditable;

    protected $fillable = [
        'consignor_id',
        'reference_no',
        'period_start',
        'period_end',
        'total_sold_qty',
        'total_sales_amount',
        'commission_amount',
        'amount_to_pay',
        'status',
        'paid_at',
        'payment_method',
        'payment_reference',
        'created_by',
        'paid_by',
        'notes',
    ];

    protected $casts = [
        'period_start'        => 'date',
        'period_end'          => 'date',
        'total_sold_qty'      => 'decimal:2',
        'total_sales_amount'  => 'decimal:2',
        'commission_amount'   => 'decimal:2',
        'amount_to_pay'       => 'decimal:2',
        'paid_at'             => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function consignor(): BelongsTo
    {
        return $this->belongsTo(Consignor::class)->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConsignmentSettlementItem::class, 'settlement_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Generate a unique reference number for a new settlement.
     */
    public static function generateReferenceNo(): string
    {
        $year  = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'SET-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
