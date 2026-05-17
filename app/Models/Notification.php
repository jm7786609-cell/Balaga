<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['type', 'message', 'product_id', 'is_read'];
    protected $casts = ['is_read' => 'boolean'];

    // SCOPES
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeLowStock($query)
    {
        return $query->where('type', 'low_stock');
    }

    public function scopeExpiration($query)
    {
        return $query->where('type', 'expiration');
    }

    public function scopeReorder($query)
    {
        return $query->where('type', 'reorder');
    }

    // RELATIONSHIPS
    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }
}
