<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReport extends Model
{
    protected $fillable = [
        'report_date',
        'product_id',
        'beginning_inventory',
        'ending_inventory',
        'physical_count',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'beginning_inventory' => 'integer',
        'ending_inventory' => 'integer',
        'physical_count' => 'integer',
    ];

    // === RELATIONSHIPS ===
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // === ATRIBUTES ===
    public function getVarianceAttribute(): int
    {
        return $this->physical_count - $this->ending_inventory;
    }

    // === SCOPES ===
    public function scopeForDate($query, $date)
    {
        return $query->where('report_date', $date);
    }

    public function scopeSearch($query, $term)
    {
        return $query->whereHas('product', function ($q) use ($term) {
            $q->where('product_name', 'like', "%{$term}%")
                ->orWhere('product_code', 'like', "%{$term}%")
                ->orWhere('product_brand', 'like', "%{$term}%");
        });
    }
}
