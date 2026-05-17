<?php

namespace App\Http\Controllers\Navigation;

use App\Http\Controllers\Controller;


class CashierNavigationController extends Controller
{
    public function inventoryManagement()
    {
        return view('cashier.inventory_management');
    }

    public function posManagement()
    {
        return view('cashier.pos_management');
    }

    public function transactionHistory()
    {
        return view('cashier.transaction_history');
    }

    public function salesReport()
    {
        return view('reports.sales');
    }
}
