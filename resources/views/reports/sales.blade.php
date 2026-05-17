@extends('layouts.master')

@section('APP-TITLE', 'Sales Report')
@section('active-sales-report', 'active')
@section('APP-SUBTITLE', 'Daily Sales & Revenue Summary')

@section('APP-STYLES')
    <style>
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .summary-value {
            font-size: 2rem;
            font-weight: 800;
        }

        #toolbar .btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
        }

        .table {
            background: rgba(255, 255, 255, 0.55) !important;
            backdrop-filter: blur(6px);
            border-radius: 10px;
        }

        .date-range-options {
            min-width: 220px;
        }

        .custom-range {
            display: none;
            gap: 10px;
            align-items: center;
        }

        .custom-range.show {
            display: flex;
        }

        .custom-date {
            width: 150px;
        }

        .filter-toolbar select,
        .filter-toolbar input,
        .filter-toolbar button {
            height: 40px;
        }
    </style>
@endsection

@section('APP-CONTENT')
    <div class="col-lg-12">

        <!-- Summary Cards -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row text-center g-3">

                    <div class="col-md-3">
                        <div class="summary-card">
                            <h6>Total Revenue</h6>
                            <div class="summary-value" id="total-revenue">₱0.00</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="summary-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%)">
                            <h6>Transactions</h6>
                            <div class="summary-value" id="total-transactions">0</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="summary-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)">
                            <h6>Items Sold</h6>
                            <div class="summary-value" id="total-items">0</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="summary-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)">
                            <h6>Avg per Sale</h6>
                            <div class="summary-value" id="avg-sale">₱0.00</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">

            <!-- Header with Filters -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Sales Transactions</h4>

                <div class="filter-toolbar d-flex align-items-center gap-2 text-end">

                    <select id="date-filter" class="form-select date-range-options">
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="this_year">This Year</option>
                        <option value="custom" selected>Custom Range</option>
                    </select>

                    <div class="custom-range" id="custom-range-inputs">
                        <input type="date" id="filter-from" class="form-control custom-date">
                        <span class="text-muted">to</span>
                        <input type="date" id="filter-to" class="form-control custom-date">
                    </div>

                    <button class="btn btn-primary shadow-sm" id="apply-filter">
                        <i class="fa fa-sync me-1"></i>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="card-body">
                <table id="sales-table" data-url="{{ route('reports.sales') }}" data-side-pagination="server"
                    data-pagination="true" data-search="true" data-show-columns="true" data-show-refresh="true"
                    data-show-export="true" data-export-types="['csv','excel','pdf']" data-sticky-header="true">
                </table>
            </div>

        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        window.priceFormatter = (value) => `₱${parseFloat(value || 0).toLocaleString('en-PH', {minimumFractionDigits: 2})}`;

        // THIS IS THE ONLY FUNCTION YOU NEED
        function ajaxRequest(params) {
            const from = $('#filter-from').val();
            const to = $('#filter-to').val();

            $.get({
                url: params.url,
                data: {
                    draw: params.data.draw,
                    start: params.data.start,
                    length: params.data.length,
                    search: params.data.search ? {
                        value: params.data.search
                    } : {
                        value: ''
                    },
                    from: from || undefined,
                    to: to || undefined
                },
                success: function(res) {
                    // Update summary cards FIRST
                    const s = res.summary;
                    $('#total-revenue').text('₱' + s.total_revenue);
                    $('#total-transactions').text(s.total_transactions.toLocaleString());
                    $('#total-items').text(s.total_items_sold.toLocaleString());
                    $('#avg-sale').text('₱' + s.average_sale);

                    // This is REQUIRED: pass the modified response back
                    params.success({
                        total: res.recordsTotal,
                        rows: res.data
                    });
                },
                error: function() {
                    toastr.error('Failed to load sales report');
                    params.error();
                }
            });
        }

        $(function() {
            const $table = $('#sales-table');

            $table.bootstrapTable('destroy').bootstrapTable({
                columns: [{
                        field: 'transaction_id',
                        title: 'Trans #',
                        sortable: true,
                    },
                    {
                        field: 'created_at',
                        title: 'Date & Time',
                        sortable: true
                    },
                    {
                        field: 'cashier_name',
                        title: 'Cashier',
                        sortable: true
                    },
                    {
                        field: 'product_code',
                        title: 'Code'
                    },
                    {
                        field: 'product_name',
                        title: 'Product'
                    },
                    {
                        field: 'brand',
                        title: 'Brand'
                    },
                    {
                        field: 'quantity',
                        title: 'Qty',
                        align: 'center',
                    },
                    {
                        field: 'price',
                        title: 'Price',
                        formatter: priceFormatter,
                        align: 'right'
                    },
                    {
                        field: 'subtotal',
                        title: 'Subtotal',
                        formatter: priceFormatter,
                        align: 'right'
                    },
                    {
                        field: 'total_amount',
                        title: 'Total Paid',
                        formatter: priceFormatter,
                        align: 'right'
                    }
                ],
                ajax: ajaxRequest, // Only this
                sidePagination: 'server',
                pageSize: 25,
                pageList: [25, 50, 100, 'All'],
                search: true,
                showRefresh: true,
                showColumns: true,
                showExport: true,
                showPrint: true,
                exportTypes: ['csv', 'excel', 'pdf'],
                exportDataType: 'all',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border"></div> Loading...</div>',
                printPageBuilder: function printPageBuilder(table) {
                    return myCustomPrint(table, 'Sales Report');
                }
            });

            // Filter Logic (unchanged)
            const $filter = $('#date-filter');
            const $custom = $('#custom-range-inputs');
            const $from = $('#filter-from');
            const $to = $('#filter-to');

            $filter.on('change', function() {
                if (this.value !== 'custom') {
                    $custom.removeClass('show');
                    applyPreset(this.value);
                } else {
                    $custom.addClass('show');
                }
            });

            function applyPreset(period) {
                const d = new Date();
                let from, to = d.toISOString().split('T')[0];

                switch (period) {
                    case 'today':
                        from = to;
                        break;
                    case 'this_week':
                        const start = new Date(d);
                        start.setDate(d.getDate() - d.getDay());
                        from = start.toISOString().split('T')[0];
                        break;
                    case 'this_month':
                        from = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-01';
                        break;
                    case 'this_year':
                        from = d.getFullYear() + '-01-01';
                        break;
                }
                $from.val(from);
                $to.val(to);
                $table.bootstrapTable('refresh');
            }

            $('#apply-filter').on('click', () => {
                if ($filter.val() !== 'custom') {
                    applyPreset($filter.val());
                } else {
                    $table.bootstrapTable('refresh');
                }
            });

            // Start with Today
            $filter.val('today').trigger('change');
        });
    </script>
@endsection
