<?php

namespace App\Http\Resources\PurchaseRequisition;

use App\Http\Resources\Resource;

class PurchaseRequisitionResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'supplier'            => $this->supplier ? $this->supplier->supplier_name : null,
            'requested_by'        => $this->requestedBy ? $this->requestedBy->email : null,
            'approved_by'         => $this->approvedBy ? $this->approvedBy->email : null,
            'approved_at'         => $this->approved_at?->format('M d, Y H:i'),
            'status'              => $this->status,
            'notes'               => $this->notes,
            'supplier_response'   => $this->supplier_response,
            'items'               => PurchaseRequisitionItemResource::collection($this->whenLoaded('items')),
            'created_at'          => $this->created_at->format('M d, Y'),
            'updated_at'          => $this->updated_at->format('M d, Y'),
        ];
    }
}
