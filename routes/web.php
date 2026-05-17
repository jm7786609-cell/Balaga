<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\Navigation\AdminNavigationController;
use App\Http\Controllers\Navigation\CashierNavigationController;
use App\Http\Controllers\Navigation\PharmacistNavigationController;
use App\Http\Controllers\Navigation\SupplierNavigation;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return view('welcome');
})->name('loginPage')->middleware('guest');

Route::prefix('admin')->middleware('checkRole')->group(function () {
    // Dashboard Route
    Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    // User Management Routes
    Route::get('userManagement', [AdminNavigationController::class, 'userManagement'])->name('admin.userManagement');
    // Categories Management Routes
    Route::get('categoriesManagement', [AdminNavigationController::class, 'categoriesManagement'])->name('admin.categoriesManagement');
    // Suppliers Management Routes
    Route::get('suppliersManagement', [AdminNavigationController::class, 'suppliersManagement'])->name('admin.suppliersManagement');
    // Products Management Routes
    Route::get('productsManagement', [AdminNavigationController::class, 'productsManagement'])->name('admin.productsManagement');
    // Inventory Management Routes
    Route::get('inventoryManagement', [AdminNavigationController::class, 'inventoryManagement'])->name('admin.inventoryManagement');
    // Stock In Management Routes
    Route::get('stockInManagement', [AdminNavigationController::class, 'stockInManagement'])->name('admin.stockInManagement');
    // Requisition Management Routes
    Route::get('requisitionManagement', [AdminNavigationController::class, 'requisitionManagement'])->name('admin.requisitionManagement');
    // POS Management Routes
    Route::get('posManagement', [AdminNavigationController::class, 'posManagement'])->name('admin.posManagement');
    // Transaction History Routes
    Route::get('transactionHistory', [AdminNavigationController::class, 'transactionHistory'])->name('admin.transactionHistory');
    // Reports Routes
    Route::get('salesReport', [AdminNavigationController::class, 'salesReport'])->name('admin.salesReport');
    Route::get('expiryReport', [AdminNavigationController::class, 'expiryReport'])->name('admin.expiryReport');
    Route::get('lowStockReport', [AdminNavigationController::class, 'lowStockReport'])->name('admin.lowStockReport');
    Route::get('topSellersReport', [AdminNavigationController::class, 'topSellersReport'])->name('admin.topSellersReport');
    Route::get('deadStockReport', [AdminNavigationController::class, 'deadStockReport'])->name('admin.deadStockReport');
});

Route::prefix('cashier')->middleware('checkRole')->group(function () {
    // Dashboard Route
    Route::get('dashboard', [DashboardController::class, 'index'])->name('cashier.dashboard');
    // User Management Routes
    Route::get('inventoryManagement', [CashierNavigationController::class, 'inventoryManagement'])->name('cashier.inventoryManagement');
    // Stock In Management Routes
    Route::get('posManagement', [CashierNavigationController::class, 'posManagement'])->name('cashier.posManagement');
    // Transaction History Routes
    Route::get('transactionHistory', [CashierNavigationController::class, 'transactionHistory'])->name('cashier.transactionHistory');
    // Reports Routes
    Route::get('salesReport', [CashierNavigationController::class, 'salesReport'])->name('cashier.salesReport');
});

Route::prefix('pharmacist')->middleware('checkRole')->group(function () {
    // Dashboard Route
    Route::get('dashboard', [DashboardController::class, 'index'])->name('pharmacist.dashboard');
    // Categories Management Routes
    Route::get('categoriesManagement', [PharmacistNavigationController::class, 'categoriesManagement'])->name('pharmacist.categoriesManagement');
    // Suppliers Management Routes
    Route::get('suppliersManagement', [PharmacistNavigationController::class, 'suppliersManagement'])->name('pharmacist.suppliersManagement');
    // Products Management Routes
    Route::get('productsManagement', [PharmacistNavigationController::class, 'productsManagement'])->name('pharmacist.productsManagement');
    // Inventory Management Routes
    Route::get('inventoryManagement', [PharmacistNavigationController::class, 'inventoryManagement'])->name('pharmacist.inventoryManagement');
    // Stock In Management Routes
    Route::get('stockInManagement', [PharmacistNavigationController::class, 'stockInManagement'])->name('pharmacist.stockInManagement');
    // Requisition Management Routes
    Route::get('requisitionManagement', [PharmacistNavigationController::class, 'requisitionManagement'])->name('pharmacist.requisitionManagement');
    // Reports Routes
    Route::get('expiryReport', [PharmacistNavigationController::class, 'expiryReport'])->name('pharmacist.expiryReport');
    Route::get('lowStockReport', [PharmacistNavigationController::class, 'lowStockReport'])->name('pharmacist.lowStockReport');
    Route::get('topSellersReport', [PharmacistNavigationController::class, 'topSellersReport'])->name('pharmacist.topSellersReport');
    Route::get('deadStockReport', [PharmacistNavigationController::class, 'deadStockReport'])->name('pharmacist.deadStockReport');
});

Route::prefix('supplier')->middleware('checkRole')->group(function () {
    // Dashboard Route
    Route::get('dashboard', [DashboardController::class, 'index'])->name('supplier.dashboard');
    // Requisition Management Routes
    Route::get('requisitionManagement', [SupplierNavigation::class, 'requisitionManagement'])->name('supplier.requisitionManagement');
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});

Route::prefix('suppliers')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::put('/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::get('/{supplier}/products', [SupplierController::class, 'getSupplierProducts'])->name('suppliers.products');
    Route::post('/{supplier}/returns', [SupplierController::class, 'processReturn'])->name('suppliers.returns');
    Route::get('/{supplier}/return-history', [SupplierController::class, 'getReturnHistory'])->name('suppliers.return-history');
});

Route::get('products/search', [ProductController::class, 'search']);

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::post('/', [ProductController::class, 'store'])->name('products.store');
    Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

Route::prefix('purchase-requisitions')->group(function () {
    Route::get('/', [PurchaseRequisitionController::class, 'index'])->name('purchaseRequisitions.index');
    Route::post('/', [PurchaseRequisitionController::class, 'store'])->name('purchaseRequisitions.store');
    Route::get('/{purchaseRequisition}', [PurchaseRequisitionController::class, 'show'])->name('purchaseRequisitions.show');
    Route::put('/{purchaseRequisition}', [PurchaseRequisitionController::class, 'update'])->name('purchaseRequisitions.update');
    Route::delete('/{purchaseRequisition}', [PurchaseRequisitionController::class, 'destroy'])->name('purchaseRequisitions.destroy');
});

Route::prefix('inventories')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('inventories.index');
    Route::get('/{inventory}', [InventoryController::class, 'show'])->name('inventories.show');
});

Route::prefix('inventory-reports')->group(function () {
    Route::get('/', [InventoryReportController::class, 'index'])->name('inventoryReports.index');
    Route::get('/create', [InventoryReportController::class, 'create'])->name('inventoryReports.create');
    Route::post('/', [InventoryReportController::class, 'store'])->name('inventoryReports.store');
    Route::get('/{inventoryReport}', [InventoryReportController::class, 'show'])->name('inventoryReports.show');
    Route::put('/{inventoryReport}', [InventoryReportController::class, 'update'])->name('inventoryReports.update');
    Route::delete('/{inventoryReport}', [InventoryReportController::class, 'destroy'])->name('inventoryReports.destroy');
});

Route::prefix('stock-ins')->group(function () {
    Route::get('/', [StockInController::class, 'index'])->name('stockIns.index');
    Route::post('/', [StockInController::class, 'store'])->name('stockIns.store');
});

// Updated web routes (add these to the existing routes.php or web.php)
Route::prefix('pos')->group(function () {
    Route::get('cart', [TransactionController::class, 'cart']);
    Route::post('cart/add', [TransactionController::class, 'addToCart']);
    Route::post('cart/add-by-barcode', [TransactionController::class, 'addByBarcode']);
    Route::delete('cart/remove/{productId}', [TransactionController::class, 'removeFromCart']);
    Route::post('checkout', [TransactionController::class, 'checkout']);
    Route::delete('cart/cancel', [TransactionController::class, 'cancelCart']);
    Route::post('cart/find-by-barcode', [TransactionController::class, 'findByBarcode']);
});

// Transaction History
Route::prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::get('/{transaction}', [TransactionController::class, 'show']);
});

Route::get('/reports/sales', [ReportController::class, 'sales'])
    ->name('reports.sales')
    ->middleware('auth');

Route::get('/reports/expiry', [ReportController::class, 'expiry'])
    ->name('reports.expiry')
    ->middleware('auth');

Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])
    ->name('reports.lowStock')
    ->middleware('auth');

Route::get('/reports/top-sellers', [ReportController::class, 'topSellers'])
    ->name('reports.topSellers')
    ->middleware('auth');

Route::get('/reports/dead-stock', [ReportController::class, 'deadStock'])
    ->name('reports.deadStock')
    ->middleware('auth');

Route::get('password/forgot', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('password/forgot', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
