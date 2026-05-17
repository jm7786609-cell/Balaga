@extends('layouts.master')

@section('APP-TITLE', 'Dashboard')
@section('active-dashboard', 'active')
@section('APP-SUBTITLE', 'Pharmacy Management Overview')

@section('APP-CONTENT')
    <div class="container-fluid">

        <!-- Top Stats -->
        <div class="row">
            @php
                $cards = [
                    ['label' => 'Products', 'value' => $totalProducts, 'icon' => 'fa-box', 'key' => 'products'],
                    ['label' => 'Categories', 'value' => $totalCategories, 'icon' => 'fa-tags', 'key' => 'categories'],
                    ['label' => 'Suppliers', 'value' => $totalSuppliers, 'icon' => 'fa-truck', 'key' => 'suppliers'],
                    ['label' => 'Users', 'value' => $totalUsers, 'icon' => 'fa-users', 'key' => 'users'],
                ];
            @endphp

            @foreach ($cards as $c)
                @if (isset($permissions[$c['key']]) && $permissions[$c['key']]['canAccess'])
                    <div class="col-md-3 mb-3">
                        <a href="{{ route($permissions[$c['key']]['route']) }}" class="text-decoration-none">
                            <div class="card shadow-sm text-center h-100"
                                style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                                onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.15)';"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)';">
                                <div class="card-body">
                                    <i class="fas {{ $c['icon'] }} fa-2x mb-2 text-primary"></i>
                                    <h5>{{ $c['label'] }}</h5>
                                    <h3 class="fw-bold">{{ $c['value'] }}</h3>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Sales & Stock -->
        <div class="row mt-4">
            @if (isset($permissions['sales']) && $permissions['sales']['canAccess'])
                <div class="col-md-4">
                    <a href="{{ route($permissions['sales']['route']) }}" class="text-decoration-none">
                        <div class="card shadow-sm h-100"
                            style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.15)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)';">
                            <div class="card-body text-center">
                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                <h5>Today's Sales</h5>
                                <h2 class="fw-bold">₱{{ number_format($salesToday, 2) }}</h2>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            @if (isset($permissions['transactions']) && $permissions['transactions']['canAccess'])
                <div class="col-md-4">
                    <a href="{{ route($permissions['transactions']['route']) }}" class="text-decoration-none">
                        <div class="card shadow-sm h-100"
                            style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.15)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)';">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                                <h5>Total Completed Sales</h5>
                                <h2 class="fw-bold">{{ $totalCompletedSales }}</h2>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            @if (isset($permissions['inventory']) && $permissions['inventory']['canAccess'])
                <div class="col-md-4">
                    <a href="{{ route($permissions['inventory']['route']) }}" class="text-decoration-none">
                        <div class="card shadow-sm text-center h-100"
                            style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.15)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)';">
                            <div class="card-body">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                <h5>Low Stock Items</h5>
                                <h2 class="fw-bold">{{ $lowStock }}</h2>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        <!-- Recent Transactions -->
        <div class="row mt-5">
            @if (isset($permissions['transactions']) && $permissions['transactions']['canAccess'])
                <div class="col-md-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <a href="{{ route($permissions['transactions']['route']) }}"
                                class="text-decoration-none text-white">
                                <h5 class="mb-0" style="cursor: pointer;"><i class="fas fa-receipt"></i> Recent
                                    Transactions</h5>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Receipt</th>
                                        <th>Cashier</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentTransactions as $t)
                                        <tr>
                                            <td><strong>{{ $t->id }}</strong></td>
                                            <td>{{ $t->cashier->fullname ?? '—' }}</td>
                                            <td>₱{{ number_format($t->total_amount, 2) }}</td>
                                            <td>{{ $t->created_at->format('M d, Y h:i A') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No recent transactions.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Low Stock Table -->
            @if (isset($permissions['inventory']) && $permissions['inventory']['canAccess'])
                <div class="col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <a href="{{ route($permissions['inventory']['route']) }}"
                                class="text-decoration-none text-white">
                                <h5 class="mb-0" style="cursor: pointer;"><i class="fas fa-box"></i> Low Stock Items</h5>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($lowStockProducts as $p)
                                        <tr>
                                            <td>{{ $p->product_name }}</td>
                                            <td><strong class="text-danger">{{ $p->qty }}</strong></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">No low stock items.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
