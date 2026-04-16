<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PwaOrderItem extends Model
{
    protected $fillable = [
        'pwa_order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the PWA order this item belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(PwaOrder::class, 'pwa_order_id');
    }

    /**
     * Get the product for this item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
