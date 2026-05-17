<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'in:none,pwd,senior'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
