@extends('layouts.master')

@section('APP-TITLE', 'Expiry Report')
@section('active-expiry-report', 'active')
@section('APP-SUBTITLE', 'Near-Expiry & Expired Batches')

@section('APP-STYLES')
    <style>
        .badge-expired {
            background: #e74c3c;
            color: white;
        }

        .badge-near {
            background: #e67e22;
            color: white;
        }

        .badge-warning {
            background: #f39c12;
            color: white;
        }

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
                            <h6>Expired Batches</h6>
                            <div class="summary-value" id="count-expired">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card" style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%)">
                            <h6>Near Expiry (≤30 days)</h6>
                            <div class="summary-value" id="count-near">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card" style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%)">
                            <h6>Expiring Soon (31–90 days)</h6>
                            <div class="summary-value" id="count-soon">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Expiry Monitoring</h4>
            </div>
            <div class="card-body">
                <table id="expiry-table" data-url="{{ route('reports.expiry') }}" data-side-pagination="server"
                    data-pagination="true" data-search="true" data-show-refresh="true" data-show-export="true"
                    data-export-types="['csv','excel','pdf']" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        function statusFormatter(value) {
            if (value === 'expired') return '<span class="badge badge-expired">Expired</span>';
            if (value === 'near') return '<span class="badge badge-near">Near Expiry</span>';
            return '<span class="badge badge-warning">Expiring Soon</span>';
        }

        function ajaxRequest(params) {
            $.get(params.url, {
                draw: params.data.draw,
                start: params.data.start,
                length: params.data.length,
                search: {
                    value: params.data.search || ''
                }
            }, function(res) {
                // Update counters
                const expired = res.data.filter(r => r.status === 'expired').length;
                const near = res.data.filter(r => r.status === 'near').length;
                const soon = res.data.filter(r => r.status === 'warning').length;
                $('#count-expired').text(expired);
                $('#count-near').text(near);
                $('#count-soon').text(soon);

                params.success({
                    total: res.recordsTotal,
                    rows: res.data
                });
            }).fail(() => toastr.error('Failed to load expiry data'));
        }

        $(function() {
            $('#expiry-table').bootstrapTable('destroy').bootstrapTable({
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
                        field: 'batch_no',
                        title: 'Batch No'
                    },
                    {
                        field: 'quantity',
                        title: 'Qty',
                        align: 'center',
                        width: 80
                    },
                    {
                        field: 'expiration_date',
                        title: 'Expiry Date',
                        sortable: true
                    },
                    {
                        field: 'days_remaining',
                        title: 'Status',
                        formatter: statusFormatter,
                        align: 'center'
                    }
                ],
                ajax: ajaxRequest,
                pageSize: 25,
                pageList: [25, 50, 100, 'All'],
                search: true,
                showPrint: true,
                showExport: true,
                exportDataType: 'all',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border"></div> Loading expiry data...</div>',
                printPageBuilder: function printPageBuilder(table) {
                    return myCustomPrint(table, 'Expiry Report');
                }
            });
        });
    </script>
@endsection
