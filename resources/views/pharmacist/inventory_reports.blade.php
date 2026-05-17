@extends('layouts.master')

@section('APP-TITLE', 'Inventory Management')
@section('active-inventory', 'active')
@section('APP-SUBTITLE', 'End-of-Day Physical Count Reports')

@section('APP-STYLES')
    <style>
        /* Main date rows */
        .date-row {
            background-color: #f8f9fa !important;
            font-weight: 600;
            font-size: 1.05em;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .date-row:hover {
            background-color: #e9ecef !important;
        }

        .date-row td {
            padding: 14px 16px !important;
            vertical-align: middle;
        }

        /* Detail table */
        #detailTable th {
            background-color: #e9ecef;
            font-weight: 600;
        }

        .system-value {
            color: #495057;
            font-weight: bold;
        }

        .variance-positive {
            color: #28a745;
            font-weight: bold;
        }

        .variance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .variance-zero {
            color: #6c757d;
        }

        /* Buttons */
        .view-btn {
            padding: 6px 14px;
            font-size: 0.9em;
        }
    </style>
@endsection

@section('APP-CONTENT')
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h4 class="card-title mb-0 text-dark">@yield('APP-SUBTITLE')</h4>
            </div>

            <div class="card-body">
                <div id="toolbar" class="mb-3">
                    <button class="btn btn-success shadow-sm" id="add-new-btn">
                        <i class="fa fa-clipboard-check mr-2"></i> Create Today's Report
                    </button>
                </div>

                <table id="mainTable" data-toolbar="#toolbar" data-url="{{ route('inventoryReports.index') }}"
                    data-side-pagination="server" data-pagination="true" data-page-size="15" data-search="false"
                    data-show-columns="false" data-show-refresh="true" data-show-export="false" data-sticky-header="true"
                    class="table table-bordered table-hover">
                </table>
            </div>
        </div>
    </div>

    <!-- View Report Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewModalTitle">
                        <i class="fa fa-eye mr-2"></i> Inventory Report Items
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <table id="detailTable" data-pagination="true" data-page-size="15" data-page-list="[10, 25, 50, 100]"
                        data-search="true" data-show-columns="true" data-show-refresh="true" data-sticky-header="true"
                        data-toggle="table" class="table table-sm table-bordered mb-0">
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Daily Report Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form id="addForm" class="modal-content">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-calendar-day mr-2"></i>
                        Daily Inventory Report — {{ now()->format('F d, Y') }}
                    </h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="report_date" value="{{ now()->format('Y-m-d') }}">

                    {{-- <div class="alert alert-info text-center mb-4">
                        <i class="fa fa-info-circle mr-2"></i>
                        Enter the <strong>actual physical count</strong> for each item below.<br>
                        System-calculated ending inventory is shown for reference.
                    </div> --}}

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="items-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="30%">Item</th>
                                    <th width="25%" class="text-center">Beginning Inventory</th>
                                    <th width="25%" class="text-center">Ending Inventory</th>
                                    <th width="20%">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Items loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success px-5">
                        <i class="fa fa-save mr-2"></i> Submit Report
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        function tableIndexFormatter(value, row, index, field) {
            // Get current page and pagination info
            const pageInfo = $('#mainTable').bootstrapTable('getOptions');
            const pageNumber = pageInfo.pageNumber || 1;
            const pageSize = pageInfo.pageSize || 15;

            // Calculate actual row number
            return (pageNumber - 1) * pageSize + index + 1;
        }

        $(document).ready(function() {
            const today = '{{ now()->format('Y-m-d') }}';

            // === MAIN TABLE: One row per inventory date ===
            $('#mainTable').bootstrapTable({
                columns: [{
                        field: 'operate',
                        formatter: 'tableIndexFormatter',
                        title: '#',
                        align: 'center',
                        width: 60
                    },
                    {
                        field: 'display_date',
                        title: 'Inventory Date',
                        sortable: false
                    },
                    {
                        field: 'created_by',
                        title: 'Created By'
                    },
                    {
                        field: 'action',
                        title: 'Action',
                        formatter: actionFormatter,
                        align: 'center',
                        width: 120
                    }
                ],
                responseHandler: function(res) {
                    const rows = res.rows || [];
                    const grouped = {};

                    rows.forEach(row => {
                        const isoDate = row.report_date; // "2025-12-22"
                        const creator = row.created_by || 'Unknown';

                        if (!grouped[isoDate]) {
                            grouped[isoDate] = {
                                report_date: isoDate,
                                display_date: row.display_date || isoDate,
                                created_by: creator
                            };
                        }
                    });

                    const uniqueDates = Object.values(grouped)
                        .sort((a, b) => b.report_date.localeCompare(a.report_date));

                    return {
                        total: uniqueDates.length,
                        rows: uniqueDates
                    };
                },
                rowStyle: () => ({
                    classes: 'date-row'
                })
            });

            // View button with correct date
            function actionFormatter(value, row) {
                return `
                    <button class="btn btn-primary btn-sm view-btn" data-date="${row.report_date}">
                        <i class="fa fa-eye mr-1"></i> View
                    </button>
                `;
            }

            // Click handlers
            $(document).on('click', '.view-btn', function(e) {
                e.stopPropagation();
                const isoDate = $(this).data('date');
                openViewModal(isoDate);
            });

            $(document).on('click', '#mainTable tr.date-row', function() {
                const isoDate = $(this).find('.view-btn').data('date');
                if (isoDate) openViewModal(isoDate);
            });

            // === DETAIL MODAL: Full item list for selected date ===
            function openViewModal(isoDate) {
                if (!isoDate) {
                    toastr.error('Invalid date selected');
                    return;
                }

                const dateObj = new Date(isoDate);
                const formatted = dateObj.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                $('#viewModalTitle').html(`<i class="fa fa-eye mr-2"></i> Inventory Report Items — ${formatted}`);
                $('#viewModal').modal('show');

                $('#detailTable').bootstrapTable('destroy').bootstrapTable({
                    url: '{{ route('inventoryReports.index') }}',
                    queryParams: params => {
                        params.report_date = isoDate;
                        params.limit = params.limit || 15;
                        return params;
                    },
                    columns: [{
                            field: 'product.product_name',
                            title: 'Item',
                            formatter: (value, row) => `
                                <strong>${row.product.product_name}</strong><br>
                                <small class="text-muted">${row.product.product_brand} - ${row.product.product_code}</small>
                            `
                        },
                        {
                            field: 'beginning_inventory',
                            title: 'Beginning Inventory',
                            align: 'center'
                        },
                        {
                            field: 'ending_inventory',
                            title: 'Ending Inventory',
                            align: 'center',
                            class: 'system-value'
                        },
                        {
                            field: 'remarks',
                            title: 'Remarks',
                            formatter: v => v || '<em class="text-muted">None</em>'
                        }
                    ],
                    sidePagination: 'server',
                    pagination: true,
                    pageSize: 15,
                    pageList: [10, 25, 50, 100],
                    search: true,
                    showColumns: true,
                    showRefresh: true,
                    stickyHeader: true,
                    showPrint: true,
                    formatNoMatches: () =>
                        '<div class="text-center text-muted py-4">No items recorded for this date.</div>',
                    printPageBuilder: function printPageBuilder(table) {
                        return myCustomPrint(table, 'Inventory Report Items');
                    }
                });
            }

            // === CREATE TODAY'S REPORT ===
            $('#add-new-btn').on('click', function() {
                $('#items-table tbody').empty();
                $('#addModal').modal('show');

                $.ajax({
                    url: '{{ route('inventoryReports.create') }}',
                    data: {
                        date: today
                    },
                    success: function(response) {
                        const data = response.content || response;
                        const items = data.items || [];

                        if (items.length === 0) {
                            $('#items-table tbody').append(
                                '<tr><td colspan="5" class="text-center text-muted py-4">No products found.</td></tr>'
                            );
                            return;
                        }

                        items.forEach((item, index) => {
                            const row = `
                                <tr>
                                    <td>
                                        <strong>${item.product.product_name}</strong><br>
                                        <small class="text-muted">${item.product.product_brand} - ${item.product.product_code}</small>
                                        <input type="hidden" name="items[${index}][product_id]" value="${item.product.id}">
                                        <input type="hidden" name="items[${index}][beginning_inventory]" value="${item.beginning_inventory}">
                                        <input type="hidden" name="items[${index}][ending_inventory]" value="${item.ending_inventory}">
                                    </td>
                                    <td class="text-center system-value">${item.beginning_inventory}</td>
                                    <td class="text-center system-value">${item.ending_inventory}</td>
                                    <td>
                                        <textarea name="items[${index}][remarks]"
                                                  class="form-control"
                                                  rows="2"
                                                  placeholder="Optional notes...">${item.remarks || ''}</textarea>
                                    </td>
                                </tr>
                            `;
                            $('#items-table tbody').append(row);
                        });
                    },
                    error: () => toastr.error('Failed to load products for report')
                });
            });

            // Submit report
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin mr-2"></i> Submitting...');

                $.ajax({
                    url: '{{ route('inventoryReports.store') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        $('#addModal').modal('hide');
                        $('#mainTable').bootstrapTable('refresh');
                        toastr.success('Daily inventory report submitted successfully!');
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Failed to submit report';
                        toastr.error(msg);
                    },
                    complete: () => {
                        $btn.prop('disabled', false).html(
                            '<i class="fa fa-save mr-2"></i> Submit Report');
                    }
                });
            });
        });
    </script>
@endsection
