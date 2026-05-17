<?php

namespace App\Http\Resources\StockIn;

use App\Http\Resources\Resource;

class StockInResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'product'         => [
                'id'          => $this->product->id,
                'product_code' => $this->product->product_code,
                'product_name' => $this->product->product_name,
                'generic_name' => $this->product->generic_name,
                'brand'       => $this->product->product_brand,
            ],
            'quantity'        => (int) $this->quantity,
            'lot_number'        => $this->lot_number,
            'batch_no'        => $this->batch_no,
            'delivery_date' => $this->delivery_date?->format('M d, Y'),
            'expiration_date' => $this->expiration_date?->format('M d, Y'),
            'received_by'     => $this->user ? [
                'id'   => $this->user->id,
                'name' => $this->user->full_name,
            ] : null,
            'received_at'     => $this->created_at->format('M d, Y h:i A'),
            'eoq_recalculated' => $this->eoq_recalculated ?? false, // we'll add this
        ];
    }
}