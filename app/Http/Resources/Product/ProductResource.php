<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Resource;

class ProductResource extends Resource
{
    public function toArray($request): array
    {
        // get first media
        $media = $this->getFirstMedia('product_images');
        $qrMedia = $this->getFirstMedia('qr_codes');

        return [
            'id'               => $this->id,
            'product_code'     => $this->product_code,
            'product_name'     => $this->product_name,
            'generic_name'     => $this->generic_name,
            'product_brand'    => $this->product_brand,
            'description'      => $this->description,
            'category'         => $this->whenLoaded('category', fn() => $this->category?->category_name),
            'supplier'         => $this->whenLoaded('supplier', fn() => $this->supplier?->supplier_name),
            'unit'             => $this->unit,
            'price'            => number_format($this->price, 2),
            'current_stock'    => (int) $this->current_stock,
            'is_low_stock'     => $this->current_stock <= $this->safety_stock,
            'needs_reorder'    => $this->current_stock <= $this->reorder_point,
            'has_expired_items' => $this->inventories()->expired()->exists(),
            'ordering_cost'    => number_format($this->ordering_cost, 2),
            'holding_cost'     => number_format($this->holding_cost, 2),
            'lead_time_days'   => $this->lead_time_days,
            'safety_stock'     => $this->safety_stock,
            'eoq'              => $this->eoq,

            // ---------------------------------------------------
            // MEDIA LIBRARY OUTPUT
            // ---------------------------------------------------

            'image_url'        => $media?->getUrl(),
            'thumbnail_url'    => $media?->getUrl('thumb'),
            'responsive_images' => $media?->responsive_images,
            'qr_code_url'      => $qrMedia?->getUrl(),
            'qr_code_thumb_url' => $qrMedia?->getUrl('qr_thumb'),

            'created_at'       => $this->created_at->format('M d, Y'),
            'updated_at'       => $this->updated_at->format('M d, Y'),
        ];
    }
}
