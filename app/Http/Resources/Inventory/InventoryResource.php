<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Resource;

class InventoryResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'product'         => [
                'id'          => $this->product->id,
                'product_code' => $this->product->product_code,
                'product_name' => $this->product->product_name,
                'brand'       => $this->product->product_brand,
                'unit'        => $this->product->unit,
            ],
            'quantity'        => (int) $this->quantity,
            'batch_no'        => $this->batch_no,
            'expiration_date' => $this->expiration_date?->format('M d, Y'),
            'days_until_expiry' => $this->expiration_date
                ? $this->expiration_date->diffInDays(now(), false)
                : null,
            'is_expired'      => $this->expiration_date && $this->expiration_date->isPast(),
            'is_near_expiry'  => $this->expiration_date && $this->expiration_date->diffInDays(now()) <= 30,
            'created_at'      => $this->created_at->format('M d, Y h:i A'),
            'updated_at'      => $this->updated_at->format('M d, Y h:i A'),
        ];
    }
}
