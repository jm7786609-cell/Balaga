<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $fillable = ['product_id', 'quantity', 'delivery_date', 'expiration_date', 'lot_number', 'batch_no', 'user_id'];
    protected $casts = ['delivery_date' => 'date', 'expiration_date' => 'date'];

    // === SCOPES ===
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0);
    }

    // === RELATIONSHIPS ===
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }
}
