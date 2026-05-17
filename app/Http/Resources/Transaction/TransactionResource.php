<?php

namespace App\Http\Resources\Transaction;

use App\Http\Resources\Resource;

class TransactionResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'receipt_no'    => 'REC' . str_pad($this->id, 8, '0', STR_PAD_LEFT),
            'cashier'       => [
                'id'   => $this->cashier->id,
                'name' => $this->cashier->full_name ?? 'Unknown',
            ],
            'items'         => TransactionItemResource::collection($this->whenLoaded('items')),
            'total_amount'  => number_format($this->total_amount, 2),
            'amount_paid'   => number_format($this->amount_paid, 2),
            'change_due'    => number_format($this->change_due, 2),
            'status'        => $this->status,
            'discount_type'        => $this->discount_type,
            'discount_amount'        => number_format($this->discount_amount, 2),
            'items_count'   => $this->items()->count(),
            'created_at'    => $this->created_at->format('M d, Y h:i A'),
            'completed_at'  => $this->status === 'completed' ? $this->updated_at->format('M d, Y h:i A') : null,
        ];
    }
}
