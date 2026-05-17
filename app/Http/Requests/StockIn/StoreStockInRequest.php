<?php

namespace App\Http\Requests\StockIn;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'      => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1',
            'delivery_date'  => 'required|date',
            'expiration_date'  => 'nullable|date|after_or_equal:today',
            'lot_number'         => 'nullable|string|max:50',
            'batch_no'         => 'nullable|string|max:50',
            'received_by'      => 'nullable|exists:users,id',
        ];
    }
}