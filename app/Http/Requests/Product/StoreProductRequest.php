<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_code'     => 'required|string|max:50|unique:products,product_code',
            'product_name'     => 'required|string|max:100',
            'product_brand'    => 'required|string|max:100',
            'generic_name'    => 'required|string|max:100',
            'description'      => 'nullable|string',
            'category_id'      => 'nullable|exists:categories,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'unit'             => 'required|string|max:20',
            'price'            => 'required|numeric|min:0',
            'ordering_cost'    => 'nullable|numeric|min:0',
            'holding_cost'     => 'nullable|numeric|min:0',
            'lead_time_days'   => 'nullable|integer|min:0',
            'safety_stock'     => 'nullable|integer|min:0',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
