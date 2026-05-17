<li class="nav-header">HOME</li>

<li class="nav-item">
    <a href="{{ route('admin.dashboard') }}" class="nav-link @yield('active-dashboard')">
        <i class="nav-icon bi bi-speedometer2"></i>
        <p>Dashboard</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.posManagement') }}" class="nav-link @yield('active-pos')">
        <i class="nav-icon bi bi-basket"></i>
        <p>POS</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.transactionHistory') }}" class="nav-link @yield('active-transactions')">
        <i class="nav-icon bi bi-clock-history"></i>
        <p>Transaction History</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.stockInManagement') }}" class="nav-link @yield('active-stock-in')">
        <i class="nav-icon bi bi-box-arrow-in-down"></i>
        <p>Stock Ins</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.inventoryManagement') }}" class="nav-link @yield('active-inventory')">
        <i class="nav-icon bi bi-journal-medical"></i>
        <p>Inventories</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.requisitionManagement') }}" class="nav-link @yield('active-purchase-requisitions')">
        <i class="nav-icon bi bi-box-arrow-in-down"></i>
        <p>Requisition</p>
    </a>
</li>

<li class="nav-header">MANAGEMENT</li>

<li class="nav-item">
    <a href="{{ route('admin.productsManagement') }}" class="nav-link @yield('active-products')">
        <i class="nav-icon bi bi-box-seam"></i>
        <p>Products</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.categoriesManagement') }}" class="nav-link @yield('active-categories')">
        <i class="nav-icon bi bi-tags"></i>
        <p>Categories</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.suppliersManagement') }}" class="nav-link @yield('active-suppliers')">
        <i class="nav-icon bi bi-truck"></i>
        <p>Suppliers</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.userManagement') }}" class="nav-link @yield('active-users')">
        <i class="nav-icon bi bi-people"></i>
        <p>Users</p>
    </a>
</li>

<li class="nav-header">Reports</li>
<li class="nav-item">
    <a href="{{ route('admin.salesReport') }}" class="nav-link @yield('active-sales-report')">
        <i class="nav-icon bi bi-bar-chart-line"></i>
        <p>Sales Report</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.expiryReport') }}" class="nav-link @yield('active-expiry-report')">
        <i class="nav-icon bi bi-alarm"></i>
        <p>Expiry Report</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.lowStockReport') }}" class="nav-link @yield('active-low-stock-report')">
        <i class="nav-icon bi bi-exclamation-triangle"></i>
        <p>Low Stock Report</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.topSellersReport') }}" class="nav-link @yield('active-top-sellers-report')">
        <i class="nav-icon bi bi-award"></i>
        <p>Top Sellers Report</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.deadStockReport') }}" class="nav-link @yield('active-dead-stock-report')">
        <i class="nav-icon bi bi-dash-circle"></i>
        <p>Dead Stock Report</p>
    </a>
</li>
