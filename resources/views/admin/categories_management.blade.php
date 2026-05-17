@extends('layouts.master')

@section('APP-TITLE', 'Category Management')
@section('active-categories', 'active')
@section('APP-SUBTITLE', 'Manage Product Categories')

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

        .badge-category {
            font-size: 0.9em;
            padding: 6px 12px;
            border-radius: 50px;
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
                        <i class="fa fa-plus"></i> Add New Category
                    </button>
                </div>

                <table id="table" data-toolbar="#toolbar" data-url="{{ route('categories.index') }}"
                    data-side-pagination="server" data-pagination="true" data-search="true" data-show-columns="true"
                    data-show-refresh="true" data-show-export="true" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog modal-dialog-centered">
            <form id="addForm" class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fa fa-folder-plus"></i> Add New Category
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" placeholder="e.g., Antibiotics, Vitamins"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Description <small class="text-muted">(Optional)</small></label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this category"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Category
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" class="modal-content">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="edit_category_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fa fa-edit"></i> Edit Category
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit_category_name" name="category_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description <small class="text-muted">(Optional)</small></label>
                        <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fa fa-check"></i> Update Category
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        let editCategoryId = null;

        // Table Formatters
        window.statusFormatter = function(value) {
            if (value === 'active') {
                return '<span class="badge badge-success badge-category">Active</span>';
            }
            return '<span class="badge badge-secondary badge-category">Inactive</span>';
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
            if (!row.id) return '';
            const name = row.name ? row.name.replace(/'/g, "\\'") : 'Category';
            return `
            <button class="btn btn-sm btn-warning" onclick="editCategory(${row.id})" title="Edit">
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteCategory(${row.id}, '${name}')" title="Delete">
                <i class="fa fa-trash"></i>
            </button>
        `;
        };

        // Edit Category
        function editCategory(id) {
            $.ajax({
                url: `/categories/${id}`,
                method: 'GET',
                success: function(res) {
                    if (res.code !== 200 || !res.content) {
                        toastr.error(res.message || 'Failed to load category');
                        return;
                    }
                    const cat = res.content;
                    editCategoryId = cat.id;
                    $('#edit_category_name').val(cat.category_name);
                    $('#edit_description').val(cat.description || '');
                    $('#editModal').modal('show');
                },
                error: () => toastr.error('Failed to fetch category data')
            });
        }

        // Delete Category
        function deleteCategory(id, name) {
            Swal.fire({
                title: 'Delete Category?',
                text: `Remove "${name}" permanently? Products in this category will become uncategorized.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/categories/${id}`,
                        method: 'DELETE',
                        success: function() {
                            $('#table').bootstrapTable('refresh');
                            toastr.success('Category deleted successfully');
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message ||
                                'Cannot delete: category has products');
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
                        field: 'category_name',
                        title: 'Category Name',
                        sortable: true
                    },
                    {
                        field: 'description',
                        title: 'Description',
                        formatter: (value) => value || '<em class="text-muted">No description</em>'
                    },
                    {
                        field: 'products_count',
                        title: 'Products',
                        align: 'center',
                        formatter: (value) => value || 0
                    },
                    {
                        field: 'created_at',
                        title: 'Created',
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
                formatNoMatches: () =>
                    '<div class="text-center p-5 text-muted">No categories found. Create your first one!</div>',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div> Loading categories...</div>',
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
                        total: res.pagination?.total || res.total || 0,
                        rows: res.rows || res.data || res.content || []
                    };
                }
            });

            // Open Add Modal
            $('#add-new-btn').on('click', function() {
                $('#addForm')[0].reset();
                $('#addModal').modal('show');
            });

            // Create Category
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route('categories.store') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        $('#addModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('Category created successfully');
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to create category');
                    }
                });
            });

            // Update Category
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: `/categories/${editCategoryId}`,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        $('#editModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('Category updated successfully');
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to update category');
                    }
                });
            });
        });
    </script>
@endsection
