<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StoreInventoryRequest;
use App\Http\Requests\Inventory\UpdateInventoryRequest;
use App\Http\Resources\Inventory\InventoryCollection;
use App\Http\Resources\Inventory\InventoryResource;
use App\Models\Inventory;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService, Request $request)
    {
        parent::__construct($request);
        $this->inventoryService = $inventoryService;

        // $this->middleware('role:admin,pharmacist');
    }

    public function index(Request $request): InventoryCollection
    {
        $validated = $request->validate([
            'product_id'    => 'nullable|exists:products,id',
            'expired'       => 'nullable|in:1,true',
            'near_expiry'   => 'nullable|in:1,true',
            'low_stock'     => 'nullable|in:1,true',
            'search'        => 'nullable|string|max:255',
            'sort'         => 'nullable|in:product_name,quantity,expiration_date,created_at',
            'order'         => 'nullable|in:asc,desc',
            'limit'         => 'nullable|integer|min:1|max:100',
            'page'          => 'nullable|integer|min:1',
        ]);

        $query = $this->inventoryService->getAllInventory($validated);
        $inventory = $query->paginate($this->limit ?? 20);

        return new InventoryCollection($inventory);
    }

    public function show(Inventory $inventory): JsonResponse
    {
        return $this->success(new InventoryResource($inventory->load('product')));
    }

    public function store(StoreInventoryRequest $request): JsonResponse
    {
        $inventory = $this->inventoryService->addStock($request->validated(), auth()->id());
        return $this->success(new InventoryResource($inventory), 'Stock added successfully', 201);
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory): JsonResponse
    {
        $inventory = $this->inventoryService->updateStock($inventory, $request->validated());
        return $this->success(new InventoryResource($inventory), 'Stock updated successfully');
    }

    public function destroy(Inventory $inventory): JsonResponse
    {
        $inventory->delete();
        return $this->success(null, 'Inventory batch deleted');
    }
}
