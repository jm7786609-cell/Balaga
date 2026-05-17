<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity'        => 'sometimes|required|integer|min:0',
            'expiration_date' => 'nullable|date',
            'batch_no'        => 'nullable|string|max:50',
        ];
    }
}
