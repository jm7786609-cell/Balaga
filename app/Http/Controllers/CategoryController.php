<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService, Request $request)
    {
        parent::__construct($request);
        $this->categoryService = $categoryService;

        // $this->middleware('role:admin')->except(['index', 'show']);
    }

    public function index(Request $request): CategoryCollection
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'sort'   => 'nullable|in:category_name,created_at,updated_at',
            'order'  => 'nullable|in:asc,desc',
            'limit'  => 'nullable|integer|min:1|max:100',
            'page'   => 'nullable|integer|min:1',
        ]);

        $query = $this->categoryService->getAllCategories($validated);
        $categories = $query->paginate($this->limit ?? 15);

        return new CategoryCollection($categories);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products');
        return $this->success(new CategoryResource($category));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());
        return $this->success(new CategoryResource($category), 'Category created successfully', 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->categoryService->update($category, $request->validated());
        return $this->success(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy(Category $category): JsonResponse
    {
        try {
            $this->categoryService->delete($category);
            return $this->success(null, 'Category deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
