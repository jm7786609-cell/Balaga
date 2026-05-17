@extends('layouts.master')

@section('APP-TITLE', 'Stock In Management')
@section('active-stock-in', 'active')
@section('APP-SUBTITLE', 'Receive New Stock & View History')

@section('APP-STYLES')
    <style>
        #toolbar .btn-primary {
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
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select id="filter-product" class="form-control">
                            <option value="">All Products</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->product_code }} -
                                    {{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" id="filter-date-from" class="form-control" placeholder="From Date">
                    </div>
                    <div class="col-md-3">
                        <input type="date" id="filter-date-to" class="form-control" placeholder="To Date">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info text-white" id="apply-filters">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                        <button class="btn btn-secondary ms-2" id="clear-filters">
                            <i class="fa fa-times"></i> Clear
                        </button>
                    </div>
                </div>
                <div id="toolbar" class="mb-3">
                    <button class="btn btn-primary shadow-sm" id="add-new-btn">
                        <i class="fa fa-plus"></i> Receive New Stock
                    </button>
                </div>

                <table id="table" data-toolbar="#toolbar" data-url="{{ route('stockIns.index') }}"
                    data-side-pagination="server" data-pagination="true" data-show-columns="true" data-show-refresh="true"
                    data-show-export="true" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>

    <!-- Add Stock In Modal -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="addForm" class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fa fa-truck-loading"></i> Receive New Stock
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-control" required>
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_code }} -
                                        {{ $product->product_name }} ({{ $product->product_brand }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label>Lot No <span class="text-danger">*</span></label>
                            <input type="text" name="lot_number" class="form-control" placeholder="Lot 001" required>
                        </div>
                        <div class="col-md-6">
                            <label>Batch No <span class="text-danger">*</span></label>
                            <input type="text" name="batch_no" class="form-control" placeholder="B230401" required>
                        </div>
                        <div class="col-md-6">
                            <label>Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" name="delivery_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Expiration Date (Optional)</label>
                            <input type="date" name="expiration_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Receive Stock
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        // Formatters
        window.dateFormatter = value => value || '<em class="text-muted">—</em>';
        window.expiryFormatter = value => value ? value : '<em class="text-muted">No Expiry</em>';
        window.receivedByFormatter = value => value?.name || '<em class="text-muted">System</em>';
        window.eoqFormatter = value => value ? '<span class="badge bg-success">Yes</span>' :
            '<span class="badge bg-warning">No</span>';

        $(function() {
            const $table = $('#table');
            $table.bootstrapTable({
                columns: [{
                        field: 'id',
                        title: '#',
                        sortable: true,
                        width: 80
                    },
                    {
                        field: 'product.product_name',
                        title: 'Product'
                    },
                    {
                        field: 'product.generic_name',
                        title: 'Generic'
                    },
                    {
                        field: 'product.brand',
                        title: 'Brand'
                    },
                    {
                        field: 'quantity',
                        title: 'Quantity',
                        align: 'center'
                    },
                    {
                        field: 'lot_number',
                        title: 'Lot No'
                    },
                    {
                        field: 'batch_no',
                        title: 'Batch No'
                    },
                    {
                        field: 'delivery_date',
                        title: 'Delivery',
                        formatter: 'expiryFormatter',
                        align: 'center'
                    },
                    {
                        field: 'expiration_date',
                        title: 'Expiry',
                        formatter: 'expiryFormatter',
                        align: 'center'
                    },
                    // {
                    //     field: 'received_by',
                    //     title: 'Received By',
                    //     formatter: 'receivedByFormatter'
                    // },
                    // {
                    //     field: 'received_at',
                    //     title: 'Received At',
                    //     formatter: 'dateFormatter',
                    //     sortable: true
                    // },
                    {
                        field: 'eoq_recalculated',
                        title: 'EOQ Recalc',
                        formatter: 'eoqFormatter',
                        align: 'center',
                        width: 120
                    }
                ],
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                pageList: [20, 50, 100],
                showColumns: true,
                showRefresh: true,
                showExport: true,
                stickyHeader: true,
                formatNoMatches: () =>
                    '<div class="text-center p-5 text-muted">No stock-in records found.<br><small>Receive your first stock to get started!</small></div>',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div> Loading records...</div>',
                queryParams: params => ({
                    limit: params.limit,
                    page: Math.floor(params.offset / params.limit) + 1,
                    product_id: $('#filter-product').val() || '',
                    date_from: $('#filter-date-from').val() || '',
                    date_to: $('#filter-date-to').val() || ''
                }),
                responseHandler: res => ({
                    total: res.pagination.total,
                    rows: res.rows
                })
            });

            $('#add-new-btn').on('click', () => {
                $('#addForm')[0].reset();
                $('#addModal').modal('show');
            });

            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.post({
                    url: '{{ route('stockIns.store') }}',
                    data: $(this).serialize(),
                    success: () => {
                        $('#addModal').modal('hide');
                        $table.bootstrapTable('refresh');
                        toastr.success('Stock received successfully');
                    },
                    error: xhr => toastr.error(xhr.responseJSON?.message ||
                        'Failed to receive stock')
                });
            });

            $('#apply-filters').on('click', () => $table.bootstrapTable('refresh'));
            $('#clear-filters').on('click', () => {
                $('#filter-product').val('');
                $('#filter-date-from').val('');
                $('#filter-date-to').val('');
                $table.bootstrapTable('refresh');
            });
        });
    </script>
@endsection
