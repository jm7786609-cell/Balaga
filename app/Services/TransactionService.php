<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockIn;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function getPendingCart(int $cashierId): Transaction
    {
        return Transaction::firstOrCreate(
            ['cashier_id' => $cashierId, 'status' => 'pending'],
            ['total_amount' => 0, 'amount_paid' => 0, 'change_due' => 0]
        );
    }

    public function addToCart(int $cashierId, array $data): Transaction
    {
        return DB::transaction(function () use ($cashierId, $data) {
            $cart = $this->getPendingCart($cashierId);
            $product = Product::findOrFail($data['product_id']);
            $quantityToAdd = (int) $data['quantity'];
            $batchStockIns = StockIn::where('product_id', $product->id)
                ->available()
                ->orderBy('expiration_date')
                ->orderBy('created_at')
                ->get();

            if ($batchStockIns->isEmpty()) {
                throw ValidationException::withMessages(['product' => 'Product is out of stock.']);
            }

            if ($quantityToAdd <= 0) {
                throw ValidationException::withMessages(['quantity' => 'Quantity must be at least 1.']);
            }

            if ($quantityToAdd > $batchStockIns->sum('quantity')) {
                $totalAvailable = $batchStockIns->sum('quantity');
                throw ValidationException::withMessages([
                    'quantity' => "Only {$totalAvailable} units of {$product->product_name} are available."
                ]);
            }

            // Find existing item or create new
            $item = TransactionItem::where([
                'transaction_id' => $cart->id,
                'product_id'     => $product->id,
            ])->first();

            if ($item) {
                // Item exists → just increase quantity
                $item->increment('quantity', $quantityToAdd);
                $item->subtotal = $item->quantity * $product->price;
                $item->save();
            } else {
                // New item
                TransactionItem::create([
                    'transaction_id' => $cart->id,
                    'product_id'     => $product->id,
                    'quantity'       => $quantityToAdd,
                    'price'          => $product->price,
                    'subtotal'       => $quantityToAdd * $product->price,
                ]);
            }

            // Recalculate total
            $cart->total_amount = $cart->items()->sum(DB::raw('quantity * price'));
            $cart->save();

            return $cart->load('items.product');
        });
    }

    public function removeFromCart(Transaction $cart, int $productId): ?Transaction
    {
        $cart->items()->where('product_id', $productId)->delete();

        $cart->total_amount = $cart->items()->sum('subtotal');
        $cart->save();

        if ($cart->items()->count() === 0) {
            $cart->delete();
            return null;
        }

        return $cart->load('items.product');
    }

    // public function checkout(Transaction $transaction, float $amountPaid): Transaction
    // {
    //     if ($transaction->status !== 'pending') {
    //         throw ValidationException::withMessages(['transaction' => 'No active cart to checkout.']);
    //     }

    //     return DB::transaction(function () use ($transaction, $amountPaid) {
    //         $total = $transaction->total_amount;

    //         if ($amountPaid < $total) {
    //             throw ValidationException::withMessages([
    //                 'amount_paid' => "Insufficient payment. Required: ₱" . number_format($total, 2)
    //             ]);
    //         }

    //         // Deduct stock using FIFO
    //         foreach ($transaction->items as $item) {
    //             $remaining = $item->quantity;
    //             $batches = Inventory::where('product_id', $item->product_id)
    //                 ->available()
    //                 ->orderBy('expiration_date')
    //                 ->orderBy('created_at')
    //                 ->get();

    //             foreach ($batches as $batch) {
    //                 if ($remaining <= 0) break;
    //                 $deduct = min($remaining, $batch->quantity);
    //                 $batch->decrement('quantity', $deduct);
    //                 if ($batch->quantity <= 0) $batch->delete();
    //                 $remaining -= $deduct;
    //             }

    //             if ($remaining > 0) {
    //                 throw ValidationException::withMessages([
    //                     'stock' => "Not enough stock for {$item->product->product_name}"
    //                 ]);
    //             }
    //         }

    //         // Finalize transaction
    //         $transaction->update([
    //             'amount_paid' => $amountPaid,
    //             'change_due'  => $amountPaid - $total,
    //             'status'      => 'completed',
    //         ]);

    //         // Update all items to completed
    //         $transaction->items()->update(['status' => 'completed']);

    //         return $transaction->load('items.product', 'cashier');
    //     });
    // }
    // public function checkout(Transaction $transaction, float $amountPaid): Transaction
    // {
    //     if ($transaction->status !== 'pending') {
    //         throw ValidationException::withMessages(['transaction' => 'No active cart to checkout.']);
    //     }

    //     return DB::transaction(function () use ($transaction, $amountPaid) {
    //         $total = $transaction->total_amount;

    //         if ($amountPaid < $total) {
    //             throw ValidationException::withMessages([
    //                 'amount_paid' => "Insufficient payment. Required: ₱" . number_format($total, 2)
    //             ]);
    //         }

    //         // Deduct stock using FIFO
    //         foreach ($transaction->items as $item) {
    //             $remaining = $item->quantity;
    //             $batches = Inventory::where('product_id', $item->product_id)
    //                 ->available()
    //                 ->orderBy('expiration_date')
    //                 ->orderBy('created_at')
    //                 ->get();

    //             foreach ($batches as $batch) {
    //                 if ($remaining <= 0) break;
    //                 $deduct = min($remaining, $batch->quantity);
    //                 $batch->decrement('quantity', $deduct);
    //                 if ($batch->quantity <= 0) $batch->delete();
    //                 $remaining -= $deduct;
    //             }

    //             if ($remaining > 0) {
    //                 throw ValidationException::withMessages([
    //                     'stock' => "Not enough stock for {$item->product->product_name}"
    //                 ]);
    //             }
    //         }

    //         // Finalize this transaction
    //         $transaction->update([
    //             'amount_paid' => $amountPaid,
    //             'change_due'  => $amountPaid - $total,
    //             'status'      => 'completed',
    //         ]);

    //         $transaction->items()->update(['status' => 'completed']);

    //         // CRITICAL FIX: Delete any old pending cart for this cashier
    //         // So next sale can create a fresh pending cart
    //         Transaction::where('cashier_id', $transaction->cashier_id)
    //             ->where('status', 'pending')
    //             ->where('id', '!=', $transaction->id) // safety
    //             ->delete();

    //         return $transaction->load('items.product', 'cashier');
    //     });
    // }
    public function checkout(Transaction $transaction, float $amountPaid, string $discountType = 'none'): Transaction
    {
        if ($transaction->status !== 'pending') {
            throw ValidationException::withMessages([
                'transaction' => 'No active cart to checkout.'
            ]);
        }

        return DB::transaction(function () use ($transaction, $amountPaid, $discountType) {

            $total = $transaction->total_amount;

            // Determine discount rate
            $discountRate = match ($discountType) {
                'pwd', 'senior' => 0.20,
                default => 0
            };

            $discountAmount = $total * $discountRate;
            $finalTotal = $total - $discountAmount;

            // Validate payment
            if ($amountPaid < $finalTotal) {
                throw ValidationException::withMessages([
                    'amount_paid' => "Insufficient payment. Required: ₱" . number_format($finalTotal, 2)
                ]);
            }

            // Deduct stock using FIFO
            foreach ($transaction->items as $item) {
                $remaining = $item->quantity;

                $batches = Inventory::where('product_id', $item->product_id)
                    ->available()
                    ->orderBy('expiration_date')
                    ->orderBy('created_at')
                    ->get();

                foreach ($batches as $batch) {
                    if ($remaining <= 0) break;

                    $batchStockIns = StockIn::where('batch_no', $batch['batch_no'])->first();

                    $deduct = min($remaining, $batch->quantity);
                    $batch->decrement('quantity', $deduct);
                    $batchStockIns->decrement('quantity', $deduct);

                    if ($batch->quantity <= 0) {
                        // $batch->delete();
                    }

                    $remaining -= $deduct;
                }

                if ($remaining > 0) {
                    throw ValidationException::withMessages([
                        'stock' => "Not enough stock for {$item->product->product_name}"
                    ]);
                }
            }

            // Finalize transaction
            $transaction->update([
                'discount_type' => $discountType,
                'discount_amount' => $discountAmount,
                'amount_paid' => $amountPaid,
                'change_due' => $amountPaid - $finalTotal,
                'status' => 'completed',
            ]);

            $transaction->items()->update(['status' => 'completed']);

            Transaction::where('cashier_id', $transaction->cashier_id)
                ->where('status', 'pending')
                ->where('id', '!=', $transaction->id)
                ->delete();

            return $transaction->load('items.product', 'cashier');
        });
    }

    public function cancelCart(int $cashierId): bool
    {
        return Transaction::where('cashier_id', $cashierId)
            ->where('status', 'pending')
            ->delete() > 0;
    }
}
