<li class="nav-header">HOME</li>

<li class="nav-item">
    <a href="{{ route('cashier.dashboard') }}" class="nav-link @yield('active-dashboard')">
        <i class="nav-icon bi bi-speedometer2"></i>
        <p>Dashboard</p>
    </a>
</li>

<li class="nav-header">MANAGEMENT</li>

<li class="nav-item">
    <a href="{{ route('cashier.inventoryManagement') }}" class="nav-link @yield('active-inventory')">
        <i class="nav-icon bi bi-journal-medical"></i>
        <p>Inventories</p>
    </a>
</li>

<li class="nav-header">Transaction</li>
<li class="nav-item">
    <a href="{{ route('cashier.posManagement') }}" class="nav-link @yield('active-pos')">
        <i class="nav-icon bi bi-basket"></i>
        <p>POS</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('cashier.transactionHistory') }}" class="nav-link @yield('active-transactions')">
        <i class="nav-icon bi bi-clock-history"></i>
        <p>Transaction History</p>
    </a>
</li>

<li class="nav-header">Reports</li>
<li class="nav-item">
    <a href="{{ route('cashier.salesReport') }}" class="nav-link @yield('active-sales-report')">
        <i class="nav-icon bi bi-bar-chart-line"></i>
        <p>Sales Report</p>
    </a>
</li>
