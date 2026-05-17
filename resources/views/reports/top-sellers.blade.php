@extends('layouts.master')

@section('APP-TITLE', 'Top Sellers')
@section('active-top-sellers-report', 'active')
@section('APP-SUBTITLE', 'Best-Selling Products')

@section('APP-STYLES')
    <style>
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
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

        .rank-badge {
            background: #667eea;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .top-3 .rank-badge {
            background: #f1c40f;
        }

        .top-1 .rank-badge {
            background: #e67e22;
        }

        .row.text-center>[class*="col-"] {
            display: flex;
        }

        .summary-card {
            flex: 1;
            min-height: 160px;
            /* or height: 160px */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    </style>
@endsection

@section('APP-CONTENT')
    <div class="col-lg-12">
        <!-- Summary Cards -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h6>Total Items Sold</h6>
                            <div class="summary-value" id="total-sold">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card" style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%)">
                            <h6>Top Product</h6>
                            <div class="summary-value" id="top-product">—</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card" style="background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%)">
                            <h6>Revenue from Top 20</h6>
                            <div class="summary-value" id="top-revenue">₱0.00</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card" style="background:linear-gradient(135deg,#43e97b 0%,#38f9d7 100%)">
                            <h6>Date Range</h6>
                            <div class="summary-value" id="date-range">All Time</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Top Selling Products</h4>

                <div class="d-flex align-items-center gap-2">
                    <select id="date-filter" class="form-control" style="width:200px">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month" selected>This Month</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    <div class="d-none" id="custom-range">
                        <input type="date" id="from-date" class="form-control" style="width:140px">
                        <input type="date" id="to-date" class="form-control" style="width:140px">
                    </div>
                    <button class="btn btn-primary" id="apply-filter">
                        <i class="fa fa-sync"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table id="topsellers-table" data-url="{{ route('reports.topSellers') }}" data-side-pagination="server"
                    data-pagination="true" data-search="true" data-show-refresh="true" data-show-export="true"
                    data-export-types="['csv','excel','pdf']" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        function rankFormatter(value, row, index) {
            const rank = row.rank;
            const cls = rank === 1 ? 'top-1' : (rank <= 3 ? 'top-3' : '');
            return `<div class="rank-badge ${cls}">${rank}</div>`;
        }

        function percentageFormatter(value, row) {
            if (!window.totalQty) return '0%';
            const pct = (row.total_qty / window.totalQty) * 100;
            return `<strong>${pct.toFixed(1)}%</strong>`;
        }

        function ajaxRequest(params) {
            const filter = $('#date-filter').val();
            let from = null,
                to = null;

            if (filter === 'custom') {
                from = $('#from-date').val();
                to = $('#to-date').val();
            } else if (filter !== 'all') {
                const d = new Date();
                to = d.toISOString().split('T')[0];
                if (filter === 'today') from = to;
                else if (filter === 'this_week') {
                    const start = new Date(d);
                    start.setDate(d.getDate() - d.getDay());
                    from = start.toISOString().split('T')[0];
                } else if (filter === 'this_month') from = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2,
                    '0') + '-01';
                else if (filter === 'this_year') from = d.getFullYear() + '-01-01';
            }

            $.get(params.url, {
                draw: params.data.draw,
                start: params.data.start,
                length: params.data.length,
                search: {
                    value: params.data.search || ''
                },
                from: from,
                to: to
            }, function(res) {
                window.totalQty = res.total_qty_all;

                // Update summary
                const top = res.data[0];
                $('#total-sold').text(res.total_qty_all.toLocaleString());
                $('#top-product').text(top ? top.product_name : '—');
                $('#top-revenue').text('₱' + res.data.slice(0, 20).reduce((s, r) => s + parseFloat(r.total_revenue),
                    0).toLocaleString('en-PH', {
                    minimumFractionDigits: 2
                }));
                $('#date-range').text($('#date-filter option:selected').text());

                res.data.forEach(r => r.percentage = 0); // will be formatted in JS

                params.success({
                    total: res.recordsTotal,
                    rows: res.data
                });
            });
        }

        $(function() {
            const $table = $('#topsellers-table');

            $table.bootstrapTable('destroy').bootstrapTable({
                columns: [{
                        field: 'rank',
                        title: '#',
                        formatter: rankFormatter,
                        width: 70,
                        align: 'center'
                    },
                    {
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
                        field: 'total_qty',
                        title: 'Qty Sold',
                        sortable: true,
                        align: 'center'
                    },
                    {
                        field: 'percentage',
                        title: '% of Total',
                        formatter: percentageFormatter,
                        align: 'center'
                    },
                    {
                        field: 'total_revenue',
                        title: 'Revenue',
                        formatter: v => '₱' + parseFloat(v).toLocaleString('en-PH', {
                            minimumFractionDigits: 2
                        }),
                        align: 'right',
                        sortable: true
                    },
                ],
                ajax: ajaxRequest,
                pageSize: 20,
                pageList: [20, 50, 100],
                search: true,
                showExport: true,
                showPrint: true,
                exportDataType: 'all',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border"></div> Loading dead stock...</div>',
                printPageBuilder: function printPageBuilder(table) {
                    return myCustomPrint(table, 'Top Sellers');
                }
            });

            $('#date-filter').on('change', function() {
                $('#custom-range').toggleClass('d-none', this.value !== 'custom');
            });

            $('#apply-filter').on('click', () => $table.bootstrapTable('refresh'));

            // Load with "This Month" default
            $('#date-filter').val('this_month');
            $table.bootstrapTable('refresh');
        });
    </script>
@endsection
