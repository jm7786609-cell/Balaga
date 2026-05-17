<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\Supplier\SupplierCollection;
use App\Http\Resources\Supplier\SupplierResource;
use App\Models\Supplier;
use App\Models\ProductReturn;
use App\Models\ProductReturnItem;
use App\Models\Inventory;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService, Request $request)
    {
        parent::__construct($request);
        $this->supplierService = $supplierService;

        // $this->middleware('role:admin')->except(['index', 'show']);
    }

    public function index(Request $request): SupplierCollection
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'sort'   => 'nullable|in:supplier_name,contact_person,email,created_at',
            'order'  => 'nullable|in:asc,desc',
            'limit'  => 'nullable|integer|min:1|max:100',
            'page'   => 'nullable|integer|min:1',
        ]);

        $query = $this->supplierService->getAllSuppliers($validated);
        $suppliers = $query->paginate($this->limit ?? 15);

        return new SupplierCollection($suppliers);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->loadCount('products');
        return $this->success(new SupplierResource($supplier));
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->supplierService->create($request->validated());
        return $this->success(new SupplierResource($supplier), 'Supplier created successfully', 201);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier = $this->supplierService->update($supplier, $request->validated());
        return $this->success(new SupplierResource($supplier), 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        try {
            $this->supplierService->delete($supplier);
            return $this->success(null, 'Supplier deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get products from a specific supplier for returns
     */
    public function getSupplierProducts(Supplier $supplier): JsonResponse
    {
        try {
            $products = $supplier->products()
                ->with('category')
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'product_code' => $product->product_code,
                        'product_name' => $product->product_name,
                        'price' => $product->price,
                        'unit' => $product->unit,
                        'category' => $product->category?->category_name ?? '—',
                    ];
                });

            return $this->success($products, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Process product return from supplier
     */
    public function processReturn(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.item_reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Create product return header
            $return = ProductReturn::create([
                'supplier_id' => $supplier->id,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            $totalAmount = 0;

            // Create return items and update inventory
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                // Create return item
                ProductReturnItem::create([
                    'product_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'reason' => $item['item_reason'] ?? null,
                ]);

                // Reduce inventory (negative adjustment)
                $inventory = Inventory::where('product_id', $item['product_id'])
                    ->latest()
                    ->first();

                if ($inventory) {
                    $inventory->quantity -= $item['quantity'];
                    $inventory->save();
                }
            }

            // Update total amount
            $return->update(['total_amount' => $totalAmount]);

            DB::commit();

            return $this->success(
                ['id' => $return->id],
                'Product return processed successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to process return: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Get return history for a supplier
     */
    public function getReturnHistory(Supplier $supplier): JsonResponse
    {
        try {
            $returns = $supplier->returns()
                ->with('items.product', 'createdBy')
                ->latest()
                ->get()
                ->map(function ($return) {
                    return [
                        'id' => $return->id,
                        'created_at' => $return->created_at->format('M d, Y H:i A'),
                        'status' => $return->status,
                        'total_amount' => number_format($return->total_amount, 2),
                        'item_count' => $return->items->count(),
                        'created_by' => $return->createdBy->full_name ?? '—',
                        'reason' => $return->reason,
                    ];
                });

            return $this->success($returns, 'Return history retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
