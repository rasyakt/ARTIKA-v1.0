<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPreOrder extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = [
        'uuid',
        'supplier_id',
        'reference_number',
        'status',
        'expected_arrival_date',
        'total_amount',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'expected_arrival_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierPreOrderItem::class);
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
