<?php

namespace App\Http\Resources\Supplier;

use App\Http\Resources\Resource;

class SupplierResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'supplier_name'  => $this->supplier_name,
            'user_id'        => $this->user_id,
            'user'    => [
                'id'           => $this->user->id,
                'first_name'   => $this->user->first_name,
                'middle_name'  => $this->user->middle_name,
                'last_name'    => $this->user->last_name,
                'email'        => $this->user->email,
                'full_name'    => $this->user->full_name,
            ],
            'phone_number'   => $this->phone_number,
            'email'          => $this->email,
            'address'        => $this->address,
            'website'        => $this->website,
            'notes'          => $this->notes,
            'product_count'  => $this->whenLoaded('products', fn() => $this->products->count()),
            'created_at'     => $this->created_at->format('M d, Y'),
            'updated_at'     => $this->updated_at->format('M d, Y'),
        ];
    }
}
