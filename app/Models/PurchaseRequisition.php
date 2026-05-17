<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    protected $fillable = [
        'supplier_id',
        'requested_by',
        'approved_by',
        'approved_at',
        'status',
        'notes',
        'supplier_response',
    ];

    // === SCOPES ===
    public function scopeSearch($query, $term)
    {
        return $query->where('notes', 'LIKE', "%{$term}%")
            ->orWhere('supplier_response', 'LIKE', "%{$term}%");
    }

    public function scopeFilterByStatus($query, $status)
    {
        return $query->when($status, fn($q) => $q->where('status', $status));
    }

    public function scopeFilterBySupplier($query, $supplierId)
    {
        return $query->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId));
    }

    // === RELATIONSHIPS ===
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }
}
