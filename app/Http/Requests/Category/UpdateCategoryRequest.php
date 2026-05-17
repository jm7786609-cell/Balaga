<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_name' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('categories', 'category_name')->ignore($this->category->id),
            ],
            'description' => 'nullable|string',
        ];
    }
}
