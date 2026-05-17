<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['product_id', 'quantity', 'expiration_date', 'batch_no'];
    protected $casts = ['expiration_date' => 'date'];

    // === SCOPES ===
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiration_date')
                ->orWhere('expiration_date', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', now());
    }

    public function scopeNearExpiry($query, $days = 30)
    {
        return $query->whereBetween('expiration_date', [now(), now()->addDays($days)]);
    }

    // === RELATIONSHIPS ===
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
