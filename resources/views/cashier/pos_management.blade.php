@extends('layouts.master')

@section('APP-TITLE', 'Pharmacy POS')
@section('active-pos', 'active')
@section('APP-SUBTITLE', 'Point of Sale System')

@section('APP-STYLES')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css">
@endsection

@section('APP-CONTENT')
    <div class="container-fluid">
        <div class="row">

            <!-- LEFT : BARCODE + SEARCH -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-barcode"></i> Scan Product</h5>
                    </div>
                    <div class="card-body">

                        <!-- Barcode Scan -->
                        <input type="text" id="barcodeInput" class="form-control form-control-lg mb-3"
                            placeholder="Scan barcode here and press Enter" autofocus>

                        <div class="text-center">
                            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#productSearchModal">
                                <i class="fas fa-search"></i> Search Product Manually
                            </button>
                        </div>

                    </div>
                </div>

                <!-- CART TABLE -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Cart Items</h5>
                    </div>
                    <div class="card-body p-0">

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th width="120">Quantity</th>
                                        <th width="120">Price</th>
                                        <th width="140">Sub-total</th>
                                        <th width="80">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cartTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Cart is empty
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <!-- RIGHT : TOTAL -->
            <div class="col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Amount</h6>
                        <h1 class="text-success fw-bold">
                            ₱ <span id="totalAmount">0.00</span>
                        </h1>

                        <button class="btn btn-success btn-lg w-100 mt-4" id="checkoutBtn" disabled>
                            <i class="fas fa-cash-register"></i> Proceed Transaction
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MANUAL PRODUCT SEARCH MODAL -->
    <div class="modal fade" id="productSearchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Search Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="text" id="manualSearchInput" class="form-control mb-3"
                        placeholder="Search product name or code">

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody id="manualSearchResults">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Type to search products
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- CHECKOUT MODAL (UNCHANGED LOGIC) -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Checkout</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <h4>Total: ₱ <span id="modalTotal">0.00</span></h4>

                    <div class="mt-3 form-group">
                        <label>Amount Paid</label>
                        <input type="number" id="amountPaid" class="form-control form-control-lg text-end">
                    </div>

                    <div class="mt-3 form-group">
                        <label>Discount Type</label>
                        <select id="discountType" class="form-control">
                            <option value="none">None</option>
                            <option value="pwd">PWD (20%)</option>
                            <option value="senior">Senior Citizen (20%)</option>
                        </select>
                    </div>

                    <div class="mt-3 text-end">
                        <p>Total: ₱<span id="modalTotalActual" data-total="10">10.00</span></p>
                        <p>Discount: ₱<span id="discountAmount">0.00</span></p>
                        <p>Final Total: ₱<span id="finalTotal">0.00</span></p>
                        <p>Change: ₱<span id="changeDue">0.00</span></p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" id="confirmCheckout">Complete Sale</button>
                </div>

            </div>
        </div>
    </div>

    <!-- QUANTITY INPUT MODAL -->
    <div class="modal fade" id="quantityModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Enter Quantity</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <h5 id="quantityProductName"></h5>
                    <p class="text-muted">Price: ₱ <span id="quantityProductPrice"></span></p>

                    <input type="number" id="quantityInput" class="form-control form-control-lg text-center"
                        min="1" value="1" autofocus>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success btn-lg px-5" id="confirmAddQuantity">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('APP-SCRIPT')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let pendingProductId = null;

        /* ---------------- BARCODE SCAN ---------------- */
        $('#barcodeInput').on('keypress', function(e) {
            if (e.which === 13) {
                const barcode = $(this).val().trim();
                if (!barcode) return;

                $.post('/pos/cart/find-by-barcode', {
                        barcode
                    })
                    .done(function(res) {
                        $('#barcodeInput').val('');
                        openQuantityModal(res.content.id, res.content.product_name, res.content.price);
                    })
                    .fail(function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Product not found';
                        Swal.fire('Not Found', msg, 'error');
                        $('#barcodeInput').val('').focus();
                    });
            }
        });

        /* ---------------- MANUAL SEARCH ---------------- */
        $('#manualSearchInput').on('keyup', function() {
            const q = $(this).val();
            if (q.length < 2) {
                $('#manualSearchResults').html(
                    '<tr><td colspan="4" class="text-center text-muted">Type to search products</td></tr>');
                return;
            }

            $.get('/products/search', {
                q
            }, function(res) {
                const rows = res.content || [];
                let html = '';

                if (!rows.length) {
                    html = `<tr><td colspan="4" class="text-center text-muted">No results</td></tr>`;
                } else {
                    rows.forEach(p => {
                        html += `
                            <tr>
                                <td>${p.product_code}</td>
                                <td>${p.product_name}</td>
                                <td>₱${parseFloat(p.price).toFixed(2)}</td>
                                <td>
                                    <button class="btn btn-sm btn-success" onclick="addManual(${p.id}, '${p.product_name.replace(/'/g, "\\'")}', ${p.price})">
                                        Add
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#manualSearchResults').html(html);
            }).fail(function() {
                $('#manualSearchResults').html(
                    '<tr><td colspan="4" class="text-center text-danger">Error loading products</td></tr>'
                );
            });
        });

        function addManual(id, name, price) {
            openQuantityModal(id, name, price);
            $('#productSearchModal').modal('hide');
        }

        function openQuantityModal(productId, productName, price) {
            pendingProductId = productId;
            $('#quantityProductName').text(productName);
            $('#quantityProductPrice').text(parseFloat(price).toFixed(2));
            $('#quantityInput').val(1);
            $('#quantityModal').modal('show');
            $('#quantityInput').focus();
        }

        /* ---------------- LOAD CART TABLE ---------------- */
        function loadCart() {
            $.get('/pos/cart', function(res) {
                const cart = res.content;
                const items = cart.items || [];
                let total = parseFloat(cart.total_amount) || 0;
                let html = '';

                if (!items.length) {
                    html = `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Cart is empty</td>
                        </tr>
                    `;
                    $('#checkoutBtn').prop('disabled', true);
                } else {
                    items.forEach(i => {
                        html += `
                            <tr>
                                <td>${i.product.code}</td>
                                <td>${i.product.name}</td>
                                <td>${i.quantity}</td>
                                <td>₱${i.price}</td>
                                <td>₱${i.subtotal}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="removeItem(${i.product.id})">
                                        ✕
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#checkoutBtn').prop('disabled', false);
                }

                $('#cartTableBody').html(html);
                $('#totalAmount').text(total.toFixed(2));
            }).fail(function() {
                Swal.fire('Error', 'Failed to load cart', 'error');
            });
        }

        function removeItem(id) {
            $.ajax({
                url: `/pos/cart/remove/${id}`,
                method: 'DELETE',
                success: loadCart,
                error: function() {
                    Swal.fire('Error', 'Failed to remove item', 'error');
                }
            });
        }

        /* ---------------- CONFIRM ADD WITH QUANTITY ---------------- */
        $('#confirmAddQuantity').click(function() {
            const quantity = parseInt($('#quantityInput').val());

            if (!pendingProductId || isNaN(quantity) || quantity < 1) {
                Swal.fire('Invalid', 'Please enter a valid quantity', 'warning');
                return;
            }

            $.post('/pos/cart/add', {
                    product_id: pendingProductId,
                    quantity: quantity
                })
                .done(function() {
                    $('#quantityModal').modal('hide');
                    loadCart();
                    pendingProductId = null;
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to add item';
                    Swal.fire('Error', msg, 'error');
                });
        });

        /* ---------------- CHECKOUT ---------------- */
        $('#checkoutBtn').click(() => {

            const totalText = $('#totalAmount').text();
            const total = parseFloat(totalText) || 0;

            $('#modalTotal').text(total.toFixed(2));

            $('#modalTotalActual')
                .text(total.toFixed(2))
                .data('total', total);

            $('#amountPaid').val('');
            $('#changeDue').text('0.00');

            $('#checkoutModal').modal('show');
        });

        function calculateCheckout() {

            const total = parseFloat($('#modalTotalActual').data('total')) || 0;
            const paid = parseFloat($('#amountPaid').val()) || 0;
            const discountType = $('#discountType').val();

            let discountRate = 0;

            if (discountType === 'pwd' || discountType === 'senior') {
                discountRate = 0.20;
            }

            const discountAmount = total * discountRate;
            const finalTotal = total - discountAmount;
            const change = paid - finalTotal;

            $('#discountAmount').text(discountAmount.toFixed(2));
            $('#finalTotal').text(finalTotal.toFixed(2));
            $('#changeDue').text(change >= 0 ? change.toFixed(2) : '0.00');
        }

        $('#amountPaid, #discountType').on('input change', function() {
            calculateCheckout();
        });

        $('#confirmCheckout').click(() => {
            const amountPaid = $('#amountPaid').val();
            if (!amountPaid || parseFloat(amountPaid) < parseFloat($('#modalTotal').text())) {
                Swal.fire('Invalid Payment', 'Amount paid must be sufficient', 'warning');
                return;
            }

            $.post('/pos/checkout', {
                amount_paid: amountPaid
            }, function(res) {
                Swal.fire({
                    title: 'Success!',
                    text: res.message || 'Transaction complete. Change: ₱' + (parseFloat(res.content
                        .change_due) || 0).toFixed(2),
                    icon: 'success'
                });
                $('#checkoutModal').modal('hide');
                loadCart();
            }).fail(function(xhr) {
                let msg = 'Checkout failed';
                if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON?.errors) {
                    // Handle validation errors (e.g., stock, amount_paid)
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    msg = errors.join('<br>');
                }
                Swal.fire({
                    title: 'Error',
                    html: msg,
                    icon: 'error'
                });
            });
        });

        // Initial load
        $(document).ready(loadCart);
    </script>
@endsection
