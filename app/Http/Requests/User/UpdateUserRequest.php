<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|required|string|max:30',
            'middle_name' => 'nullable|string|max:30',
            'last_name' => 'sometimes|required|string|max:30',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => 'sometimes|required|in:admin,cashier,pharmacist,supplier',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->email) {
            $this->merge(['email' => strtolower($this->email)]);
        }
    }
}
