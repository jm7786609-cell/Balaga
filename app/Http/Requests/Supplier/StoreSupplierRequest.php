<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('suppliers', 'supplier_name'),
            ],
            'first_name'  => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name'   => 'required|string|max:50',
            'phone_number' => [
                'required',
                'string',
                'max:15',
                'regex:/^[\d\+\-\s\(\)]+$/',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email'),
                Rule::unique('users', 'email'),
            ],
            'password' => 'required|string|min:8|confirmed',
            'address' => 'required|string',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
