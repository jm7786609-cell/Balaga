<?php

namespace App\Http\Resources\PurchaseRequisition;

use App\Http\Resources\Resource;

class PurchaseRequisitionItemResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'product_name'        => $this->product ? $this->product->product_name : null,
            'product_id'          => $this->product_id,
            'requested_quantity'  => $this->requested_quantity,
            'approved_quantity'   => $this->approved_quantity,
            'suggested_price'     => $this->suggested_price,
            'created_at'          => $this->created_at->format('M d, Y'),
            'updated_at'          => $this->updated_at->format('M d, Y'),
        ];
    }
}
