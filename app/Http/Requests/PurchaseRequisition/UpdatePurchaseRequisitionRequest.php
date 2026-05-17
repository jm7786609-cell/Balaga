<?php

namespace App\Http\Requests\PurchaseRequisition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id'               => 'sometimes|required|exists:suppliers,id',
            'approved_by'               => 'nullable|exists:users,id',
            'approved_at'               => 'nullable|date',
            'status'                    => ['sometimes', Rule::in(['pending', 'approved', 'rejected', 'cancelled'])],
            'notes'                     => 'nullable|string',
            'supplier_response'         => 'nullable|string',
            'items'                     => 'sometimes|array',
            'items.*.id'                => 'required_with:items.*|exists:purchase_requisition_items,id',
            'items.*.product_id'        => 'sometimes|exists:products,id',
            'items.*.requested_quantity' => 'sometimes|integer|min:1',
            'items.*.approved_quantity' => 'nullable|integer|min:0',
            'items.*.suggested_price'   => 'nullable|numeric|min:0',
        ];
    }
}
