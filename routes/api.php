<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::prefix('auth')->group(function () {
//     Route::post('/login', [AuthController::class, 'login']);

//     Route::middleware('auth:sanctum')->group(function () {
//         Route::post('/logout', [AuthController::class, 'logout']);
//         Route::get('/me', [AuthController::class, 'me']);
//     });
// });

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('auth:sanctum')->prefix('users')->group(function () {
//     Route::get('/', [UserController::class, 'index']);
//     Route::post('/', [UserController::class, 'store']);
//     Route::get('/{user}', [UserController::class, 'show']);
//     Route::put('/{user}', [UserController::class, 'update']);
//     Route::delete('/{user}', [UserController::class, 'destroy']);
// });

// Route::middleware('auth:sanctum')->get('/user/profile', [UserController::class, 'profile']);

// Route::middleware('auth:sanctum')->prefix('categories')->group(function () {
//     Route::get('/', [CategoryController::class, 'index']);
//     Route::get('/{category}', [CategoryController::class, 'show']);
//     Route::post('/', [CategoryController::class, 'store']);
//     Route::put('/{category}', [CategoryController::class, 'update']);
//     Route::delete('/{category}', [CategoryController::class, 'destroy']);
// });

// Route::middleware('auth:sanctum')->prefix('suppliers')->group(function () {
//     Route::get('/', [SupplierController::class, 'index']);
//     Route::get('/{supplier}', [SupplierController::class, 'show']);
//     Route::post('/', [SupplierController::class, 'store']);
//     Route::put('/{supplier}', [SupplierController::class, 'update']);
//     Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
// });

// Route::middleware('auth:sanctum')->prefix('products')->group(function () {
//     Route::get('/', [ProductController::class, 'index']);
//     Route::get('/{product}', [ProductController::class, 'show']);
//     Route::post('/', [ProductController::class, 'store']);
//     Route::put('/{product}', [ProductController::class, 'update']);
//     Route::delete('/{product}', [ProductController::class, 'destroy']);
// });

// Route::middleware('auth:sanctum')->prefix('inventories')->group(function () {
//     Route::get('/', [InventoryController::class, 'index']);
//     Route::get('/{inventory}', [InventoryController::class, 'show']);
//     Route::post('/', [InventoryController::class, 'store']);
//     Route::put('/{inventory}', [InventoryController::class, 'update']);
//     Route::delete('/{inventory}', [InventoryController::class, 'destroy']);
// });

// Route::middleware('auth:sanctum')->prefix('stock-in')->group(function () {
//     Route::get('/', [StockInController::class, 'index']);
//     Route::post('/', [StockInController::class, 'store']);
// });

// Route::middleware('auth:sanctum')->group(function () {

//     // POS System (Cashier only)
//     Route::prefix('pos')->group(function () {
//         Route::get('cart', [TransactionController::class, 'cart']);
//         Route::post('cart/add', [TransactionController::class, 'addToCart']);
//         Route::delete('cart/remove/{productId}', [TransactionController::class, 'removeFromCart']);
//         Route::post('checkout', [TransactionController::class, 'checkout']);
//         Route::delete('cart/cancel', [TransactionController::class, 'cancelCart']);
//     });

//     // Transaction History
//     Route::prefix('transactions')->group(function () {
//         Route::get('/', [TransactionController::class, 'index']);
//         Route::get('/{transaction}', [TransactionController::class, 'show']);
//     });
// });
