<?php

namespace App\Http\Requests\InventoryReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admin or pharmacist can create reports
        return $this->user()->role === 'admin' || $this->user()->role === 'pharmacist';
    }

    public function rules(): array
    {
        return [
            'report_date'          => 'required|date',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.beginning_inventory' => 'required|integer|min:0',
            'items.*.ending_inventory'    => 'required|integer|min:0',
            'items.*.remarks'      => 'nullable|string|max:65535',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item must be provided.',
        ];
    }
}
