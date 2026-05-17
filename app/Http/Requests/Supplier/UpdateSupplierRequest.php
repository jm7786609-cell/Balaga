<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('suppliers', 'supplier_name')
                    ->ignore($this->supplier->id),
            ],
            'first_name'  => 'sometimes|required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name'   => 'sometimes|required|string|max:50',
            'phone_number' => [
                'sometimes',
                'string',
                'max:15',
                'regex:/^[\d\+\-\s\(\)]+$/',
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')
                    ->ignore($this->supplier->id),
                Rule::unique('users', 'email')
                    ->ignore($this->supplier->user_id),
            ],
            'address' => 'sometimes|string',
            'website' => 'nullable|url|max:255',
            'notes'   => 'nullable|string',
        ];
    }
}
