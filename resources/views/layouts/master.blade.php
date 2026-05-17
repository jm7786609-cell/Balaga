<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('APP-TITLE') | {{ env('APP_NAME') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('dist/img/AdminLTELogo.png') }}">

    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <!-- Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-fileinput/css/fileinput.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/data-table/css/bootstrap-table.css') }}">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

    <!-- ------------------------------------------------------------- -->
    <!--   PHARMACY THEME + GLASSMORPHISM + ANIMATED BACKGROUND        -->
    <!-- ------------------------------------------------------------- -->
    <style>
        /* ------------------------------------- */
        /* Soft animated floating pharmacy bubbles */
        /* ------------------------------------- */
        body {
            overflow-x: hidden;
            background: linear-gradient(135deg, #d9f7ed, #baf2da);
        }

        .bg-bubble {
            position: fixed;
            z-index: 0;
            border-radius: 50%;
            background: rgba(43, 191, 130, 0.18);
            animation: floatUp 16s infinite ease-in-out;
            filter: blur(2px);
        }

        .bg-bubble:nth-child(1) {
            width: 160px;
            height: 160px;
            left: 10%;
            animation-duration: 18s;
            top: 70%;
        }

        .bg-bubble:nth-child(2) {
            width: 110px;
            height: 110px;
            left: 70%;
            animation-duration: 22s;
            top: 85%;
        }

        .bg-bubble:nth-child(3) {
            width: 200px;
            height: 200px;
            left: 40%;
            animation-duration: 21s;
            top: 80%;
        }

        @keyframes floatUp {
            0% {
                transform: translateY(0);
                opacity: 0.35;
            }

            50% {
                opacity: 0.65;
            }

            100% {
                transform: translateY(-130vh);
                opacity: 0;
            }
        }

        /* ---------------------------- */
        /* GLASS NAVBAR                 */
        /* ---------------------------- */
        .main-header {
            background: rgba(255, 255, 255, 0.25) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.4) !important;
        }

        .navbar .nav-link i {
            color: #2bbf82 !important;
            font-size: 20px;
        }

        /* ---------------------------- */
        /* GLASS SIDEBAR                */
        /* ---------------------------- */
        .main-sidebar {
            background: rgba(20, 45, 30, 0.80) !important;
            backdrop-filter: blur(16px);
        }

        .brand-link {
            background: rgba(255, 255, 255, 0.15) !important;
        }

        .brand-link .brand-text {
            color: #c5f2dd !important;
            font-weight: 700;
        }

        .nav-sidebar>.nav-item>.nav-link.active {
            background-color: rgba(43, 191, 130, 0.25) !important;
            color: #2bbf82 !important;
            border-left: 4px solid #2bbf82;
        }

        .nav-sidebar>.nav-item>.nav-link:hover {
            background-color: rgba(43, 191, 130, 0.15) !important;
        }

        /* ---------------------------- */
        /* CARDS                        */
        /* ---------------------------- */
        .card {
            border-radius: 14px;
            border: none;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: #2bbf82 !important;
            color: white !important;
            border-radius: 14px 14px 0 0 !important;
        }

        .content-wrapper {
            background: rgba(255, 255, 255, 0.55);
            /* backdrop-filter: blur(6px); */
        }

        /* ---------------------------- */
        /* BUTTONS                      */
        /* ---------------------------- */
        .btn-primary {
            background-color: #2bbf82 !important;
            border-color: #28a776 !important;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #28a776 !important;
        }

        .btn-secondary {
            border-radius: 8px;
        }

        /* Breadcrumb */
        .breadcrumb-item a {
            color: #1e7b59 !important;
        }

        /* ─────────────────────────────────────────────────────────────── */
        /* FIX: Disable inherited backdrop-filter on modals (Bootstrap 4) */
        /* ─────────────────────────────────────────────────────────────── */
        .modal,
        .modal-backdrop {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            /* Safari */
        }

        /* Optional: slightly improve modal appearance on glass background */
        .modal-content {
            background: rgba(255, 255, 255, 0.96) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: none;
        }

        .modal-header {
            background: #2bbf82 !important;
            color: white !important;
            border-radius: 14px 14px 0 0 !important;
        }

        /* Keep your beautiful bubbles visible behind the modal */
        .bg-bubble {
            z-index: 0 !important;
        }

        .user-panel .user-name {
            font-size: 15px;
            color: #e7fff4 !important;
            letter-spacing: 0.4px;
        }

        .role-badge {
            display: inline-block;
            margin-top: 3px;
            padding: 3px 10px;
            font-size: 12px;
            color: #4facfe;
            background: rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(43, 191, 130, 0.35);
            border-radius: 8px;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.10);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.6px;
        }

        /* Improve color consistency on sidebar */
        .main-sidebar .user-panel .info a,
        .main-sidebar .user-panel .info div {
            color: #c5f2dd !important;
        }

        /* Navbar polish */
        .main-header {
            transition: all 0.3s ease;
        }

        /* Icon button */
        .nav-icon-btn {
            border-radius: 8px;
            transition: 0.2s ease;
        }

        .nav-icon-btn:hover {
            background: rgba(43, 191, 130, 0.15);
            transform: scale(1.05);
        }

        /* Welcome text */
        .nav-welcome {
            color: #1e7b59;
            font-weight: 500;
            letter-spacing: 0.4px;
        }

        /* Avatar */
        .nav-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid #2bbf82;
            transition: 0.3s ease;
        }

        .nav-avatar:hover {
            transform: scale(1.08);
            box-shadow: 0 0 10px rgba(43, 191, 130, 0.4);
        }

        /* Glass dropdown */
        .dropdown-glass {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(14px);
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            min-width: 220px;
        }

        .dropdown-glass .dropdown-item {
            border-radius: 8px;
            transition: 0.2s ease;
        }

        .dropdown-glass .dropdown-item:hover {
            background: rgba(43, 191, 130, 0.15);
        }
    </style>

    @yield('APP-STYLES')
</head>

<body class="sidebar-mini layout-fixed layout-footer-fixed layout-navbar-fixed">

    <!-- Animated Background -->
    <div class="bg-bubble"></div>
    <div class="bg-bubble"></div>
    <div class="bg-bubble"></div>

    <div class="wrapper">

        @include('layouts.navbar')

        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-teal elevation-4">
            <a href="#" class="brand-link">
                <img src="{{ asset('dist/img/AdminLTELogo.png') }}" class="brand-image img-circle elevation-3">
                <span class="brand-text font-weight-light">{{ env('APP_NAME') }}</span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                    <div class="image">
                        <img src="{{ asset('dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2"
                            alt="User Image">
                    </div>

                    <div class="info ml-2">
                        <div class="user-name text-white font-weight-bold">
                            {{ auth()->user()->full_name ?? 'User' }}
                        </div>

                        <div class="user-role">
                            <span class="role-badge">
                                {{ auth()->user()->role ?? 'Role' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- MENU -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column">

                        @if (auth()->user()->role === 'admin')
                            @include('admin.sidebar')
                        @elseif (auth()->user()->role === 'cashier')
                            @include('cashier.sidebar')
                        @elseif (auth()->user()->role === 'pharmacist')
                            @include('pharmacist.sidebar')
                        @elseif (auth()->user()->role === 'supplier')
                            @include('supplier.sidebar')
                        @endif

                    </ul>
                </nav>
            </div>

        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <h1>@yield('APP-TITLE')</h1>
                </div>
            </section>

            <section class="content">
                @yield('APP-CONTENT')
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer text-dark">
            <strong>&copy; {{ date('Y') }} {{ env('APP_NAME') }}</strong>. All rights reserved.
        </footer>

    </div>

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Data Table -->
    <script src="{{ asset('plugins/data-table/js/bootstrap-table.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-addrbar.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-auto-refresh.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-custom-view.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-defer-url.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-editable.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-export.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-filter-control.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-fixed-columns.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-mobile.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-multiple-sort.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-page-jump-to.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-pipeline.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-print.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-resizable.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-sticky-header.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/bootstrap-table-toolbar.js') }}"></script>
    <script src="{{ asset('plugins/data-table/js/utils.js') }}"></script>

    <!-- Toastr -->
    <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>

    <!-- jquery-validation -->
    <script src="{{ asset('plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-validation/additional-methods.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.all.js') }}"></script>

    <!-- Bootstrap Fileinput -->
    <script src="{{ asset('plugins/bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap-fileinput/themes/fa5/theme.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script>
        const companyLogo = "{{ asset('dist/img/AdminLTELogo.png') }}";

        function openModal(modal) {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
            $(modal).modal('show');
        }

        function closeModal(modal) {
            $(modal).modal('hide');
            setTimeout(() => {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            }, 300); // Wait for animation
        }

        function myCustomPrint(table, title) {
            return `
        <html>
            <head>
                <style type="text/css" media="print">
                    @page {
                        size: auto;
                        margin: 25px 0 25px 0;
                    }
                </style>

                <style type="text/css" media="all">
                    body {
                        font-family: Arial, sans-serif;
                    }

                    /* Header layout */
                    .report-header {
                        width: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 20px;
                        padding: 10px 0;
                        border-bottom: 2px solid #444;
                    }

                    .header-left {
                        width: 20%;
                        text-align: left;
                        padding-left: 20px;
                    }

                    .header-center {
                        width: 60%;
                        text-align: center;
                    }

                    .header-right {
                        width: 20%;
                    }

                    .header-center h1 {
                        margin: 0;
                        font-size: 28px;
                    }

                    .header-center h3 {
                        margin: 5px 0 0 0;
                        font-size: 16px;
                        font-weight: normal;
                    }

                    .report-title {
                        text-align: center;
                        margin-top: 15px;
                        font-size: 22px;
                        font-weight: bold;
                        text-transform: uppercase;
                    }

                    table {
                        border-collapse: collapse;
                        font-size: 12px;
                        width: 94%;
                        margin-left: 3%;
                        margin-right: 3%;
                    }
                    table, th, td {
                        border: 1px solid grey;
                    }
                    th, td {
                        text-align: center;
                        vertical-align: middle;
                    }

                    .bs-table-print {
                        text-align: center;
                    }
                </style>
            </head>

            <title>{{ env('APP_NAME') }} | ${title}</title>

            <body>

                <!-- HEADER -->
                <div class="report-header">

                    <!-- LOGO -->
                    <div class="header-left">
                        <img src="${companyLogo}" alt="Company Logo" style="max-width:100px; max-height:100px;">
                    </div>

                    <!-- COMPANY INFO -->
                    <div class="header-center">
                        <h1><b>{{ env('APP_NAME') }}</b></h1>
                        <h3>Company Location Here</h3>
                    </div>

                    <div class="header-right"></div>
                </div>

                <!-- REPORT TITLE -->
                <div class="report-title">${title}</div>

                <div class="bs-table-print">
                    ${table}
                </div>

            </body>
        </html>
    `;
        }



        $(document).ready(function() {
            // CSRF token setup for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 5000
            };

            $('#signOut').click(function(event) {
                event.preventDefault();

                Swal.fire({
                    title: 'Logout?',
                    text: "You will be signed out of the system.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2bbf82',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, logout'
                }).then((result) => {
                    if (result.isConfirmed) {

                        $.ajax({
                            method: 'POST',
                            url: '{{ route('logout') }}',
                            dataType: 'JSON',
                            success: function(response) {
                                toastr.success(response.message);
                                location.href = "{{ route('loginPage') }}";
                            },
                            error: function(xhr) {
                                toastr.error('Unable to logout.');
                            }
                        });

                    }
                });
            });
        });
    </script>
    @yield('APP-SCRIPT')
</body>

</html>
