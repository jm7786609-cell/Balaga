<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService, Request $request)
    {
        parent::__construct($request);
        $this->productService = $productService;

        // $this->middleware('role:admin')->except(['index', 'show']);
    }

    public function index(Request $request): ProductCollection
    {
        $validated = $request->validate([
            'search'        => 'nullable|string|max:255',
            'category_id'    => 'nullable|exists:categories,id',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'low_stock'     => 'nullable|in:1,true',
            'out_of_stock'  => 'nullable|in:1,true',
            'near_expiry'   => 'nullable|in:1,true',
            'sort'          => 'nullable|in:product_name,product_code,product_brand,current_stock,created_at',
            'order'         => 'nullable|in:asc,desc',
            'limit'         => 'nullable|integer|min:1|max:100',
            'page'          => 'nullable|integer|min:1',
        ]);

        $query = $this->productService->getAllProducts($validated);
        $products = $query->paginate($this->limit ?? 20);

        return new ProductCollection($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'supplier', 'inventories']);
        return $this->success(new ProductResource($product));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());
        return $this->success(new ProductResource($product), 'Product created successfully', 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->update($product, $request->validated());
        return $this->success(new ProductResource($product), 'Product updated successfully');
    }

    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->productService->delete($product);
            return $this->success(null, 'Product deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $q = $request->query('q', '');

        $products = Product::query()
            ->with('stockIns')
            ->where('product_code', 'like', "%{$q}%")
            ->orWhere('product_name', 'like', "%{$q}%")
            ->select('id', 'product_code', 'product_name', 'price')
            ->addSelect([
                'current_stock' => function ($query) {
                    $query->selectRaw('SUM(quantity)')
                        ->from('stock_ins')
                        ->whereColumn('product_id', 'products.id');
                },
                'recent_sales' => function ($query) {
                    $query->selectRaw('SUM(quantity)')
                        ->from('transaction_items as ti')
                        ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
                        ->whereColumn('ti.product_id', 'products.id')
                        ->where('t.created_at', '>=', now()->subDays(90));
                },
            ])
            ->orderBy('product_name')
            ->get();

        return $this->success($products);
    }
}
