@extends('layouts.master')

@section('APP-TITLE', 'Product Management')
@section('active-products', 'active')
@section('APP-SUBTITLE', 'Manage Medicines & Inventory Items')

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

        .stock-low {
            color: #e74c3c;
            font-weight: 600;
        }

        .stock-critical {
            color: #c0392b;
            font-weight: 700;
        }

        .badge-reorder {
            background: #e67e22;
        }

        label {
            font-weight: 600;
        }

        .form-text small {
            color: #95a5a6;
        }

        .current-image-preview {
            margin-bottom: 15px;
        }

        .current-image-preview img {
            max-width: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
                        <i class="fa fa-plus"></i> Add New Product
                    </button>
                </div>

                <table id="table" data-toolbar="#toolbar" data-url="{{ route('products.index') }}"
                    data-side-pagination="server" data-pagination="true" data-search="true" data-show-columns="true"
                    data-show-refresh="true" data-show-export="true" data-sticky-header="true">
                </table>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form id="addForm" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-box-open"></i> Add New Product
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Product Code (SKU) <span class="text-danger">*</span></label>
                            <input type="text" name="product_code" class="form-control" placeholder="e.g., PAR001"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label>Milligram <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control"
                                placeholder="e.g., Paracetamol 500mg" required>
                        </div>
                        <div class="col-md-4">
                            <label>Generic Name <span class="text-danger">*</span></label>
                            <input type="text" name="generic_name" class="form-control" placeholder="e.g., Paracetamol"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label>Brand <span class="text-danger">*</span></label>
                            <input type="text" name="product_brand" class="form-control" placeholder="e.g., Biogesic"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label>Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">-- No Category --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Supplier</label>
                            <select name="supplier_id" class="form-control">
                                <option value="">-- No Supplier --</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->supplier_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Unit <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" placeholder="e.g., tablet, bottle"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label>Price (₱) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00"
                                required>
                        </div>

                        <div class="col-12">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional product description..."></textarea>
                        </div>

                        <!-- New: Product Image Upload -->
                        <div class="col-12">
                            <label>Product Image</label>
                            <input type="file" name="image" id="add_image" class="file">
                            <small class="form-text text-muted">Accepted formats: jpg, jpeg, png, webp. Max size:
                                2MB</small>
                        </div>

                        <div class="col-12">
                            <hr>
                        </div>
                        <div class="col-12">
                            <h6>EOQ & Reorder Settings</h6>
                        </div>

                        <div class="col-md-4">
                            <label>Ordering Cost (S)</label>
                            <input type="number" step="0.01" name="ordering_cost" class="form-control" value="0">
                            <small class="form-text text-muted">Cost per purchase order</small>
                        </div>
                        <div class="col-md-4">
                            <label>Holding Cost (H)</label>
                            <input type="number" step="0.01" name="holding_cost" class="form-control"
                                value="0">
                            <small class="form-text text-muted">Cost to hold one unit for a year</small>
                        </div>
                        <div class="col-md-4">
                            <label>Lead Time (days)</label>
                            <input type="number" name="lead_time_days" class="form-control" value="0"
                                min="0">
                        </div>
                        <div class="col-md-6">
                            <label>Safety Stock</label>
                            <input type="number" name="safety_stock" class="form-control" value="0"
                                min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="add-submit-btn">
                        <span class="btn-text">Save Product</span>
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form id="editForm" class="modal-content">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-edit"></i> Edit Product
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Product Code (SKU) <span class="text-danger">*</span></label>
                            <input type="text" id="edit_product_code" name="product_code" class="form-control"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label>Milligram <span class="text-danger">*</span></label>
                            <input type="text" id="edit_product_name" name="product_name" class="form-control"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label>Generic Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_generic_name" name="generic_name" class="form-control"
                                placeholder="e.g., Paracetamol" required>
                        </div>
                        <div class="col-md-6">
                            <label>Brand <span class="text-danger">*</span></label>
                            <input type="text" id="edit_product_brand" name="product_brand" class="form-control"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label>Category</label>
                            <select id="edit_category_id" name="category_id" class="form-control">
                                <option value="">-- No Category --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Supplier</label>
                            <select id="edit_supplier_id" name="supplier_id" class="form-control">
                                <option value="">-- No Supplier --</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->supplier_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Unit <span class="text-danger">*</span></label>
                            <input type="text" id="edit_unit" name="unit" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Price (₱) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="edit_price" name="price" class="form-control"
                                required>
                        </div>

                        <div class="col-12">
                            <label>Description</label>
                            <textarea id="edit_description" name="description" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- New: Product Image Upload for Edit -->
                        <div class="col-12">
                            <label>Product Image</label>
                            <div id="current_image_preview" class="current-image-preview"></div>
                            <input type="file" name="image" id="edit_image" class="file">
                            <small class="form-text text-muted">Upload a new image to replace the current one. Accepted
                                formats: jpg, jpeg, png, webp. Max size: 2MB</small>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="remove_image" id="remove_image" class="form-check-input">
                                <label class="form-check-label" for="remove_image">Remove current image</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <hr>
                        </div>
                        <div class="col-12">
                            <h6>EOQ & Reorder Settings</h6>
                        </div>

                        <div class="col-md-4">
                            <label>Ordering Cost (S)</label>
                            <input type="number" step="0.01" id="edit_ordering_cost" name="ordering_cost"
                                class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Holding Cost (H)</label>
                            <input type="number" step="0.01" id="edit_holding_cost" name="holding_cost"
                                class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Lead Time (days)</label>
                            <input type="number" id="edit_lead_time_days" name="lead_time_days" class="form-control"
                                min="0">
                        </div>
                        <div class="col-md-6">
                            <label>Safety Stock</label>
                            <input type="number" id="edit_safety_stock" name="safety_stock" class="form-control"
                                min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="edit-submit-btn">
                        <span class="btn-text">Update Product</span>
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script>
        let editProductId;

        // Formatters
        function imageFormatter(value, row) {
            if (value) {
                return `<img src="${value}" alt="Product" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">`;
            } else {
                return '<i class="fa fa-image text-muted"></i>';
            }
        }

        function priceFormatter(value, row) {
            return `₱${value}`;
        }

        function stockFormatter(value, row) {
            let cls = '';
            if (row.is_low_stock) cls = 'stock-low';
            if (value <= 0) cls = 'stock-critical';
            return `<span class="${cls}">${value}</span>`;
        }

        function reorderFormatter(value) {
            return value ? '<span class="badge badge-reorder">Reorder</span>' :
                '<span class="badge badge-success">OK</span>';
        }

        function dateFormatter(value, row) {
            return value;
        }

        function actionFormatter(value, row) {
            return `
                <button class="btn btn-sm btn-info" onclick="editProduct(${row.id})"><i class="fa fa-edit"></i></button>
                <button class="btn btn-sm btn-danger" onclick="deleteProduct(${row.id}, '${row.product_name}')"><i class="fa fa-trash"></i></button>
            `;
        }

        function editProduct(id) {
            editProductId = id;

            $.get(`{{ url('products') }}/${id}`)
                .done(response => {
                    // Extract actual product from response.content
                    const p = response.content;

                    $('#edit_product_code').val(p.product_code);
                    $('#edit_product_name').val(p.product_name);
                    $('#edit_generic_name').val(p.generic_name);
                    $('#edit_product_brand').val(p.product_brand);
                    $('#edit_unit').val(p.unit);
                    $('#edit_price').val(parseFloat(p.price));
                    $('#edit_description').val(p.description || '');

                    // Important: category and supplier are strings (names), not objects!
                    // So we need to find the matching <option> by text
                    $('#edit_category_id').val('');
                    $('#edit_supplier_id').val('');

                    if (p.category) {
                        $('#edit_category_id option').filter(function() {
                            return $(this).text() === p.category;
                        }).prop('selected', true);
                    }

                    if (p.supplier) {
                        $('#edit_supplier_id option').filter(function() {
                            return $(this).text() === p.supplier;
                        }).prop('selected', true);
                    }

                    $('#edit_ordering_cost').val(p.ordering_cost || 0);
                    $('#edit_holding_cost').val(p.holding_cost || 0);
                    $('#edit_lead_time_days').val(p.lead_time_days || 0);
                    $('#edit_safety_stock').val(p.safety_stock || 0);

                    // Handle current image preview
                    const imagePreview = $('#current_image_preview');
                    if (p.image_url) {
                        imagePreview.html(`
                    <div class="mb-2">
                        <strong>Current Image:</strong>
                    </div>
                    <img src="${p.image_url}" alt="Current Product Image" style="max-width: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                `);
                    } else {
                        imagePreview.html('<p class="text-muted"><em>No image uploaded.</em></p>');
                    }

                    // QR Code Preview (Bonus)
                    if (p.qr_code_url) {
                        imagePreview.append(`
                    <div class="mt-3">
                        <strong>QR Code:</strong>
                    </div>
                    <img src="${p.qr_code_url}" alt="QR Code" style="max-width: 150px; border: 1px solid #ddd; border-radius: 8px;">
                `);
                    }

                    // Reset file input and remove checkbox
                    $('#edit_image').fileinput('clear');
                    $('#remove_image').prop('checked', false);

                    // Show modal
                    $('#editModal').modal('show');
                })
                .fail(xhr => {
                    console.error(xhr.responseText);
                    toastr.error('Failed to fetch product details');
                });
        }

        function deleteProduct(id, name) {
            Swal.fire({
                title: 'Delete Product?',
                text: `"${name}" will be permanently removed.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('products') }}/${id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: () => {
                            $('#table').bootstrapTable('refresh');
                            toastr.success('Product deleted successfully');
                        },
                        error: xhr => toastr.error(xhr.responseJSON?.message || 'Cannot delete product')
                    });
                }
            });
        }

        $(function() {
            // Initialize file inputs
            $("#add_image, #edit_image").fileinput({
                theme: 'fa5',
                showUpload: false,
                showRemove: true,
                allowedFileTypes: ['image'],
                allowedFileExtensions: ['jpg', 'jpeg', 'png', 'webp'],
                maxFileSize: 2048,
                maxFileCount: 1,
                browseLabel: 'Browse Image',
                dropZoneTitle: 'Drag & drop image here ...'
            });

            $('#table').bootstrapTable({
                columns: [{
                        field: 'id',
                        title: '#',
                        sortable: true,
                        width: 70
                    },
                    {
                        field: 'thumbnail_url',
                        title: 'Image',
                        formatter: 'imageFormatter',
                        align: 'center',
                        width: 100
                    },
                    {
                        field: 'product_code',
                        title: 'Code',
                        sortable: true
                    },
                    {
                        field: 'product_name',
                        title: 'Milligram',
                        sortable: true
                    },
                    {
                        field: 'product_brand',
                        title: 'Brand',
                        sortable: true
                    },
                    {
                        field: 'category',
                        title: 'Category',
                        formatter: v => v || '<em>—</em>'
                    },
                    {
                        field: 'price',
                        title: 'Price',
                        formatter: 'priceFormatter',
                        align: 'right'
                    },
                    {
                        field: 'current_stock',
                        title: 'Stock',
                        formatter: 'stockFormatter',
                        align: 'center'
                    },
                    {
                        field: 'needs_reorder',
                        title: 'Status',
                        formatter: 'reorderFormatter',
                        align: 'center'
                    },
                    {
                        field: 'created_at',
                        title: 'Added',
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
                pageSize: 15,
                pageList: [15, 25, 50, 100],
                search: true,
                showColumns: true,
                showRefresh: true,
                showExport: true,
                stickyHeader: true,
                formatNoMatches: () =>
                    '<div class="text-center p-5 text-muted">No products found.<br><small>Add your first product to get started!</small></div>',
                formatLoadingMessage: () =>
                    '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div> Loading products...</div>',
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
                $('#add_image').fileinput('clear');
                $('#addModal').modal('show');
            });

            $('#addForm').on('submit', function(e) {
                e.preventDefault();

                const $btn = $('#add-submit-btn');
                const $text = $btn.find('.btn-text');
                const originalText = $text.text();

                // Show spinner + disable button
                $btn.prop('disabled', true);
                $text.html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                let formData = new FormData(this);

                $.ajax({
                    url: '{{ route('products.store') }}',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: () => {
                        $('#addModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('Product added successfully');
                    },
                    error: (xhr) => {
                        toastr.error(xhr.responseJSON?.message || 'Failed to add product');
                    },
                    complete: () => {
                        // Always re-enable button and restore text
                        $btn.prop('disabled', false);
                        $text.text(originalText);
                    }
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();

                const $btn = $('#edit-submit-btn');
                const $text = $btn.find('.btn-text');
                const originalText = $text.text();

                // Show spinner + disable
                $btn.prop('disabled', true);
                $text.html('<i class="fa fa-spinner fa-spin"></i> Updating...');

                let formData = new FormData(this);

                $.ajax({
                    url: `{{ url('products') }}/${editProductId}`,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: () => {
                        $('#editModal').modal('hide');
                        $('#table').bootstrapTable('refresh');
                        toastr.success('Product updated successfully');
                    },
                    error: (xhr) => {
                        toastr.error(xhr.responseJSON?.message || 'Failed to update product');
                    },
                    complete: () => {
                        // Always restore button
                        $btn.prop('disabled', false);
                        $text.text(originalText);
                    }
                });
            });
        });
    </script>
@endsection
