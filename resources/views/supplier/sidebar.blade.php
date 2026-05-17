<li class="nav-header">HOME</li>

<li class="nav-item">
    <a href="{{ route('supplier.dashboard') }}" class="nav-link @yield('active-dashboard')">
        <i class="nav-icon bi bi-speedometer2"></i>
        <p>Dashboard</p>
    </a>
</li>

<li class="nav-header">MANAGEMENT</li>

<li class="nav-item">
    <a href="{{ route('supplier.requisitionManagement') }}" class="nav-link @yield('active-requisitions')">
        <i class="nav-icon bi bi-journal-medical"></i>
        <p>Item Requisition</p>
    </a>
</li>
