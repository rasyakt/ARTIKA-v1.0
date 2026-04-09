<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentSettlementItem extends Model
{
    protected $fillable = [
        'settlement_id',
        'consignment_item_id',
        'quantity',
        'price',
        'commission_rate',
        'amount_to_pay',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'price'           => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'amount_to_pay'   => 'decimal:2',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(ConsignmentSettlement::class);
    }

    public function consignmentItem(): BelongsTo
    {
        return $this->belongsTo(ConsignmentItem::class);
    }
}
