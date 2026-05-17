@extends('layouts.master')

@section('APP-TITLE', 'Purchase Requisitions')
@section('active-requisitions', 'active')
@section('APP-SUBTITLE', 'Approve or Reject Requisitions')

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

        .badge-status {
            font-size: 0.85em;
            padding: 6px 12px;
            border-radius: 50px;
        }

        .item-row {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
        }
    </style>
@endsection

@section('APP-CONTENT')
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">@yield('APP-SUBTITLE')</h4>
                <span class="text-muted">Only requisitions addressed to you are shown</span>
            </div>
            <div class="card-body">
                <div id="toolbar">
                    <select id="status-filter" class="form-control w-auto d-inline-block mr-2">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <table id="table" data-toolbar="#toolbar" data-url="{{ route('purchaseRequisitions.index') }}"
                    data-side-pagination="server" data-pagination="true" data-search="true" data-show-columns="true"
                    data-show-refresh="true" data-show-export="true" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>

    <!-- Review & Approve/Reject Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form id="reviewForm" class="modal-content">
                @csrf
                @method('PUT')
                <input type="hidden" id="requisition_id">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-file-contract"></i> Review Purchase Requisition
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Requisition ID:</strong> <span id="req-id-display"></span></p>
                            <p><strong>Requested By:</strong> <span id="req-requested-by"></span></p>
                            <p><strong>Date Created:</strong> <span id="req-created-at"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Current Status:</strong> <span id="req-status-badge"></span></p>
                            <p><strong>Internal Notes:</strong><br><span id="req-notes" class="text-muted"></span></p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3"><i class="fa fa-list"></i> Requested Items</h6>
                    <div id="items-container"></div>

                    <hr>

                    <div class="form-group">
                        <label>Action <span class="text-danger">*</span></label>
                        <select name="status" id="action-select" class="form-control" required>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>

                    <div class="form-group" id="response-group">
                        <label>Supplier Response / Comments <span class="text-danger">*</span></label>
                        <textarea name="supplier_response" class="form-control" rows="4"
                            placeholder="Provide reason for rejection or any notes (e.g., adjusted quantities, availability, pricing)"></textarea>
                    </div>

                    <div id="quantity-adjustment-container" style="display: none;">
                        <h6 class="mb-3">Adjust Approved Quantities (Optional)</h6>
                        <div id="quantity-fields"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" id="submit-btn">
                        <i class="fa fa-check"></i> Submit Decision
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        window.statusFormatter = function(value) {
            const map = {
                pending: ['badge-warning', 'Pending Approval'],
                approved: ['badge-success', 'Approved'],
                rejected: ['badge-danger', 'Rejected'],
                cancelled: ['badge-secondary', 'Cancelled']
            };
            const [badge, text] = map[value] || ['badge-secondary', value];
            return `<span class="badge ${badge} badge-status text-capitalize">${text}</span>`;
        };

        window.dateFormatter = function(value) {
            if (!value) return '—';
            return new Date(value).toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        window.actionFormatter = function(value, row) {
            if (row.status !== 'pending') {
                return '<button class="btn btn-sm btn-secondary" onclick="reviewRequisition(' + row.id +
                    ', true)" title="View"><i class="fa fa-eye"></i></button>';
            }
            return '<button class="btn btn-sm btn-primary" onclick="reviewRequisition(' + row.id +
                ')" title="Review"><i class="fa fa-edit"></i> Review</button>';
        };

        function reviewRequisition(id, readonly = false) {
            $.ajax({
                url: `/purchase-requisitions/${id}`,
                method: 'GET',
                success: function(res) {
                    if (res.code !== 200 || !res.content) {
                        toastr.error(res.message || 'Failed to load requisition');
                        return;
                    }
                    const req = res.content;

                    $('#requisition_id').val(req.id);
                    $('#req-id-display').text('#' + req.id);
                    $('#req-requested-by').text(req.requested_by || 'N/A');
                    $('#req-created-at').text(dateFormatter(req.created_at));
                    $('#req-status-badge').html(statusFormatter(req.status));
                    $('#req-notes').text(req.notes || 'No notes provided');

                    // Items
                    let itemsHtml = '';
                    let quantityFields = '';
                    req.items.forEach((item, i) => {
                        itemsHtml += `
                            <div class="item-row">
                                <div class="row">
                                    <div class="col-md-6"><strong>${item.product_name || 'Product ID ' + item.product_id}</strong></div>
                                    <div class="col-md-3">Requested: <strong>${item.requested_quantity}</strong></div>
                                    <div class="col-md-3">Suggested Price: <strong>${item.suggested_price ? '₱' + Number(item.suggested_price).toFixed(2) : '—'}</strong></div>
                                </div>
                            </div>`;

                        if (!readonly && req.status === 'pending') {
                            quantityFields += `
                                <div class="row mb-2 align-items-end">
                                    <div class="col-md-6">
                                        <label>${item.product_name || 'Product ID ' + item.product_id}</label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" name="items[${i}][id]" value="${item.id}" hidden>
                                        <input type="number" name="items[${i}][approved_quantity]"
                                               class="form-control" min="0" placeholder="Approved qty (leave blank to accept requested)">
                                    </div>
                                </div>`;
                        }
                    });

                    $('#items-container').html(itemsHtml);
                    $('#quantity-fields').html(quantityFields);

                    // Readonly mode
                    if (readonly || req.status !== 'pending') {
                        $('#action-select').prop('disabled', true);
                        $('#response-group').hide();
                        $('#quantity-adjustment-container').hide();
                        $('#submit-btn').hide();
                    } else {
                        $('#action-select').prop('disabled', false);
                        $('#response-group').show();
                        $('#quantity-adjustment-container').show();
                        $('#submit-btn').show();
                    }

                    // Show quantity adjustment only on approve
                    $('#action-select').off('change').on('change', function() {
                        if ($(this).val() === 'approved') {
                            $('#quantity-adjustment-container').show();
                        } else {
                            $('#quantity-adjustment-container').hide();
                        }
                    });

                    $('#reviewModal').modal('show');
                },
                error: () => toastr.error('Failed to load requisition details')
            });
        }

        $(document).ready(function() {
            $('#table').bootstrapTable({
                columns: [{
                        field: 'id',
                        title: '#',
                        sortable: true,
                        width: 80
                    },
                    {
                        field: 'requested_by',
                        title: 'Requested By',
                        sortable: true
                    },
                    {
                        field: 'created_at',
                        title: 'Date',
                        formatter: 'dateFormatter',
                        sortable: true
                    },
                    {
                        field: 'status',
                        title: 'Status',
                        formatter: 'statusFormatter',
                        align: 'center'
                    },
                    {
                        field: 'action',
                        title: 'Action',
                        formatter: 'actionFormatter',
                        align: 'center',
                        width: 150
                    }
                ],
                sidePagination: 'server',
                pagination: true,
                pageSize: 10,
                pageList: [10, 25, 50],
                search: true,
                showColumns: true,
                showRefresh: true,
                showExport: true,
                stickyHeader: true,
                formatNoMatches: () =>
                    '<div class="text-center p-5 text-muted">No requisitions addressed to you yet.</div>',
                queryParams: params => ({
                    limit: params.limit,
                    page: (params.offset / params.limit) + 1,
                    search: params.search || '',
                    sort: params.sort || '',
                    order: params.order || '',
                    status: $('#status-filter').val()
                }),
                responseHandler: res => ({
                    total: res.pagination?.total || 0,
                    rows: res.rows || []
                })
            });

            // Filter by status
            $('#status-filter').on('change', function() {
                $('#table').bootstrapTable('refresh');
            });

            // Submit approval/rejection
            $('#reviewForm').on('submit', function(e) {
                e.preventDefault();
                const status = $('#action-select').val();
                const message = status === 'approved' ? 'approve' : 'reject';

                Swal.fire({
                    title: 'Confirm Action',
                    text: `Are you sure you want to ${message} this requisition?`,
                    icon: status === 'approved' ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, ' + message,
                    confirmButtonColor: status === 'approved' ? '#28a745' : '#dc3545'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/purchase-requisitions/${$('#requisition_id').val()}`,
                            method: 'POST',
                            data: $(this).serialize(),
                            success: function() {
                                $('#reviewModal').modal('hide');
                                $('#table').bootstrapTable('refresh');
                                toastr.success(`Requisition has been ${status}!`);
                            },
                            error: function(xhr) {
                                toastr.error(xhr.responseJSON?.message ||
                                    'Failed to update requisition');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
