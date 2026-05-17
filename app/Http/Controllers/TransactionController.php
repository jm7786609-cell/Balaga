<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\AddToCartRequest;
use App\Http\Requests\Transaction\CheckoutRequest;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Resources\Transaction\TransactionCollection;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService, Request $request)
    {
        parent::__construct($request);
        $this->transactionService = $transactionService;
    }

    // POS: Get current cart
    public function cart(): JsonResponse
    {
        $cart = $this->transactionService->getPendingCart(auth()->user()->id);
        return $this->success(new TransactionResource($cart->load('items.product')));
    }

    // POS: Add to cart
    public function addToCart(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->transactionService->addToCart(auth()->user()->id, $request->validated());
        return $this->success(new TransactionResource($cart), 'Item added to cart');
    }

    // POS: Remove item
    public function removeFromCart(int $productId): JsonResponse
    {
        $cart = Transaction::where('cashier_id', auth()->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $result = $this->transactionService->removeFromCart($cart, $productId);

        if (!$result) {
            return $this->success(null, 'Cart cleared');
        }

        return $this->success(new TransactionResource($result), 'Item removed');
    }

    // Find product by barcode (for scanner) - returns product details
    public function findByBarcode(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $product = Product::where('product_code', $request->barcode)
            ->select('id', 'product_name', 'price')
            ->first();

        if (!$product) {
            return $this->error('Product not found', 404);
        }

        return $this->success($product);
    }

    // POS: Checkout
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $cart = Transaction::where('cashier_id', auth()->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $transaction = $this->transactionService->checkout($cart, $request->amount_paid);

        return $this->success(
            new TransactionResource($transaction),
            'Checkout successful! Change: ₱' . number_format($transaction->change_due, 2)
        );
    }

    // POS: Cancel cart
    public function cancelCart(): JsonResponse
    {
        $this->transactionService->cancelCart(auth()->user()->id);
        return $this->success(null, 'Cart cancelled');
    }

    // List completed transactions (own for cashier, all for admin)
    public function index(Request $request): TransactionCollection
    {
        $query = Transaction::with(['cashier', 'items.product'])
            ->where('status', 'completed');

        if (auth()->user()->role !== 'admin') {
            $query->where('cashier_id', auth()->user()->id);
        }

        $query->when($request->date, fn($q, $d) => $q->whereDate('created_at', $d))
            ->when($request->from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest();

        return new TransactionCollection($query->paginate(20));
    }

    // Show single transaction (own or admin)
    public function show(Transaction $transaction): JsonResponse
    {
        if (auth()->user()->role !== 'admin' && $transaction->cashier_id !== auth()->user()->id) {
            return $this->error('Unauthorized', 403);
        }

        return $this->success(new TransactionResource($transaction->load('items.product', 'cashier')));
    }

    // Updated TransactionController.php (add this method to the existing class)
    public function addByBarcode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::where('product_code', $validated['barcode'])->first();

        if (!$product) {
            return $this->error('Product not found', 404);
        }

        $data = [
            'product_id' => $product->id,
            'quantity' => 1,
        ];

        $cart = $this->transactionService->addToCart(auth()->user()->id, $data);

        return $this->success(
            new TransactionResource($cart->load('items.product')),
            'Item added to cart'
        );
    }
}
