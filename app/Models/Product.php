<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'product_code',
        'product_name',
        'product_brand',
        'generic_name',
        'description',
        'category_id',
        'supplier_id',
        'unit',
        'price',
        'ordering_cost',
        'holding_cost',
        'lead_time_days',
        'safety_stock'
    ];

    protected $casts = [
        'ordering_cost' => 'decimal:2',
        'holding_cost'  => 'decimal:2',
    ];

    // ---------------------------------------------------
    // SPATIE MEDIA LIBRARY SETUP
    // ---------------------------------------------------

    /**
     * Register media collections for Product
     */
    public function registerMediaCollections(): void
    {
        // Existing product images
        $this
            ->addMediaCollection('product_images')
            ->useFallbackUrl('/dist/img/photo1.png')
            ->useFallbackPath(public_path('dist/img/photo1.png'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();

        // New: QR Code collection (single file)
        $this->addMediaCollection('qr_codes')
            ->singleFile() // Only one QR code per product
            ->acceptsMimeTypes(['image/png']);
    }

    /**
     * Register conversions (thumbnails, previews, etc.)
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // Existing conversions...
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(800)
            ->nonQueued();

        // QR code thumbnail
        $this->addMediaConversion('qr_thumb')
            ->width(200)
            ->height(200)
            ->nonQueued();
    }

    // ---------------------------------------------------
    // SCOPES
    // ---------------------------------------------------

    public function scopeLowStock($query, $threshold = null)
    {
        $threshold = $threshold ?? $this->safety_stock;
        return $query->whereRaw('(
            SELECT COALESCE(SUM(quantity), 0)
            FROM inventories
            WHERE inventories.product_id = products.id
            AND (expiration_date IS NULL OR expiration_date > CURDATE())
        ) <= ?', [$threshold]);
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('(
            SELECT COALESCE(SUM(quantity), 0)
            FROM inventories
            WHERE inventories.product_id = products.id
            AND (expiration_date IS NULL OR expiration_date > CURDATE())
        ) <= 0');
    }

    public function scopeExpired($query)
    {
        return $query->whereHas('inventories', fn($q) => $q->where('expiration_date', '<', now()));
    }

    public function scopeNearExpiry($query, $days = 30)
    {
        return $query->whereHas('inventories', function ($q) use ($days) {
            $q->whereBetween('expiration_date', [now(), now()->addDays($days)]);
        });
    }

    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('
            (SELECT COALESCE(SUM(quantity),0) FROM inventories WHERE product_id = products.id)
            <= (products.safety_stock + products.lead_time_days *
               (SELECT COALESCE(AVG(daily_sales),0) FROM (
                   SELECT SUM(ti.quantity) / 90 as daily_sales
                   FROM transaction_items ti
                   JOIN transactions t ON ti.transaction_id = t.id
                   WHERE ti.product_id = products.id
                   AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
               ) as sales)
            )
        ');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('product_name', 'LIKE', "%{$term}%")
            ->orWhere('product_code', 'LIKE', "%{$term}%")
            ->orWhere('product_brand', 'LIKE', "%{$term}%");
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // ---------------------------------------------------
    // RELATIONSHIPS
    // ---------------------------------------------------

    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withDefault();
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // ---------------------------------------------------
    // ACCESSORS
    // ---------------------------------------------------

    public function getCurrentStockAttribute()
    {
        return $this->inventories()->where(function ($q) {
            $q->whereNull('expiration_date')
                ->orWhere('expiration_date', '>', now());
        })->sum('quantity');
    }

    public function getReorderPointAttribute()
    {
        return $this->safety_stock + ($this->lead_time_days ?? 0);
    }
}
