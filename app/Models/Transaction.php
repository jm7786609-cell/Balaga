<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'cashier_id',
        'total_amount',
        'amount_paid',
        'change_due',
        'status',
        'discount_type',
        'discount_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'change_due'   => 'decimal:2',
        'discount_amount'   => 'decimal:2',
    ];

    // === SCOPES ===
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByCashier($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // === RELATIONSHIPS ===
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // === HELPERS ===

    /**
     * Calculate the total amount from items
     */
    public function calculateTotal(): void
    {
        $this->total_amount = $this->items->sum(fn($item) => $item->subtotal);
    }

    /**
     * Calculate change due
     */
    public function calculateChange(): void
    {
        $this->change_due = max(0, $this->amount_paid - $this->total_amount);
    }
}
