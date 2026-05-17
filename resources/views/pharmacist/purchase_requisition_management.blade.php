@extends('layouts.master')

@section('APP-TITLE', 'Purchase Requisitions')
@section('active-purchase-requisitions', 'active')
@section('APP-SUBTITLE', 'Manage Purchase Requisitions')

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
            color: #495057;
        }

        .badge-status {
            font-size: 0.85em;
            padding: 6px 12px;
            border-radius: 50px;
        }

        .item-row {
            border-bottom: 1px solid #eee;
            padding: 12px 0;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        /* Elegant View Modal Styles */
        .view-info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }

        .view-info-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.95rem;
        }

        .view-info-value {
            font-size: 1.05rem;
            color: #212529;
        }

        #view-items-table {
            margin-top: 10px;
        }

        #view-items-table th {
            background-color: #e9ecef;
            font-weight: 600;
        }
    </style>
@endsection

@section('APP-CONTENT')
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h4 class="card-title mb-0 text-primary">@yield('APP-SUBTITLE')</h4>
            </div>
            <div class="card-body">
                <div id="toolbar">
                    <button class="btn btn-primary shadow-sm" id="add-new-btn">
                        <i class="fa fa-plus"></i> Create New Requisition
                    </button>
                </div>

                <table id="table" data-toolbar="#toolbar" data-url="{{ route('purchaseRequisitions.index') }}"
                    data-side-pagination="server" data-pagination="true" data-search="true" data-show-columns="true"
                    data-show-refresh="true" data-show-export="true" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>

    <!-- Create Requisition Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form id="addForm" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-file-alt"></i> Create New Purchase Requisition
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-control select2" required>
                                    <option value="">Select Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Requested By</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->email }}" readonly>
                                <input type="hidden" name="requested_by" value="{{ auth()->id() }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes <small class="text-muted">(Optional)</small></label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Internal notes or special instructions"></textarea>
                    </div>

                    <hr>

                    <h6 class="mb-3"><i class="fa fa-list"></i> Requisition Items</h6>

                    <div id="items-container">
                        <div class="item-row row align-items-end mb-3">
                            <div class="col-md-5">
                                <label>Product <span class="text-danger">*</span></label>
                                <select class="form-control select2 product-select" name="items[0][product_id]" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->product_name }}
                                            ({{ $product->product_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Requested Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="items[0][requested_quantity]"
                                    min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label>Suggested Price <small>(Optional)</small></label>
                                <input type="number" step="0.01" class="form-control" name="items[0][suggested_price]"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-item" disabled>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-item-btn" class="btn btn-outline-secondary btn-sm mt-2">
                        <i class="fa fa-plus"></i> Add Another Item
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Requisition</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Elegant View Requisition Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fa fa-eye mr-2"></i> Purchase Requisition Details
                    </h5>
                    <div>
                        <button type="button" class="btn btn-light btn-sm mr-2 shadow-sm" id="print-requisition-btn">
                            <i class="fa fa-print"></i> Print
                        </button>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <!-- Requisition Summary Card -->
                    <div class="view-info-card">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="view-info-label"><i class="fa fa-building mr-2 text-info"></i> Supplier</div>
                                <div class="view-info-value" id="view-supplier">-</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="view-info-label"><i class="fa fa-user mr-2 text-info"></i> Requested By</div>
                                <div class="view-info-value" id="view-requested-by">-</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="view-info-label"><i class="fa fa-calendar-alt mr-2 text-info"></i> Date
                                    Created</div>
                                <div class="view-info-value" id="view-created-at">-</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="view-info-label"><i class="fa fa-tag mr-2 text-info"></i> Status</div>
                                <div id="view-status" class="mt-1"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="view-info-label"><i class="fa fa-check-circle mr-2 text-info"></i> Approved By
                                </div>
                                <div class="view-info-value" id="view-approved-by">N/A</div>
                            </div>
                            <div class="col-md-4">
                                <div class="view-info-label"><i class="fa fa-clock mr-2 text-info"></i> Approved At</div>
                                <div class="view-info-value" id="view-approved-at">N/A</div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-secondary"><i class="fa fa-sticky-note mr-2"></i> Notes</h6>
                        <p class="text-muted bg-light p-3 rounded" id="view-notes">None</p>
                    </div>

                    <!-- Supplier Response -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-secondary"><i class="fa fa-reply mr-2"></i> Supplier Response
                        </h6>
                        <p class="text-muted bg-light p-3 rounded" id="view-supplier-response">N/A</p>
                    </div>

                    <!-- Items Table -->
                    <h6 class="font-weight-bold text-secondary mb-3"><i class="fa fa-list-alt mr-2"></i> Requisition Items
                    </h6>
                    <div class="table-responsive">
                        <table id="view-items-table" data-toggle="table"
                            data-classes="table table-bordered table-hover table-striped" data-pagination="false"
                            data-search="false" data-show-columns="false" data-show-refresh="false">
                            <thead class="thead-light">
                                <tr>
                                    <th data-field="no" data-formatter="rowNumberFormatter" data-width="50">#</th>
                                    <th data-field="product_name" data-width="40%">Product</th>
                                    <th data-field="requested_quantity" data-align="center">Requested Qty</th>
                                    <th data-field="suggested_price" data-align="right" data-formatter="priceFormatter">
                                        Suggested Price</th>
                                    <th data-field="approved_quantity" data-align="center"
                                        data-formatter="nullFormatter">Approved Qty</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <small class="text-muted">Requisition ID: <span id="view-id-display">-</span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Requisition Modal (unchanged structure, cleaned) -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form id="editForm" class="modal-content">
                @method('PUT')
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-edit"></i> Edit Purchase Requisition
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-control select2" required>
                                    <option value="">Select Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Requested By</label>
                                <input type="text" class="form-control" id="edit-requested-by" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes <small class="text-muted">(Optional)</small></label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <hr>

                    <h6 class="mb-3"><i class="fa fa-list"></i> Requisition Items</h6>
                    <div id="edit-items-container"></div>
                    <button type="button" id="edit-add-item-btn" class="btn btn-outline-secondary btn-sm mt-2">
                        <i class="fa fa-plus"></i> Add Another Item
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        const products = @json($products);
        const suppliers = @json($suppliers);
        let currentRequisitionData = null;
        let itemIndex = 1;
        let editItemIndex = 0;

        // Formatters
        function rowNumberFormatter(value, row, index) {
            return index + 1;
        }

        function priceFormatter(value) {
            return value !== null ? parseFloat(value).toFixed(2) : 'N/A';
        }

        function nullFormatter(value) {
            return value !== null ? value : 'N/A';
        }

        function statusFormatter(value) {
            const badges = {
                pending: 'badge-warning',
                approved: 'badge-success',
                rejected: 'badge-danger'
            };
            const badge = badges[value] || 'badge-secondary';
            return `<span class="badge ${badge} badge-status text-uppercase">${value}</span>`;
        }

        function dateFormatter(value) {
            return value ? new Date(value).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : '';
        }

        function actionFormatter(value, row) {
            let buttons = `
        <button class="btn btn-sm btn-info view-btn" data-id="${row.id}" title="View">
            <i class="fa fa-eye"></i>
        </button>`;

            if (row.status === 'pending') {
                buttons = `
            <button class="btn btn-sm btn-warning edit-btn mr-1" data-id="${row.id}" title="Edit">
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger delete-btn mr-1" data-id="${row.id}" title="Delete">
                <i class="fa fa-trash"></i>
            </button>` + buttons;
            }

            return buttons;
        }

        // Print Function (kept clean)
        function myCustomPrintRequisition(tableHtml, title) {
            const companyLogo = "{{ asset('images/logo.png') }}"; // Adjust path if needed

            return `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>{{ env('APP_NAME') }} | ${title}</title>
            <style type="text/css" media="all">
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .report-header {
                    display: flex; align-items: center; justify-content: space-between;
                    border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 30px;
                }
                .header-left img { max-width: 120px; max-height: 120px; }
                .header-center { text-align: center; flex: 1; }
                .header-center h1 { margin: 0; font-size: 28px; }
                .header-center h3 { margin: 5px 0 0; font-size: 16px; font-weight: normal; }
                .report-title { text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0; }
                .info-table { width: 100%; margin-bottom: 30px; }
                .info-table td { padding: 8px; vertical-align: top; }
                .info-label { font-weight: bold; width: 180px; }
                table.items-table {
                    width: 100%; border-collapse: collapse; font-size: 13px;
                }
                table.items-table th, table.items-table td {
                    border: 1px solid #000; padding: 10px; text-align: center;
                }
                table.items-table th { background-color: #f0f0f0; }
                .text-left { text-align: left !important; }
                .text-right { text-align: right !important; }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            <div class="report-header">
                <div class="header-left">
                    <img src="${companyLogo}" alt="Logo">
                </div>
                <div class="header-center">
                    <h1><b>{{ env('APP_NAME') }}</b></h1>
                    <h3>Pharmacy Management System</h3>
                </div>
                <div></div>
            </div>

            <div class="report-title">${title}</div>

            <table class="info-table">
                <tr>
                    <td class="info-label">Requisition ID:</td>
                    <td>${currentRequisitionData.id}</td>
                    <td class="info-label">Date Created:</td>
                    <td>${currentRequisitionData.created_at}</td>
                </tr>
                <tr>
                    <td class="info-label">Supplier:</td>
                    <td>${currentRequisitionData.supplier}</td>
                    <td class="info-label">Requested By:</td>
                    <td>${currentRequisitionData.requested_by}</td>
                </tr>
                <tr>
                    <td class="info-label">Status:</td>
                    <td>${currentRequisitionData.status.toUpperCase()}</td>
                    <td class="info-label">Approved By:</td>
                    <td>${currentRequisitionData.approved_by || 'N/A'}</td>
                </tr>
                <tr>
                    <td class="info-label">Notes:</td>
                    <td colspan="3">${currentRequisitionData.notes || 'None'}</td>
                </tr>
            </table>

            <h3 style="text-align: center;">Requisition Items</h3>

            ${tableHtml}

        </body>
        </html>
    `;
        }

        $(function() {
            // Main table initialization
            $('#table').bootstrapTable({
                columns: [{
                        field: 'id',
                        title: 'ID',
                        sortable: true
                    },
                    {
                        field: 'supplier',
                        title: 'Supplier',
                        sortable: true
                    },
                    {
                        field: 'requested_by',
                        title: 'Requested By',
                        sortable: true
                    },
                    {
                        field: 'status',
                        title: 'Status',
                        formatter: 'statusFormatter',
                        sortable: true
                    },
                    {
                        field: 'created_at',
                        title: 'Created At',
                        formatter: 'dateFormatter',
                        sortable: true
                    },
                    {
                        field: 'action',
                        title: 'Action',
                        formatter: 'actionFormatter',
                        align: 'center',
                        width: 100
                    }
                ],
                sidePagination: 'server',
                pagination: true,
                pageSize: 10,
                pageList: [10, 25, 50, 100],
                search: true,
                showColumns: true,
                showRefresh: true,
                showExport: true,
                stickyHeader: true,
                formatNoMatches: () =>
                    '<div class="text-center p-5 text-muted">No requisitions found. Create one to get started!</div>',
                queryParams: params => ({
                    limit: params.limit,
                    page: (params.offset / params.limit) + 1,
                    search: params.search || '',
                    sort: params.sort || '',
                    order: params.order || ''
                }),
                responseHandler: res => ({
                    total: res.pagination?.total || 0,
                    rows: res.rows || []
                })
            });

            // Initialize Select2 inside modal
            $('#add-new-btn').on('click', function() {
                $('#addForm')[0].reset();
                $('#items-container').html(`
                    <div class="item-row row align-items-end mb-3">
                        <div class="col-md-5">
                            <label>Product <span class="text-danger">*</span></label>
                            <select class="form-control select2 product-select" name="items[0][product_id]" required>
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }} ({{ $product->product_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Requested Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="items[0][requested_quantity]" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label>Suggested Price <small>(Optional)</small></label>
                            <input type="number" step="0.01" class="form-control" name="items[0][suggested_price]">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm remove-item" disabled><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                `);
                $('#items-container .product-select').select2({
                    dropdownParent: $('#addModal')
                });
                itemIndex = 1;
                $('#addModal').modal('show');
            });

            // Add new item row
            $(document).on('click', '#add-item-btn', function() {
                const row = `
                    <div class="item-row row align-items-end mb-3">
                        <div class="col-md-5">
                            <label>Product <span class="text-danger">*</span></label>
                            <select class="form-control select2 product-select" name="items[${itemIndex}][product_id]" required>
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }} ({{ $product->product_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Requested Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="items[${itemIndex}][requested_quantity]" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label>Suggested Price <small>(Optional)</small></label>
                            <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][suggested_price]">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>`;
                $('#items-container').append(row);
                $(`select[name="items[${itemIndex}][product_id]"]`).select2({
                    dropdownParent: $('#addModal')
                });
                itemIndex++;
            });

            // Remove item
            $(document).on('click', '.remove-item:not(:disabled)', function() {
                $(this).closest('.item-row').remove();
            });

            // Submit form
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route('purchaseRequisitions.store') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        $('#addModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('Purchase requisition created successfully');
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let msg = '';
                            $.each(errors, (key, val) => msg += val[0] + '<br>');
                            toastr.error(msg);
                        } else {
                            toastr.error(xhr.responseJSON?.message ||
                                'Failed to create requisition');
                        }
                    }
                });
            });

            $(document).on('click', '.view-btn', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: '{{ route('purchaseRequisitions.index') }}/' + id,
                    method: 'GET',
                    success: function(response) {
                        const data = response.content;
                        currentRequisitionData = data;

                        $('#view-supplier').text(data.supplier);
                        $('#view-requested-by').text(data.requested_by);
                        $('#view-created-at').text(data.created_at);
                        $('#view-notes').text(data.notes || 'None');
                        $('#view-status').html(statusFormatter(data.status));
                        $('#view-approved-by').text(data.approved_by || 'N/A');
                        $('#view-approved-at').text(data.approved_at || 'N/A');
                        $('#view-supplier-response').text(data.supplier_response || 'N/A');
                        $('#view-id-display').text(data.id);

                        $('#view-items-table').bootstrapTable('load', data.items.map((item,
                            i) => ({
                            no: i + 1,
                            product_name: item.product_name ||
                                'Unknown Product',
                            requested_quantity: item.requested_quantity,
                            suggested_price: item.suggested_price,
                            approved_quantity: item.approved_quantity
                        })));

                        $('#viewModal').modal('show');
                    }
                });
            });

            $(document).on('click', '#print-requisition-btn', function() {
                if (!currentRequisitionData) return;

                const $table = $('#view-items-table');
                let tableHtml = '<table class="items-table">';

                // Header
                tableHtml += '<thead><tr>';
                $table.find('thead th').each(function() {
                    tableHtml += `<th>${$(this).text()}</th>`;
                });
                tableHtml += '</tr></thead><tbody>';

                // Rows
                $table.find('tbody tr').each(function() {
                    tableHtml += '<tr>';
                    $(this).find('td').each(function() {
                        const align = $(this).css('text-align');
                        const cls = align === 'right' ? 'text-right' : align === 'left' ?
                            'text-left' : '';
                        tableHtml += `<td class="${cls}">${$(this).html()}</td>`;
                    });
                    tableHtml += '</tr>';
                });

                tableHtml += '</tbody></table>';

                const title =
                    `Purchase Requisition #${currentRequisitionData.id} - ${currentRequisitionData.supplier}`;

                const printWindow = window.open('', '_blank');
                printWindow.document.write(myCustomPrint(tableHtml, title));
                printWindow.document.close();
            });

            // Edit button
            let editItemIndex = 0;
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: '{{ route('purchaseRequisitions.index') }}/' + id,
                    method: 'GET',
                    success: function(response) {
                        const data = response.content;

                        // Set requisition ID
                        $('input[name="id"]').val(data.id);

                        // Set Supplier
                        const supplier = suppliers.find(s => s.supplier_name === data.supplier);
                        $('select[name="supplier_id"]').val(supplier ? supplier.id : '')
                            .trigger('change');

                        // Requested By (readonly)
                        $('#edit-requested-by').val(data.requested_by);

                        // Notes
                        $('textarea[name="notes"]').val(data.notes || '');

                        // Clear and rebuild items
                        $('#edit-items-container').empty();
                        editItemIndex = 0;

                        data.items.forEach(item => {
                            const row = `
                    <div class="item-row row align-items-end mb-3">
                        <!-- Hidden ID to tell backend which item to update -->
                        <input type="hidden" name="items[${editItemIndex}][id]" value="${item.id}">

                        <div class="col-md-5">
                            <label>Product <span class="text-danger">*</span></label>
                            <select class="form-control select2 product-select" name="items[${editItemIndex}][product_id]" required>
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" ${item.product_id == {{ $product->id }} ? 'selected' : ''}>
                                        {{ $product->product_name }} ({{ $product->product_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Requested Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="items[${editItemIndex}][requested_quantity]" min="1" required value="${item.requested_quantity}">
                        </div>
                        <div class="col-md-3">
                            <label>Suggested Price <small>(Optional)</small></label>
                            <input type="number" step="0.01" class="form-control" name="items[${editItemIndex}][suggested_price]" value="${item.suggested_price !== null ? item.suggested_price : ''}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm remove-item" ${editItemIndex === 0 && data.items.length === 1 ? 'disabled' : ''}>
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>`;

                            $('#edit-items-container').append(row);
                            editItemIndex++;
                        });

                        // Initialize Select2
                        $('#edit-items-container .product-select').select2({
                            dropdownParent: $('#editModal')
                        });

                        $('#editModal').modal('show');
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ||
                            'Failed to load requisition for editing');
                    }
                });
            });

            // Add new item in edit
            $(document).on('click', '#edit-add-item-btn', function() {
                const row = `
                    <div class="item-row row align-items-end mb-3">
                        <div class="col-md-5">
                            <label>Product <span class="text-danger">*</span></label>
                            <select class="form-control select2 product-select" name="items[${editItemIndex}][product_id]" required>
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }} ({{ $product->product_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Requested Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="items[${editItemIndex}][requested_quantity]" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label>Suggested Price <small>(Optional)</small></label>
                            <input type="number" step="0.01" class="form-control" name="items[${editItemIndex}][suggested_price]">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>`;
                $('#edit-items-container').append(row);
                $(`select[name="items[${editItemIndex}][product_id]"]`).select2({
                    dropdownParent: $('#editModal')
                });
                editItemIndex++;
            });

            // Submit edit form
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('input[name="id"]').val();
                $.ajax({
                    url: '/purchase-requisitions/' + id,
                    method: 'PUT',
                    data: $(this).serialize(),
                    success: function() {
                        $('#editModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('Purchase requisition updated successfully');
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let msg = '';
                            $.each(errors, (key, val) => msg += val[0] + '<br>');
                            toastr.error(msg);
                        } else {
                            toastr.error(xhr.responseJSON?.message ||
                                'Failed to update requisition');
                        }
                    }
                });
            });

            // Delete requisition (only for pending)
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This purchase requisition will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/purchase-requisitions/' + id,
                            method: 'DELETE',
                            success: function(response) {
                                $('#table').bootstrapTable('refresh');
                                toastr.success(
                                    'Purchase requisition deleted successfully');
                            },
                            error: function(xhr) {
                                let message = 'Failed to delete requisition';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                toastr.error(message);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
