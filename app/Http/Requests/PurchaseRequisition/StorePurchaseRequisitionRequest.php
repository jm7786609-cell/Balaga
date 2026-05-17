<?php

namespace App\Http\Requests\PurchaseRequisition;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id'   => 'required|exists:suppliers,id',
            'requested_by'  => 'required|exists:users,id',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.requested_quantity' => 'required|integer|min:1',
            'items.*.suggested_price' => 'nullable|numeric|min:0',
        ];
    }
}
