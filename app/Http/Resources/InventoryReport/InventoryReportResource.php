<?php

namespace App\Http\Resources\InventoryReport;

use App\Http\Resources\Resource;

class InventoryReportResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'report_date' => $this->report_date->format('Y-m-d'),  // Raw for filtering: '2025-12-22'
            'display_date' => $this->report_date->format('M d, Y'), // Human readable: 'Dec 22, 2025'
            'product' => [
                'id' => $this->product->id,
                'product_code' => $this->product->product_code,
                'product_name' => $this->product->product_name,
                'product_brand' => $this->product->product_brand,
                'unit' => $this->product->unit,
            ],
            'beginning_inventory' => $this->beginning_inventory,
            'ending_inventory' => $this->ending_inventory,
            'physical_count' => $this->physical_count,
            'variance' => $this->variance,
            'remarks' => $this->remarks,
            'created_by' => $this->whenLoaded('creator', fn() => $this->creator?->first_name . ' ' . $this->creator?->last_name),
            'created_at' => $this->created_at->format('M d, Y h:i A'),
        ];
    }
}
