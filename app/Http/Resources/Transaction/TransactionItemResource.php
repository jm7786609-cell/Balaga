<?php

namespace App\Http\Resources\Transaction;

use App\Http\Resources\Resource;

class TransactionItemResource extends Resource
{
    public function toArray($request): array
    {
        $product = $this->product;
        $media = $product?->getFirstMedia('product_images');

        return [
            'id'         => $this->id,
            'product'    => [
                'id'           => $product?->id,
                'code'         => $product?->product_code,
                'name'         => $product?->product_name,
                'brand'        => $product?->product_brand,
                'thumbnail_url' => $media?->getUrl('thumb'), // Fixed syntax
            ],
            'quantity'   => $this->quantity,
            'price'      => number_format($this->price, 2),
            'subtotal'   => number_format($this->subtotal, 2),
            'status'     => $this->status,
        ];
    }
}
