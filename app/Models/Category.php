<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['category_name', 'description'];

    // === SCOPES ===
    public function scopeWithProductCount($query)
    {
        return $query->withCount('products');
    }

    public function scopeHasProducts($query)
    {
        return $query->whereHas('products');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('category_name', 'LIKE', "%{$term}%")
            ->orWhere('description', 'LIKE', "%{$term}%");
    }

    // === RELATIONSHIPS ===
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
