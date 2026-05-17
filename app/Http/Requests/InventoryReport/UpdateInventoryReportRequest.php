<?php

namespace App\Http\Requests\InventoryReport;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'admin' || $this->user()->role === 'pharmacist';
    }

    public function rules(): array
    {
        return [
            'beginning_inventory' => 'required|integer|min:0',
            'ending_inventory'    => 'required|integer|min:0',
            'remarks'             => 'nullable|string|max:65535',
        ];
    }
}
