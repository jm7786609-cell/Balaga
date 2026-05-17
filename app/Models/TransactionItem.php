<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
        'status',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Automatically calculate subtotal when creating/updating
    // protected static function booted()
    // {
    //     static::saving(function ($item) {
    //         $item->subtotal = $item->quantity * $item->price;
    //     });
    // }

    // === RELATIONSHIPS ===
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
