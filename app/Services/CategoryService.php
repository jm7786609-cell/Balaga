<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class CategoryService
{
    public function getAllCategories(array $filters): Builder
    {
        return Category::query()
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('category_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $order = $filters['order'] ?? 'asc';
                $q->orderBy($sort, $order);
            });
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->refresh();
    }

    public function delete(Category $category): bool
    {
        // Optional: prevent deletion if category has products
        if ($category->products()->exists()) {
            throw new \Exception('Cannot delete category with existing products.');
        }

        return $category->delete();
    }
}
