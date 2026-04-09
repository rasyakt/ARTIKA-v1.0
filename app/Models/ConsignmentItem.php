<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class ConsignmentItem extends Model
{
    use Auditable;

    protected $fillable = [
        'consignor_id',
        'product_id',
        'quantity_received',
        'quantity_returned',
        'cost_to_consignor',
        'commission_rate',
        'received_at',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity_received'  => 'decimal:2',
        'quantity_returned'  => 'decimal:2',
        'cost_to_consignor'  => 'decimal:2',
        'commission_rate'    => 'decimal:2',
        'received_at'        => 'date',
        'expiry_date'        => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function consignor(): BelongsTo
    {
        return $this->belongsTo(Consignor::class)->withTrashed();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function settlementItems(): HasMany
    {
        return $this->hasMany(ConsignmentSettlementItem::class, 'consignment_item_id');
    }

    // ── Computed Accessors ────────────────────────────────────────────────────

    /**
     * Tarif komisi efektif: pakai override per-barang jika ada, jika tidak pakai tarif penitip.
     */
    public function getEffectiveCommissionRateAttribute(): float
    {
        if (!is_null($this->commission_rate)) {
            return (float) $this->commission_rate;
        }
        return (float) ($this->consignor?->commission_rate ?? 0);
    }

    /**
     * Jumlah unit yang sudah terjual melalui POS (telah disalurkan secara FIFO).
     * Mencegah double-counting jika ada beberapa batch produk yang sama.
     */
    public function getQuantitySoldAttribute(): float
    {
        if (!$this->product_id) return 0;

        // 1. Ambil semua batch untuk produk ini, diurutkan FIFO (urut tanggal masuk, lalu ID)
        $allBatches = self::where('product_id', $this->product_id)
            ->orderBy('received_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($allBatches->isEmpty()) return 0;

        // 2. Hitung TOTAL penjualan produk ini sejak batch pertama kali masuk
        $earliestDate = $allBatches->first()->received_at;
        $totalSoldAllTime = (float) \App\Models\TransactionItem::whereHas('transaction', fn ($q) =>
                $q->where('status', 'completed')
                  ->where('created_at', '>=', $earliestDate)
            )
            ->where('product_id', $this->product_id)
            ->sum('quantity');

        // 3. Alokasikan total penjualan tersebut ke tiap batch secara FIFO
        $precedingCapacity = 0;
        foreach ($allBatches as $batch) {
            $currentCapacity = (float) ($batch->quantity_received - $batch->quantity_returned);
            
            if ($batch->id === $this->id) {
                // Sisa penjualan yang masuk ke batch ini = Total - Penjualan yang sudah dikonsumsi batch sebelumnya
                // Tapi tidak boleh melebihi kapasitas batch ini
                $remainingSold = max(0, $totalSoldAllTime - $precedingCapacity);
                return min($currentCapacity, $remainingSold);
            }
            
            $precedingCapacity += $currentCapacity;
        }

        return 0;
    }

    /**
     * Sisa stok konsinyasi (diterima - terjual - dikembalikan).
     */
    public function getQuantityRemainingAttribute(): float
    {
        return max(0, (float) $this->quantity_received - $this->quantity_sold - (float) $this->quantity_returned);
    }

    /**
     * Total nilai penjualan (qty terjual × harga jual produk).
     */
    public function getSalesAmountAttribute(): float
    {
        return $this->quantity_sold * (float) ($this->product?->price ?? 0);
    }

    /**
     * Bagian komisi toko dari penjualan barang ini.
     */
    public function getCommissionAmountAttribute(): float
    {
        return $this->sales_amount * ($this->effective_commission_rate / 100);
    }

    /**
     * Jumlah yang sudah masuk dalam dokumen settlement sebelumnya.
     */
    public function getQuantityAlreadySettledAttribute(): float
    {
        return (float) $this->settlementItems()->sum('quantity');
    }

    /**
     * Sisa qty terjual yang belum dibayarkan via settlement.
     */
    public function getQuantityToSettleAttribute(): float
    {
        return max(0, $this->quantity_sold - $this->quantity_already_settled);
    }

    /**
     * Jumlah yang harus dibayarkan ke penitip = penjualan - komisi toko.
     */
    public function getAmountToPayAttribute(): float
    {
        return $this->sales_amount - $this->commission_amount;
    }

    /**
     * Nilai bersih (ke penitip) dari qty yang belum dibayarkan.
     */
    public function getUnsettledAmountAttribute(): float
    {
        $qtyToSettle = $this->quantity_to_settle;
        if ($qtyToSettle <= 0) return 0;

        $unitPrice = (float) ($this->product?->price ?? 0);
        $netUnitPrice = $unitPrice * (1 - ($this->effective_commission_rate / 100));

        return $qtyToSettle * $netUnitPrice;
    }
}
