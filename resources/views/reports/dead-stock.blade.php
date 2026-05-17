@extends('layouts.master')

@section('APP-TITLE', 'Dead / Slow-Moving Items')
@section('active-dead-stock-report', 'active')
@section('APP-SUBTITLE', 'Products with No Sales in Selected Period')

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

        .table {
            background: rgba(255, 255, 255, 0.55) !important;
            backdrop-filter: blur(6px);
            border-radius: 10px;
        }

        .badge-dead {
            background: #2c3e50;
            color: white;
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
                        <div class="summary-card bg-danger">
                            <h6>Dead Stock Items</h6>
                            <div class="summary-value" id="dead-count">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card" style="background:linear-gradient(135deg,#8e44ad 0%,#c0392b 100%)">
                            <h6>Total Units</h6>
                            <div class="summary-value" id="total-units">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card" style="background:linear-gradient(135deg,#34495e 0%,#2c3e50 100%)">
                            <h6>Tied-Up Capital</h6>
                            <div class="summary-value" id="total-value">₱0.00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Dead Stock Items</h4>

                <div class="d-flex align-items-center gap-2">
                    <select id="days-filter" class="form-control" style="width:220px">
                        <option value="30">No Sales in Last 30 Days</option>
                        <option value="60">No Sales in Last 60 Days</option>
                        <option value="90" selected>No Sales in Last 90 Days</option>
                        <option value="180">No Sales in Last 6 Months</option>
                        <option value="365">No Sales in Last Year</option>
                    </select>
                    <button class="btn btn-primary" id="apply-filter">
                        <i class="fa fa-sync"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table id="deadstock-table" data-url="{{ route('reports.deadStock') }}" data-side-pagination="server"
                    data-pagination="true" data-search="true" data-show-refresh="true" data-show-export="true"
                    data-export-types="['csv','excel','pdf']" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        function ajaxRequest(params) {
            const days = $('#days-filter').val();

            $.get(params.url, {
                draw: params.data.draw,
                start: params.data.start,
                length: params.data.length,
                search: {
                    value: params.data.search || ''
                },
                days: days
            }, function(res) {
                const totalUnits = res.data.reduce((s, r) => s + parseInt(r.current_stock), 0);
                const totalValue = res.data.reduce((s, r) => s + parseFloat(r.stock_value.replace(/,/g, '')), 0);

                $('#dead-count').text(res.recordsFiltered);
                $('#total-units').text(totalUnits.toLocaleString());
                $('#total-value').text('₱' + totalValue.toLocaleString('en-PH', {
                    minimumFractionDigits: 2
                }));

                params.success({
                    total: res.recordsTotal,
                    rows: res.data
                });
            }).fail(() => toastr.error('Failed to load dead stock data'));
        }

        $(function() {
            $('#deadstock-table').bootstrapTable('destroy').bootstrapTable({
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
                        field: 'stock_value',
                        title: 'Value',
                        formatter: v => '₱' + v,
                        align: 'right'
                    },
                    {
                        field: 'supplier',
                        title: 'Supplier'
                    },
                    {
                        field: 'last_sold',
                        title: 'Last Sold',
                        sortable: true
                    }
                ],
                ajax: ajaxRequest,
                pageSize: 25,
                pageList: [25, 50, 100, 'All'],
                search: true,
                showExport: true,
                showPrint: true,
                exportDataType: 'all',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border"></div> Loading dead stock...</div>',
                printPageBuilder: function printPageBuilder(table) {
                    return myCustomPrint(table, 'Dead / Slow-Moving Items');
                }
            });

            $('#apply-filter').on('click', () => $('#deadstock-table').bootstrapTable('refresh'));

            // Load default (90 days)
            $('#deadstock-table').bootstrapTable('refresh');
        });
    </script>
@endsection
