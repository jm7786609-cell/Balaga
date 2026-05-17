<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class InventoryService
{
    public function getAllInventory(array $filters): Builder
    {
        return Inventory::query()
            ->with('product')
            ->when($filters['product_id'] ?? null, fn($q, $id) => $q->where('product_id', $id))
            ->when($filters['expired'] ?? null, fn($q) => $q->expired())
            ->when($filters['near_expiry'] ?? null, fn($q) => $q->nearExpiry(30))
            ->when($filters['low_stock'] ?? null, fn($q) => $q->where('quantity', '<=', 10))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->whereHas('product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%");
                });
            })
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $order = $filters['order'] ?? 'desc';
                $q->orderBy($sort === 'product_name' ? 'product_id' : $sort, $order);
            });
    }

    public function addStock(array $data, $userId = null): Inventory
    {
        $inventory = Inventory::create($data);

        // Optional: Log stock in history
        // StockIn::create([...]);

        return $inventory->load('product');
    }

    public function updateStock(Inventory $inventory, array $data): Inventory
    {
        $inventory->update($data);
        return $inventory->load('product');
    }

    public function removeStock(Inventory $inventory, int $quantity): bool
    {
        if ($inventory->quantity < $quantity) {
            throw new \Exception('Insufficient stock in this batch.');
        }

        $inventory->quantity -= $quantity;
        $inventory->save();

        if ($inventory->quantity === 0) {
            $inventory->delete(); // optional: remove empty batch
        }

        return true;
    }
}
