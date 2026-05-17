<?php

namespace App\Services;

use App\Models\PurchaseRequisition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class PurchaseRequisitionService
{
    public function getAllRequisitions(array $filters): Builder
    {
        return PurchaseRequisition::query()
            ->with(['supplier', 'requestedBy', 'approvedBy', 'items'])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->search($search);
            })
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->filterByStatus($status);
            })
            ->when($filters['supplier_id'] ?? null, function ($q, $supplierId) {
                $q->filterBySupplier($supplierId);
            })
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $order = $filters['order'] ?? 'asc';
                $q->orderBy($sort, $order);
            });
    }

    public function create(array $data): PurchaseRequisition
    {
        $requisition = PurchaseRequisition::create($data);

        // Create items if provided
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $requisition->items()->create($item);
            }
        }

        return $requisition->refresh();
    }

    public function update(PurchaseRequisition $requisition, array $data): PurchaseRequisition
    {
        // Update main requisition fields
        $requisition->update(Arr::except($data, ['items']));

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                if (isset($itemData['id'])) {
                    $updateData = [];

                    if (isset($itemData['product_id'])) {
                        $updateData['product_id'] = $itemData['product_id'];
                    }
                    if (isset($itemData['requested_quantity'])) {
                        $updateData['requested_quantity'] = $itemData['requested_quantity'];
                    }
                    if (array_key_exists('suggested_price', $itemData)) {
                        $updateData['suggested_price'] = $itemData['suggested_price'] ?: null;
                    }

                    if (!empty($updateData)) {
                        $requisition->items()->where('id', $itemData['id'])->update($updateData);
                    }
                }
            }
        }

        return $requisition->fresh(['items', 'supplier', 'requestedBy']);
    }

    public function delete(PurchaseRequisition $requisition): bool
    {
        // Delete all associated items first (or let DB cascade if configured)
        $requisition->items()->delete();

        // Now delete the requisition itself
        return $requisition->delete();
    }
}
