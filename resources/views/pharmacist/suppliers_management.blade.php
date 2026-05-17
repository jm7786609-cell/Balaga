@extends('layouts.master')

@section('APP-TITLE', 'Supplier Management')
@section('active-suppliers', 'active')
@section('APP-SUBTITLE', 'Manage Product Suppliers')

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
                <div id="toolbar">
                    <button class="btn btn-primary shadow-sm" id="add-new-btn">
                        <i class="fa fa-plus"></i> Add New Supplier
                    </button>
                </div>

                <table id="table" data-toolbar="#toolbar" data-url="{{ route('suppliers.index') }}"
                    data-side-pagination="server" data-pagination="true" data-search="true" data-show-columns="true"
                    data-show-refresh="true" data-show-export="true" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="addForm" class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fa fa-truck"></i> Add New Supplier
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" name="supplier_name" class="form-control" placeholder="e.g., BioLabs Inc."
                                required>
                        </div>
                        <div class="col-md-6">
                            <label>Contact Person</label>
                            <input type="text" name="contact_person" class="form-control"
                                placeholder="e.g., Maria Santos">
                        </div>
                        <div class="col-md-6">
                            <label>Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" placeholder="0917-123-4567">
                        </div>
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="supplier@biolabs.com">
                        </div>
                        <div class="col-md-12">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Full address"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label>Website</label>
                            <input type="url" name="website" class="form-control" placeholder="https://biolabs.com">
                        </div>
                        <div class="col-md-12">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Payment terms, delivery days, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Supplier
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="editForm" class="modal-content">
                @method('PUT')
                <input type="hidden" id="edit_supplier_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fa fa-edit"></i> Edit Supplier
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_supplier_name" name="supplier_name" class="form-control"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label>Contact Person</label>
                            <input type="text" id="edit_contact_person" name="contact_person" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Phone Number</label>
                            <input type="text" id="edit_phone_number" name="phone_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" id="edit_email" name="email" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label>Address</label>
                            <textarea id="edit_address" name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label>Website</label>
                            <input type="url" id="edit_website" name="website" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label>Notes</label>
                            <textarea id="edit_notes" name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fa fa-check"></i> Update Supplier
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        let editSupplierId = null;

        // Formatters
        window.phoneFormatter = value => value ? `<i class="fa fa-phone text-muted mr-1"></i>${value}` :
            '<em class="text-muted">—</em>';
        window.emailFormatter = value => value ? `<i class="fa fa-envelope text-muted mr-1"></i>${value}` :
            '<em class="text-muted">—</em>';
        window.dateFormatter = value => value || '—';

        window.actionFormatter = function(value, row) {
            const name = row.supplier_name ? row.supplier_name.replace(/'/g, "\\'") : 'Supplier';
            return `
            <button class="btn btn-sm btn-warning" onclick="editSupplier(${row.id})" title="Edit">
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteSupplier(${row.id}, '${name}')" title="Delete">
                <i class="fa fa-trash"></i>
            </button>
        `;
        };

        function editSupplier(id) {
            $.get(`/suppliers/${id}`)
                .done(res => {
                    if (res.code !== 200 || !res.content) {
                        toastr.error(res.message || 'Failed to load supplier');
                        return;
                    }
                    const s = res.content;
                    editSupplierId = s.id;
                    $('#edit_supplier_name').val(s.supplier_name);
                    $('#edit_contact_person').val(s.contact_person || '');
                    $('#edit_phone_number').val(s.phone_number || '');
                    $('#edit_email').val(s.email || '');
                    $('#edit_address').val(s.address || '');
                    $('#edit_website').val(s.website || '');
                    $('#edit_notes').val(s.notes || '');
                    $('#editModal').modal('show');
                })
                .fail(() => toastr.error('Failed to fetch supplier'));
        }

        function deleteSupplier(id, name) {
            Swal.fire({
                title: 'Delete Supplier?',
                text: `"${name}" will be permanently removed.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/suppliers/${id}`,
                        method: 'DELETE',
                        success: () => {
                            $('#table').bootstrapTable('refresh');
                            toastr.success('Supplier deleted successfully');
                        },
                        error: xhr => toastr.error(xhr.responseJSON?.message || 'Cannot delete supplier')
                    });
                }
            });
        }

        $(function() {
            $('#table').bootstrapTable({
                columns: [{
                        field: 'id',
                        title: '#',
                        sortable: true,
                        width: 80
                    },
                    {
                        field: 'supplier_name',
                        title: 'Supplier Name',
                        sortable: true
                    },
                    {
                        field: 'contact_person',
                        title: 'Contact Person',
                        formatter: v => v || '<em class="text-muted">—</em>'
                    },
                    {
                        field: 'phone_number',
                        title: 'Phone',
                        formatter: 'phoneFormatter'
                    },
                    {
                        field: 'email',
                        title: 'Email',
                        formatter: 'emailFormatter'
                    },
                    {
                        field: 'address',
                        title: 'Address',
                        formatter: v => v ?
                            `<small>${v.substring(0,40)}${v.length > 40 ? '...' : ''}</small>` :
                            '<em class="text-muted">—</em>'
                    },
                    {
                        field: 'created_at',
                        title: 'Added On',
                        formatter: 'dateFormatter',
                        sortable: true
                    },
                    {
                        field: 'action',
                        title: 'Action',
                        formatter: 'actionFormatter',
                        align: 'center',
                        width: 110
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
                    '<div class="text-center p-5 text-muted">No suppliers found.<br><small>Add your first supplier to get started!</small></div>',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div> Loading suppliers...</div>',
                queryParams: params => ({
                    limit: params.limit,
                    page: Math.floor(params.offset / params.limit) + 1,
                    search: params.search || '',
                    sort: params.sort || '',
                    order: params.order || 'asc'
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
                    url: '{{ route('suppliers.store') }}',
                    data: $(this).serialize(),
                    success: () => {
                        $('#addModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('Supplier added successfully');
                    },
                    error: xhr => toastr.error(xhr.responseJSON?.message ||
                        'Failed to add supplier')
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.post({
                    url: `/suppliers/${editSupplierId}`,
                    data: $(this).serialize(),
                    success: () => {
                        $('#editModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('Supplier updated successfully');
                    },
                    error: xhr => toastr.error(xhr.responseJSON?.message ||
                        'Failed to update supplier')
                });
            });
        });
    </script>
@endsection
