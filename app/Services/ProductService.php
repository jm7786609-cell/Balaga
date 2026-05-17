<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class ProductService
{
    /**
     * Get filtered products query builder
     */
    public function getAllProducts(array $filters)
    {
        return Product::query()
            ->with(['category', 'supplier'])
            ->withCount(['inventories as expired_count' => fn($q) => $q->expired()])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhere('product_brand', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'] ?? null, fn($q, $id) => $q->where('category_id', $id))
            ->when($filters['supplier_id'] ?? null, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($filters['low_stock'] ?? null, fn($q) => $q->lowStock())
            ->when($filters['out_of_stock'] ?? null, fn($q) => $q->outOfStock())
            ->when($filters['near_expiry'] ?? null, fn($q) => $q->nearExpiry(30))
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $order = $filters['order'] ?? 'asc';
                if ($sort === 'current_stock') {
                    $q->orderByRaw('(SELECT COALESCE(SUM(quantity), 0) FROM inventories WHERE product_id = products.id AND (expiration_date IS NULL OR expiration_date > CURDATE())) ' . $order);
                } else {
                    $q->orderBy($sort, $order);
                }
            });
    }

    /**
     * Create product with image upload
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $image = $data['image'] ?? null;
            unset($data['image']);

            $product = Product::create($data);

            // Upload product image if provided
            if ($image && $image->isValid()) {
                $product->addMedia($image)
                    ->usingFileName($this->generateUniqueName($image, $product))
                    ->toMediaCollection('product_images');
            }

            // Generate and attach QR code
            $this->generateAndAttachQrCode($product);

            return $product->load(['media', 'category', 'supplier']);
        });
    }

    /**
     * Update product with optional image replace/delete
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $image       = $data['image'] ?? null;
            $removeImage = $data['remove_image'] ?? false;
            $oldCode     = $product->product_code;

            unset($data['image'], $data['remove_image']);

            $product->update($data);

            // Handle product image
            if ($removeImage) {
                $product->clearMediaCollection('product_images');
            }
            if ($image && $image->isValid()) {
                $product->clearMediaCollection('product_images');
                $product->addMedia($image)
                    ->usingFileName($this->generateUniqueName($image, $product))
                    ->toMediaCollection('product_images');
            }

            // Regenerate QR only if product_code changed
            if ($product->wasChanged('product_code') || $product->getMedia('qr_codes')->isEmpty()) {
                $product->clearMediaCollection('qr_codes');
                $this->generateAndAttachQrCode($product);
            }

            return $product->load(['media', 'category', 'supplier']);
        });
    }

    /**
     * Delete product safely
     */
    public function delete(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            if ($product->transactionItems()->exists()) {
                throw new \Exception('Cannot delete product that has been sold in transactions.');
            }

            // Clean up related data
            $product->inventories()->delete();
            $product->stockIns()->delete();
            $product->clearMediaCollection('product_images'); // Delete images

            return $product->delete();
        });
    }

    /**
     * Generate clean, unique filename
     */
    private function generateUniqueName($file, Product $product): string
    {
        $extension = $file->getClientOriginalExtension();
        $slug = Str::slug($product->product_name ?? 'product');
        $id = $product->id;

        return "{$slug}-{$id}-" . time() . ".{$extension}";
    }

    /**
     * Generate QR code and attach to 'qr_codes' media collection
     */
    private function generateAndAttachQrCode(Product $product): void
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $product->product_code,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 20,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            logoPath: public_path('dist/img/AdminLTELogo.png'),
            logoResizeToWidth: 70,
            logoPunchoutBackground: true,
            labelText: $product->product_code,
            labelFont: new OpenSans(20),
            labelAlignment: LabelAlignment::Center
        );

        $qrCodeResult = $builder->build();

        // Generate unique filename
        $fileName = "qr-{$product->product_code}-{$product->id}.png";

        // Attach directly from memory (no temp file needed!)
        $product->addMediaFromString($qrCodeResult->getString())
            ->usingName("QR Code - {$product->product_name}")
            ->usingFileName($fileName)
            ->toMediaCollection('qr_codes');
    }
}
