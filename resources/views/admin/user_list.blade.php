@extends('layouts.master')

@section('APP-TITLE', 'User Management')
@section('active-users', 'active')
@section('APP-SUBTITLE', 'Manage System Users')

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
                    <button class="btn btn-primary" id="add-new-btn">
                        <i class="fa fa-plus"></i> Add New User
                    </button>
                </div>

                <table id="table" data-toolbar="#toolbar" data-url="{{ route('users.index') }}"
                    data-side-pagination="server" data-pagination="true" data-search="true" data-show-columns="true"
                    data-show-refresh="true" data-show-export="true" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog modal-lg">
            <form id="addForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-control" required>
                                <option value="">-- Select Role --</option>
                                <option value="admin">Admin</option>
                                <option value="pharmacist">Pharmacist</option>
                                <option value="cashier">Cashier</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label>Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save User</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog modal-lg">
            <form id="editForm" class="modal-content">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="edit_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" id="edit_middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Role <span class="text-danger">*</span></label>
                            <select name="role" id="edit_role" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="pharmacist">Pharmacist</option>
                                <option value="cashier">Cashier</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <small class="text-muted">Leave password fields blank to keep current password</small>
                        </div>
                        <div class="col-md-6">
                            <label>New Password (optional)</label>
                            <input type="password" name="password" class="form-control" minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label>Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Update User</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        let editUserId = null;

        // Table Formatters
        window.roleBadgeFormatter = function(value) {
            const badges = {
                admin: 'bg-danger',
                pharmacist: 'bg-success',
                cashier: 'bg-primary'
            };
            const label = value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
            return `<span class="badge ${badges[value] || 'bg-secondary'}">${label}</span>`;
        };

        window.dateFormatter = function(value) {
            return value ? new Date(value).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : '—';
        };

        window.actionFormatter = function(value, row) {
            if (!row.id) return '';
            const name = row.full_name ? row.full_name.replace(/'/g, "\\'") : 'User';
            return `
            <button class="btn btn-sm btn-warning" onclick="editUser(${row.id})" title="Edit">
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteUser(${row.id}, '${name}')" title="Delete">
                <i class="fa fa-trash"></i>
            </button>
        `;
        };

        // Edit User
        function editUser(id) {
            $.ajax({
                url: `/users/${id}`,
                method: 'GET',
                success: function(res) {
                    const user = res.content;
                    editUserId = user.id;

                    $('#edit_first_name').val(user.first_name || '');
                    $('#edit_middle_name').val(user.middle_name || '');
                    $('#edit_last_name').val(user.last_name || '');
                    $('#edit_email').val(user.email || '');
                    $('#edit_role').val(user.role || '');

                    openModal('#editModal'); // Fixed
                },
                error: function() {
                    toastr.error('Failed to load user data');
                }
            });
        }

        // Delete User
        function deleteUser(id, name) {
            Swal.fire({
                title: 'Delete User?',
                text: `Remove "${name}" permanently? This cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/users/${id}`,
                        method: 'DELETE',
                        success: function() {
                            $('#table').bootstrapTable('refresh');
                            toastr.success('User deleted successfully');
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Failed to delete user');
                        }
                    });
                }
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
                        field: 'fullname',
                        title: 'Full Name',
                        sortable: true
                    },
                    {
                        field: 'email',
                        title: 'Email',
                        sortable: true
                    },
                    {
                        field: 'role',
                        title: 'Role',
                        formatter: 'roleBadgeFormatter',
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
                        width: 120
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
                formatNoMatches: () => '<div class="text-center p-5 text-muted">No users found.</div>',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div> Loading...</div>',
                queryParams: function(params) {
                    return {
                        limit: params.limit,
                        page: (params.offset / params.limit) + 1,
                        search: params.search || '',
                        sort: params.sort || '',
                        order: params.order || ''
                    };
                },
                responseHandler: function(res) {
                    return {
                        total: res.pagination.total,
                        rows: res.rows || res.data
                    };
                }
            });

            // Add New Button
            $('#add-new-btn').on('click', function() {
                $('#addForm')[0].reset();
                openModal('#addModal'); // Fixed
            });

            // Create User
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route('users.store') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        $('#addModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('User created successfully');
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to create user');
                    }
                });
            });

            // Update User
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: `/users/${editUserId}`,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        $('#editModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('User updated successfully');
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to update user');
                    }
                });
            });
        });
    </script>
@endsection
