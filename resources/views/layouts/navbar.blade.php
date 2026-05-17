<nav class="main-header navbar navbar-expand navbar-light px-3">

    <!-- Left -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link nav-icon-btn" data-widget="pushmenu" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- Right -->
    <ul class="navbar-nav ml-auto align-items-center">

        <!-- Welcome Text -->
        <li class="nav-item d-none d-md-block mr-3">
            <span class="nav-welcome">
                Welcome, <strong>{{ auth()->user()->full_name ?? 'User' }}</strong>
            </span>
        </li>

        <!-- User Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link user-dropdown-toggle" data-toggle="dropdown" href="#">
                <img src="{{ asset('dist/img/user2-160x160.jpg') }}" class="nav-avatar" alt="User Image">
            </a>

            <div class="dropdown-menu dropdown-menu-right dropdown-glass">

                <div class="dropdown-header text-center">
                    <strong>{{ auth()->user()->full_name ?? 'User' }}</strong>
                    <br>
                    <small>{{ auth()->user()->role ?? 'Role' }}</small>
                </div>

                <div class="dropdown-divider"></div>

                <a href="#" class="dropdown-item" id="signOut">
                    <i class="fas fa-sign-out-alt mr-2 text-danger"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>
</nav>
