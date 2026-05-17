<?php

namespace App\Http\Controllers\Navigation;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class AdminNavigationController extends Controller
{
    public function userManagement()
    {
        return view('admin.user_list');
    }

    public function categoriesManagement()
    {
        return view('admin.categories_management');
    }

    public function suppliersManagement()
    {
        return view('admin.suppliers_management');
    }

    public function productsManagement()
    {
        $categories = Category::all();
        $suppliers = Supplier::all();
        return view('admin.products_management', compact('categories', 'suppliers'));
    }

    public function inventoryManagement()
    {
        return view('admin.inventory_reports');
    }

    public function stockInManagement()
    {
        $products = Product::all();
        return view('admin.stock_in_management', compact('products'));
    }

    public function requisitionManagement()
    {
        $products = Product::all();
        $suppliers = Supplier::all();
        return view('admin.purchase_requisition_management', compact('products', 'suppliers'));
    }

    public function posManagement()
    {
        return view('admin.pos_management');
    }

    public function transactionHistory()
    {
        return view('admin.transaction_history');
    }

    public function salesReport()
    {
        return view('reports.sales');
    }

    public function expiryReport()
    {
        return view('reports.expiry');
    }

    public function lowStockReport()
    {
        return view('reports.low-stock');
    }

    public function topSellersReport()
    {
        return view('reports.top-sellers');
    }

    public function deadStockReport()
    {
        return view('reports.dead-stock');
    }
}
