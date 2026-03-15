<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Traits\Auditable;

class Product extends Model
{
    use Auditable;

    protected $fillable = [
        'barcode',
        'name',
        'category_id',
        'price',
        'cost_price',
        'unit',
        'description',
        'image',
        'is_active',
        'is_favorite',
        'is_consignment',
        'consignor_id',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'cost_price'     => 'decimal:2',
        'is_active'      => 'boolean',
        'is_favorite'    => 'boolean',
        'is_consignment' => 'boolean',
    ];

    /**
     * Get the category for this product
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all stocks for this product
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Get the first stock record (singular)
     */
    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    /**
     * Get total stock
     */
    public function getTotalStockAttribute()
    {
        return $this->stocks()->sum('quantity');
    }

    /**
     * Get available stock (only non-expired batches)
     */
    public function getAvailableStockAttribute()
    {
        return $this->stocks()
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->sum('quantity');
    }

    /**
     * Get profit margin
     */
    public function getProfitMarginAttribute()
    {
        if ($this->cost_price == 0) {
            return 0;
        }
        return (($this->price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * Get earliest expiration date among batches
     */
    public function getEarliestExpiryAttribute()
    {
        return $this->stocks()
            ->where('quantity', '>', 0)
            ->whereNotNull('expired_at')
            ->orderBy('expired_at', 'asc')
            ->value('expired_at');
    }

    /**
     * Get next expiration date (only non-expired batches)
     */
    public function getNextExpiryAttribute()
    {
        return $this->stocks()
            ->where('quantity', '>', 0)
            ->whereNotNull('expired_at')
            ->where('expired_at', '>', now())
            ->orderBy('expired_at', 'asc')
            ->value('expired_at');
    }
    /**
     * Get all purchases from suppliers for this product
     */
    public function supplierPurchases(): HasMany
    {
        return $this->hasMany(SupplierPurchase::class);
    }

    /**
     * Get the consignor (penitip) of this product if it's a consignment product
     */
    public function consignor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Consignor::class);
    }

    /**
     * Get all consignment item records for this product
     */
    public function consignmentItems(): HasMany
    {
        return $this->hasMany(ConsignmentItem::class);
    }
}

