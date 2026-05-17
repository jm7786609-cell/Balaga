@extends('layouts.master')

@section('APP-TITLE', 'Low Stock Alert')
@section('active-low-stock-report', 'active')
@section('APP-SUBTITLE', 'Products Below Reorder Point')

@section('APP-STYLES')
    <style>
        .badge-out {
            background: #e74c3c;
            color: white;
        }

        .badge-low {
            background: #e67e22;
            color: white;
        }

        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .summary-card.active {
            transform: scale(1.05);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 2px solid white;
        }

        .summary-value {
            font-size: 2rem;
            font-weight: 800;
        }

        .table {
            background: rgba(255, 255, 255, 0.55) !important;
            backdrop-filter: blur(6px);
            border-radius: 10px;
        }

        .filter-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.3);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            display: none;
        }

        .summary-card.active .filter-badge {
            display: block;
        }
    </style>
@endsection

@section('APP-CONTENT')
    <div class="col-lg-12">
        <!-- Summary Cards -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="summary-card bg-danger filter-card" data-filter="out"
                            title="Click to filter out-of-stock products">
                            <div class="filter-badge">Active Filter</div>
                            <h6>Out of Stock</h6>
                            <div class="summary-value" id="count-out">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card filter-card" data-filter="low"
                            style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%)"
                            title="Click to filter low-stock products">
                            <div class="filter-badge">Active Filter</div>
                            <h6>Low Stock</h6>
                            <div class="summary-value" id="count-low">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card filter-card" data-filter="all"
                            style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%)"
                            title="Click to view all stock alerts">
                            <div class="filter-badge">Active Filter</div>
                            <h6>Total Alerts</h6>
                            <div class="summary-value" id="total-alerts">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Low Stock & Reorder Recommendations</h4>
            </div>
            <div class="card-body">
                <table id="lowstock-table" data-url="{{ route('reports.lowStock') }}" data-side-pagination="server"
                    data-pagination="true" data-search="true" data-show-refresh="true" data-show-export="true"
                    data-export-types="['csv','excel','pdf']" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        // State to track current filter
        let currentFilter = null;

        function statusFormatter(value) {
            return value === 'out' ?
                '<span class="badge badge-out">Out of Stock</span>' :
                '<span class="badge badge-low">Low Stock</span>';
        }

        function ajaxRequest(params) {
            const filterParam = currentFilter ? currentFilter : '';

            $.get(params.url, {
                draw: params.data.draw,
                start: params.data.start,
                length: params.data.length,
                search: {
                    value: params.data.search || ''
                },
                filter: filterParam
            }, function(res) {
                const out = res.data.filter(r => r.status === 'out').length;
                const low = res.data.filter(r => r.status === 'low').length;
                $('#count-out').text(out);
                $('#count-low').text(low);
                $('#total-alerts').text(res.recordsFiltered);

                params.success({
                    total: res.recordsTotal,
                    rows: res.data
                });
            }).fail(() => toastr.error('Failed to load low stock data'));
        }

        $(function() {
            // Filter card click handler
            $('.filter-card').on('click', function() {
                const filterValue = $(this).data('filter');
                const isActive = $(this).hasClass('active');

                // Toggle filter off if clicking the same card
                if (isActive && filterValue === currentFilter) {
                    currentFilter = null;
                    $('.filter-card').removeClass('active');
                } else {
                    // Set new filter
                    currentFilter = filterValue === 'all' ? null : filterValue;
                    $('.filter-card').removeClass('active');
                    $(this).addClass('active');
                }

                // Reload table with new filter
                $('#lowstock-table').bootstrapTable('selectPage', 1);
                $('#lowstock-table').bootstrapTable('refresh');
            });

            $('#lowstock-table').bootstrapTable('destroy').bootstrapTable({
                columns: [{
                        field: 'product_code',
                        title: 'Code',
                        sortable: true
                    },
                    {
                        field: 'product_name',
                        title: 'Product',
                        sortable: true
                    },
                    {
                        field: 'brand',
                        title: 'Brand'
                    },
                    {
                        field: 'category',
                        title: 'Category'
                    },
                    {
                        field: 'current_stock',
                        title: 'Stock',
                        align: 'center',
                        width: 90
                    },
                    {
                        field: 'reorder_point',
                        title: 'Reorder Point',
                        align: 'center'
                    },
                    {
                        field: 'shortage',
                        title: 'Need',
                        align: 'center',
                        width: 90
                    },
                    {
                        field: 'supplier',
                        title: 'Supplier'
                    },
                    {
                        field: 'status',
                        title: 'Status',
                        formatter: statusFormatter,
                        align: 'center',
                        width: 130
                    }
                ],
                ajax: ajaxRequest,
                pageSize: 25,
                pageList: [25, 50, 100, 'All'],
                search: true,
                showExport: true,
                exportDataType: 'all',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border"></div> Loading low stock items...</div>',
                printPageBuilder: function printPageBuilder(table) {
                    return myCustomPrint(table, 'Low Stock Report');
                }
            });
        });
    </script>
@endsection
