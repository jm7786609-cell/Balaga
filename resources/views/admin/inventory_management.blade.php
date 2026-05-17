@extends('layouts.master')

@section('APP-TITLE', 'Inventory Management')
@section('active-inventory', 'active')
@section('APP-SUBTITLE', 'Track Batches, Expiry & Stock Levels')

@section('APP-STYLES')
    <style>
        #toolbar .btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
        }

        table.table {
            background: rgba(255, 255, 255, 0.55) !important;
            backdrop-filter: blur(6px);
            border-radius: 10px;
        }

        .modal-content {
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }

        .badge-expired {
            background: #e74c3c;
        }

        .badge-near-expiry {
            background: #e67e22;
        }

        .badge-low {
            background: #f39c12;
        }

        .text-expired {
            color: #c0392b;
            font-weight: 600;
        }

        .text-near {
            color: #d35400;
        }

        label {
            font-weight: 600;
        }
    </style>
@endsection

@section('APP-CONTENT')
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">@yield('APP-SUBTITLE')</h4>
                <div>

                </div>
            </div>

            <div class="card-body">
                <div id="toolbar">
                    <button class="btn btn-success shadow-sm me-2" id="filter-expired">
                        <i class="fa fa-exclamation-triangle"></i> Expired
                    </button>
                    <button class="btn btn-warning shadow-sm me-2" id="filter-near-expiry">
                        <i class="fa fa-clock"></i> Near Expiry
                    </button>
                    <button class="btn btn-info text-white shadow-sm" id="filter-low-stock">
                        <i class="fa fa-box"></i> Low Stock
                    </button>
                </div>

                <table id="table" data-toolbar="#toolbar" data-url="{{ route('inventories.index') }}"
                    data-side-pagination="server" data-pagination="true" data-search="true" data-show-columns="true"
                    data-show-refresh="true" data-show-export="true" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-box-open"></i> Batch Details
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="detail-body">
                    <!-- Filled via JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        // Formatters
        window.quantityFormatter = (value, row) => {
            if (value <= 0) return '<span class="text-danger font-weight-bold">Empty</span>';
            if (value <= 10) return `<span class="text-warning font-weight-bold">${value}</span>`;
            return value;
        };

        window.expiryFormatter = (value, row) => {
            if (!value) return '<em class="text-muted">No expiry</em>';
            const days = row.days_until_expiry;
            if (row.is_expired)
                return `<span class="badge badge-expired">Expired</span>`;
            if (days <= 30)
                return `<span class="badge badge-near-expiry">Expires in ${days} days</span>`;
            return value;
        };

        window.statusFormatter = (value, row) => {
            const badges = [];
            if (row.is_expired) badges.push('<span class="badge badge-expired me-1">Expired</span>');
            if (row.is_near_expiry) badges.push('<span class="badge badge-near-expiry me-1">Near Expiry</span>');
            if (row.quantity <= 10) badges.push('<span class="badge badge-low me-1">Low Stock</span>');
            return badges.length ? badges.join(' ') : '<span class="text-success">Good</span>';
        };

        window.actionFormatter = (value, row) => {
            return `<button class="btn btn-sm btn-info" onclick="showDetail(${row.id})" title="View Details">
                        <i class="fa fa-eye"></i>
                    </button>`;
        };

        function showDetail(id) {
            $.get(`{{ url('inventories') }}/${id}`)
                .done(res => {
                    if (res.code !== 200 || !res.content) {
                        toastr.error('Failed to load batch details');
                        return;
                    }
                    const i = res.content;
                    const exp = i.expiration_date || 'No expiry date';
                    const days = i.days_until_expiry !== null ? i.days_until_expiry : '—';
                    const status = i.is_expired ? 'Expired' : (i.is_near_expiry ? 'Near Expiry' : 'Valid');

                    $('#detail-body').html(`
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr><th>Product Code</th><td>${i.product.product_code}</td></tr>
                                    <tr><th>Product Name</th><td><strong>${i.product.product_name}</strong></td></tr>
                                    <tr><th>Brand</th><td>${i.product.brand}</td></tr>
                                    <tr><th>Unit</th><td>${i.product.unit}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr><th>Current Quantity</th><td><strong class="${i.quantity <= 10 ? 'text-danger' : ''}">${i.quantity}</strong></td></tr>
                                    <tr><th>Batch No</th><td>${i.batch_no || '<em>—</em>'}</td></tr>
                                    <tr><th>Expiration Date</th><td>${exp}</td></tr>
                                    <tr><th>Days Until Expiry</th><td>${days}</td></tr>
                                    <tr><th>Status</th><td>${status}</td></tr>
                                </table>
                            </div>
                        </div>
                        <hr>
                        <small class="text-muted">
                            Added: ${i.created_at}<br>
                            Last Updated: ${i.updated_at}
                        </small>
                    `);
                    $('#detailModal').modal('show');
                })
                .fail(() => toastr.error('Failed to fetch batch details'));
        }

        $(function() {
            const $table = $('#table');

            $table.bootstrapTable({
                columns: [{
                        field: 'id',
                        title: '#',
                        width: 70,
                        sortable: true
                    },
                    {
                        field: 'product.product_code',
                        title: 'Code',
                        sortable: true
                    },
                    {
                        field: 'product.product_name',
                        title: 'Product',
                        sortable: true
                    },
                    {
                        field: 'product.brand',
                        title: 'Brand'
                    },
                    {
                        field: 'quantity',
                        title: 'Qty',
                        formatter: 'quantityFormatter',
                        align: 'center',
                        width: 90
                    },
                    {
                        field: 'batch_no',
                        title: 'Batch No',
                        formatter: v => v || '<em>—</em>'
                    },
                    {
                        field: 'expiration_date',
                        title: 'Expiry',
                        formatter: 'expiryFormatter',
                        align: 'center'
                    },
                    {
                        field: 'status',
                        title: 'Status',
                        formatter: 'statusFormatter',
                        align: 'center',
                        width: 180
                    },
                    {
                        field: 'created_at',
                        title: 'Added On',
                        formatter: v => v.split(' ')[0],
                        sortable: true
                    },
                    {
                        field: 'action',
                        title: 'Action',
                        formatter: 'actionFormatter',
                        align: 'center',
                        width: 80
                    }
                ],
                sidePagination: 'server',
                pagination: true,
                pageSize: 15,
                pageList: [15, 25, 50, 100],
                search: true,
                showColumns: true,
                showRefresh: true,
                showExport: true,
                stickyHeader: true,
                formatNoMatches: () =>
                    '<div class="text-center p-5 text-muted">No inventory batches found.</div>',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border"></div> Loading inventory...</div>',
                queryParams: function(params) {

                    // map frontend sort fields to backend allowed fields
                    const sortMap = {
                        'product.product_name': 'product_name',
                        'product.product_code': 'product_code',
                        'product.brand': 'brand',
                        // add more if needed
                    };

                    return {
                        limit: params.limit,
                        page: (params.offset / params.limit) + 1,
                        search: params.search || '',
                        sort: sortMap[params.sort] || params.sort || '',
                        order: params.order || 'desc',
                        expired: params.expired || '',
                        near_expiry: params.near_expiry || '',
                        low_stock: params.low_stock || ''
                    };
                },
                responseHandler: res => ({
                    total: res.pagination.total,
                    rows: res.rows
                })
            });

            // Custom Search
            $('#search-btn, #custom-search').on('click keyup', function(e) {
                if (e.type === 'keyup' && e.which !== 13) return;
                $table.bootstrapTable('refresh');
            });

            // Quick Filters
            $('#filter-expired').on('click', function() {
                $table.bootstrapTable('refresh', {
                    query: {
                        expired: 1,
                        near_expiry: '',
                        low_stock: ''
                    }
                });
            });
            $('#filter-near-expiry').on('click', function() {
                $table.bootstrapTable('refresh', {
                    query: {
                        near_expiry: 1,
                        expired: '',
                        low_stock: ''
                    }
                });
            });
            $('#filter-low-stock').on('click', function() {
                $table.bootstrapTable('refresh', {
                    query: {
                        low_stock: 1,
                        expired: '',
                        near_expiry: ''
                    }
                });
            });
        });
    </script>
@endsection
