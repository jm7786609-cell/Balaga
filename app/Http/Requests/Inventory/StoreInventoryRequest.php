<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'       => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1',
            'expiration_date'  => 'nullable|date|after:today',
            'batch_no'         => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'expiration_date.after' => 'Expiration date cannot be in the past.',
        ];
    }
}
