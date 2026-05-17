<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockIn;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockInService
{
    /**
     * Generate a unique batch number
     */
    private function generateBatchNumber(): string
    {
        $date = now()->format('Ymd');

        // Get the last batch number for today
        $lastBatch = StockIn::whereDate('created_at', today())
            ->where('batch_no', 'like', "BATCH-$date-%")
            ->orderBy('batch_no', 'desc')
            ->value('batch_no');

        // Extract last counter
        if ($lastBatch) {
            $lastCounter = (int) Str::afterLast($lastBatch, '-');
            $newCounter = str_pad($lastCounter + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newCounter = '001';
        }

        return "BATCH-$date-$newCounter";
    }
    /**
     * Generate a unique batch number
     */
    private function generateLotNumber(): string
    {
        $date = now()->format('Ymd');

        // Get the last batch number for today
        $lastBatch = StockIn::whereDate('created_at', today())
            ->where('lot_number', 'like', "LOT-$date-%")
            ->orderBy('lot_number', 'desc')
            ->value('lot_number');

        // Extract last counter
        if ($lastBatch) {
            $lastCounter = (int) Str::afterLast($lastBatch, '-');
            $newCounter = str_pad($lastCounter + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newCounter = '001';
        }

        return "LOT-$date-$newCounter";
    }

    /**
     * Receive stock into inventory
     */
    public function receiveStock(array $data, $receivedById = null)
    {
        return DB::transaction(function () use ($data, $receivedById) {

            $product = Product::findOrFail($data['product_id']);

            /** ------------------------------------------------
             *  AUTO-GENERATE BATCH NUMBER IF NOT PROVIDED
             *  Format: BATCH-YYYYMMDD-###
             * ------------------------------------------------ */
            $batchNo = $data['batch_no'] ?? $this->generateBatchNumber();
            $lotNumber = $data['lot_number'] ?? $this->generateLotNumber();

            // 1. Create StockIn record (audit)
            $stockIn = StockIn::create([
                'product_id'      => $product->id,
                'quantity'        => $data['quantity'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'expiration_date' => $data['expiration_date'] ?? null,
                'batch_no'        => $batchNo,
                'lot_number'        => $lotNumber,
                'user_id'         => $receivedById ?? auth()->id(),
            ]);

            // 2. Increase inventory (same batch)
            Inventory::updateOrCreate(
                [
                    'product_id'      => $product->id,
                    'batch_no'        => $batchNo,
                    'expiration_date' => $data['expiration_date'] ?? null,
                ],
                [
                    'quantity' => DB::raw("quantity + {$data['quantity']}"),
                ]
            );

            // 3. Recalculate EOQ
            $this->recalculateEOQ($product);

            $stockIn->update([
                'eoq_recalculated' => true
            ]);

            return $stockIn->load('product', 'user');
        });
    }

    /**
     * Recalculate EOQ using latest sales velocity (last 90 days)
     */
    private function recalculateEOQ(Product $product): void
    {
        // Annual Demand (D) = average daily sales × 365
        $dailySales = $product->transactionItems()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->where('transactions.created_at', '>=', now()->subDays(90))
            ->sum('transaction_items.quantity') / 90;

        $annualDemand = $dailySales * 365;

        $orderingCost = $product->ordering_cost ?: 1;   // S
        $holdingCost  = $product->holding_cost ?: 1;    // H

        if ($annualDemand <= 0 || $orderingCost <= 0 || $holdingCost <= 0) {
            return; // avoid division by zero
        }

        // EOQ = √(2DS / H)
        $eoq = sqrt((2 * $annualDemand * $orderingCost) / $holdingCost);

        $product->update([
            'eoq' => round($eoq), // add eoq column or use computed
        ]);
    }

    public function getStockInHistory(array $filters)
    {
        return StockIn::with(['product', 'user'])
            ->when($filters['product_id'] ?? null, fn($q, $id) => $q->where('product_id', $id))
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->orderBy('created_at', 'desc');
    }
}
