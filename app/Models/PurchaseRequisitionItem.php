<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id',
        'product_id',
        'requested_quantity',
        'approved_quantity',
        'suggested_price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
