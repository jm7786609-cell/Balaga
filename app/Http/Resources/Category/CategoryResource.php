<?php

namespace App\Http\Resources\Category;

use App\Http\Resources\Resource;

class CategoryResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'category_name' => $this->category_name,
            'description'   => $this->description,
            'product_count' => $this->whenLoaded('products', fn() => $this->products->count()),
            'created_at'    => $this->created_at->format('M d, Y'),
            'updated_at'    => $this->updated_at->format('M d, Y'),
        ];
    }
}
