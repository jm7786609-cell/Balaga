<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Counts
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();
        $totalUsers = User::count();

        // Stock computations
        $outOfStock = Inventory::selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->having('qty', '<=', 0)
            ->count();

        $lowStock = Inventory::selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->having('qty', '<=', 10)
            ->count();

        // Sales
        $totalCompletedSales = Transaction::where('status', 'completed')->count();

        $salesToday = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');

        // Recent transactions
        $recentTransactions = Transaction::with('cashier')
            ->where('status', 'completed')
            ->latest()
            ->take(5)
            ->get();

        // Low stock product list
        $lowStockProducts = Inventory::join('products', 'products.id', '=', 'inventories.product_id')
            ->select('products.product_name')
            ->selectRaw('SUM(inventories.quantity) as qty')
            ->groupBy('products.id', 'products.product_name')
            ->having('qty', '<=', 10)
            ->orderBy('qty')
            ->get();

        // Get user permissions based on role
        $user = Auth::user();
        $userRole = $user->role;
        $permissions = $this->getUserPermissions($userRole);

        return view('dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalSuppliers',
            'totalUsers',
            'outOfStock',
            'lowStock',
            'totalCompletedSales',
            'salesToday',
            'recentTransactions',
            'lowStockProducts',
            'permissions',
            'userRole'
        ));
    }

    /**
     * Get permissions based on user role
     */
    private function getUserPermissions($role)
    {
        $permissions = [];

        if ($role === 'admin') {
            $permissions = [
                'products' => ['route' => 'admin.productsManagement', 'canAccess' => true],
                'categories' => ['route' => 'admin.categoriesManagement', 'canAccess' => true],
                'suppliers' => ['route' => 'admin.suppliersManagement', 'canAccess' => true],
                'users' => ['route' => 'admin.userManagement', 'canAccess' => true],
                'inventory' => ['route' => 'admin.inventoryManagement', 'canAccess' => true],
                'transactions' => ['route' => 'admin.transactionHistory', 'canAccess' => true],
                'sales' => ['route' => 'admin.salesReport', 'canAccess' => true],
            ];
        } elseif ($role === 'pharmacist') {
            $permissions = [
                'products' => ['route' => 'pharmacist.productsManagement', 'canAccess' => true],
                'categories' => ['route' => 'pharmacist.categoriesManagement', 'canAccess' => true],
                'suppliers' => ['route' => 'pharmacist.suppliersManagement', 'canAccess' => true],
                'users' => ['route' => 'pharmacist.userManagement', 'canAccess' => false],
                'inventory' => ['route' => 'pharmacist.inventoryManagement', 'canAccess' => true],
                'transactions' => ['route' => 'pharmacist.transactionHistory', 'canAccess' => false],
                'sales' => ['route' => 'pharmacist.salesReport', 'canAccess' => false],
            ];
        } elseif ($role === 'cashier') {
            $permissions = [
                'products' => ['route' => 'cashier.posManagement', 'canAccess' => true],
                'categories' => ['route' => 'cashier.posManagement', 'canAccess' => false],
                'suppliers' => ['route' => 'cashier.suppliersManagement', 'canAccess' => false],
                'users' => ['route' => 'cashier.userManagement', 'canAccess' => false],
                'inventory' => ['route' => 'cashier.inventoryManagement', 'canAccess' => true],
                'transactions' => ['route' => 'cashier.transactionHistory', 'canAccess' => true],
                'sales' => ['route' => 'cashier.salesReport', 'canAccess' => true],
            ];
        }

        return $permissions;
    }
}
