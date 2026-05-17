@extends('layouts.master')

@section('APP-TITLE', 'Transaction History')
@section('active-transactions', 'active')
@section('APP-SUBTITLE', 'Completed Sales Records')

@section('APP-STYLES')
    <style>
        #toolbar .btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
        }

        table.table {
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(6px);
            border-radius: 12px;
        }

        .fixed-table-container {
            border-radius: 12px !important;
        }

        .receipt-no {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 1.15em;
            color: #1e3a8a;
        }

        .modal-content {
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }

        .badge-success {
            background: #10b981 !important;
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
                <table id="transactionsTable" data-toolbar="#toolbar" data-url="/transactions" data-side-pagination="server"
                    data-pagination="true" data-page-list="[10, 25, 50, 100]" data-page-size="15" data-search="true"
                    data-search-align="left" data-show-refresh="true" data-show-columns="true" data-show-export="true"
                    data-export-types="['csv','excel']" data-sticky-header="true" data-sticky-header-offset-y="0"
                    data-query-params="queryParams" data-response-handler="responseHandler" class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th data-field="receipt_no" data-sortable="true" data-formatter="receiptFormatter">Receipt No.
                            </th>
                            <th data-field="cashier.name" data-sortable="true">Cashier</th>
                            <th data-field="created_at" data-sortable="true" data-formatter="dateFormatter">Sale Date</th>
                            <th data-field="items_count" data-align="center" data-sortable="true">Items</th>
                            <th data-field="total_amount" data-align="right" data-formatter="priceFormatter">Total</th>
                            <th data-field="amount_paid" data-align="right" data-formatter="priceFormatter">Paid</th>
                            <th data-field="change_due" data-align="right" data-formatter="priceFormatter">Change</th>
                            <th data-field="action" data-align="center" data-formatter="actionFormatter" data-width="100">
                                Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- View Receipt Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        Receipt Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="receiptDetails">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-3">Loading receipt...</p>
                    </div>
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
        // Query params for server-side
        function queryParams(params) {
            return {
                page: Math.floor(params.offset / params.limit) + 1,
                per_page: params.limit,
                search: params.search || '',
                sort: params.sort || '',
                order: params.order || 'asc'
            };
        }

        // Map backend response
        function responseHandler(res) {
            return {
                total: res.pagination.total,
                rows: res.rows
            };
        }

        // Formatters
        window.receiptFormatter = (value, row) => `<span class="receipt-no">${row.receipt_no}</span>`;
        window.dateFormatter = (value, row) => `
        <div><strong>${row.created_at}</strong></div>
        <small class="text-muted">Completed: ${row.completed_at || '—'}</small>
    `;
        window.priceFormatter = value => '₱' + parseFloat(value || 0).toFixed(2);
        window.actionFormatter = (value, row) => `
        <button class="btn btn-sm btn-info shadow-sm" onclick="viewReceipt(${row.id})" title="View Receipt">
            <i class="fa fa-eye"></i> View
        </button>
    `;

        // View Receipt Modal
        function viewReceipt(id) {
            $('#viewModal').modal('show');
            $('#receiptDetails').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3">Loading receipt...</p>
            </div>
        `);

            $.get(`/transactions/${id}`)
                .done(res => {
                    if (res.code !== 200 || !res.content) {
                        $('#receiptDetails').html('<div class="alert alert-danger">Failed to load receipt.</div>');
                        return;
                    }

                    const t = res.content;
                    let itemsHtml = '';
                    t.items.forEach(item => {
                        itemsHtml += `
                        <tr>
                            <td>${item.quantity}x</td>
                            <td>${item.product.name} <small class="text-muted">(${item.product.brand})</small></td>
                            <td class="text-end">₱${parseFloat(item.price).toFixed(2)}</td>
                            <td class="text-end">₱${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                    });

                    $('#receiptDetails').html(`
                    <div class="row">
                        <div class="col-12 text-center mb-4">
                            <h4 class="receipt-no">${t.receipt_no}</h4>
                            <p><strong>Date:</strong> ${t.completed_at || t.created_at}</p>
                            <p><strong>Cashier:</strong> ${t.cashier.name}</p>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Qty</th>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                    </table>
                    <div class="text-end mt-3">
                        <h5>Total Amount: <strong>₱${parseFloat(t.total_amount).toFixed(2)}</strong></h5>
                        <h5>Amount Paid: <strong class="text-success">₱${parseFloat(t.amount_paid).toFixed(2)}</strong></h5>
                        <h5>Change: <strong class="text-primary">₱${parseFloat(t.change_due).toFixed(2)}</strong></h5>
                    </div>
                    <div class="text-center mt-4 text-muted"><em>Thank you for your purchase!</em></div>
                `);
                })
                .fail(() => $('#receiptDetails').html('<div class="alert alert-danger">Error loading receipt.</div>'));
        }

        $(function() {
            const $table = $('#transactionsTable');

            // No records message
            const noDataMsg = `
            <div class="text-center py-5 text-muted">
                <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
                <h5>No transactions yet</h5>
                <p>Complete your first sale to see it here!</p>
            </div>
        `;

            $table.bootstrapTable({
                formatNoMatches: () => noDataMsg,
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border"></div> Loading transactions...</div>'
            });
        });
    </script>
@endsection
