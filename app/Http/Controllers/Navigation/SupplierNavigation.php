<?php

namespace App\Http\Controllers\Navigation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupplierNavigation extends Controller
{
    public function requisitionManagement()
    {
        return view('supplier.item_requisition_management');
    }
}
