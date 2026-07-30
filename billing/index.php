<?php
$page_title = 'Create Invoice';
$active_menu = 'billing';
require_once '../includes/header.php';

$customers_result = mysqli_query($conn, "SELECT id, name, phone FROM customers ORDER BY name ASC");

$products_result = mysqli_query($conn, "SELECT id, name, category, price, quantity FROM products WHERE status = 'Active' AND quantity > 0 ORDER BY name ASC");
$products = [];
while ($row = mysqli_fetch_assoc($products_result)) {
    $products[] = $row;
}
mysqli_data_seek($products_result, 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_bill'])) {
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
    $payment_mode = sanitize($_POST['payment_mode']);
    $payment_status = sanitize($_POST['payment_status']);
    $gst_enabled = isset($_POST['gst_enabled']) ? true : false;
    
    $product_ids = $_POST['item_product_id'] ?? [];
    $quantities = $_POST['item_qty'] ?? [];
    
    if (empty($product_ids)) {
        set_flash_message('danger', 'Please add at least one product to the bill.');
    } else {
        mysqli_begin_transaction($conn);
        try {
            $sub_total = 0.00;
            $items_to_save = [];
            
            for ($i = 0; $i < count($product_ids); $i++) {
                $pid = intval($product_ids[$i]);
                $qty = intval($quantities[$i]);
                
                if ($qty <= 0) {
                    throw new Exception("Quantity must be greater than zero.");
                }
                
                $p_stmt = mysqli_prepare($conn, "SELECT name, price, quantity FROM products WHERE id = ? AND status = 'Active' LIMIT 1");
                mysqli_stmt_bind_param($p_stmt, "i", $pid);
                mysqli_stmt_execute($p_stmt);
                $p_res = mysqli_stmt_get_result($p_stmt);
                $product_detail = mysqli_fetch_assoc($p_res);
                mysqli_stmt_close($p_stmt);
                
                if (!$product_detail) {
                    throw new Exception("Product ID #$pid does not exist or is inactive.");
                }
                
                if ($product_detail['quantity'] < $qty) {
                    throw new Exception("Insufficient stock for product '{$product_detail['name']}'. Available: {$product_detail['quantity']}, Requested: $qty");
                }
                
                $price = $product_detail['price'];
                $total_amount = $price * $qty;
                $sub_total += $total_amount;
                
                $items_to_save[] = [
                    'product_id' => $pid,
                    'qty' => $qty,
                    'price' => $price,
                    'total_amount' => $total_amount
                ];
            }
            
            $gst_amount = 0.00;
            if ($gst_enabled) {
                $gst_amount = $sub_total * 0.18;
            }
            $grand_total = $sub_total + $gst_amount;
            
            $bill_no = "BILL-" . date('Ymd') . "-" . rand(1000, 9999);
            
            $user_id = $_SESSION['user_id'];
            $bill_stmt = mysqli_prepare($conn, "INSERT INTO bills (bill_no, customer_id, user_id, sub_total, gst_amount, grand_total, payment_mode, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($bill_stmt, "siidddss", $bill_no, $customer_id, $user_id, $sub_total, $gst_amount, $grand_total, $payment_mode, $payment_status);
            
            if (!mysqli_stmt_execute($bill_stmt)) {
                throw new Exception("Failed to save invoice record.");
            }
            $bill_id = mysqli_insert_id($conn);
            mysqli_stmt_close($bill_stmt);
            
            foreach ($items_to_save as $item) {
                $item_stmt = mysqli_prepare($conn, "INSERT INTO bill_items (bill_id, product_id, quantity, price, total_amount) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($item_stmt, "iiidd", $bill_id, $item['product_id'], $item['qty'], $item['price'], $item['total_amount']);
                if (!mysqli_stmt_execute($item_stmt)) {
                    throw new Exception("Failed to save bill item records.");
                }
                mysqli_stmt_close($item_stmt);
                
                $stock_stmt = mysqli_prepare($conn, "UPDATE products SET quantity = quantity - ? WHERE id = ?");
                mysqli_stmt_bind_param($stock_stmt, "ii", $item['qty'], $item['product_id']);
                if (!mysqli_stmt_execute($stock_stmt)) {
                    throw new Exception("Failed to update stock levels.");
                }
                mysqli_stmt_close($stock_stmt);
            }
            
            if ($payment_status === 'Paid') {
                $pay_stmt = mysqli_prepare($conn, "INSERT INTO payments (bill_id, payment_mode, amount) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($pay_stmt, "isd", $bill_id, $payment_mode, $grand_total);
                if (!mysqli_stmt_execute($pay_stmt)) {
                    throw new Exception("Failed to register payment transactions.");
                }
                mysqli_stmt_close($pay_stmt);
            }
            
            mysqli_commit($conn);
            set_flash_message('success', "Invoice $bill_no generated successfully!");
            echo "<script>window.location.href = 'invoice.php?id=$bill_id';</script>";
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            set_flash_message('danger', "Billing Error: " . $e->getMessage());
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-white fw-bold mb-0">New Billing Invoice</h3>
        <p class="text-muted small">Select a customer, add products, and generate printable invoice receipts</p>
    </div>
</div>

<form method="POST" action="index.php" id="billing-form">
    <div class="row g-4">
        <div class="col-lg-4 col-md-5 col-sm-12">
            <div class="card card-custom mb-4">
                <div class="card-header border-bottom border-secondary-subtle bg-transparent py-3">
                    <h5 class="text-white mb-0"><i class="bi bi-person-fill text-primary me-2"></i>Customer Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label text-muted small fw-bold">Select Customer</label>
                        <select class="form-select form-select-custom" id="customer_id" name="customer_id">
                            <option value="">-- Walk-In Customer --</option>
                            <?php while ($c = mysqli_fetch_assoc($customers_result)): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']) . ' (' . htmlspecialchars($c['phone']) . ')'; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card card-custom">
                <div class="card-header border-bottom border-secondary-subtle bg-transparent py-3">
                    <h5 class="text-white mb-0"><i class="bi bi-wallet2 text-success me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="payment_mode" class="form-label text-muted small fw-bold">Payment Method</label>
                        <select class="form-select form-select-custom" id="payment_mode" name="payment_mode" required>
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Card">Card</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_status" class="form-label text-muted small fw-bold">Payment Status</label>
                        <select class="form-select form-select-custom" id="payment_status" name="payment_status" required>
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="gst_enabled" name="gst_enabled" value="1" checked>
                        <label class="form-check-label text-muted small fw-bold" for="gst_enabled">Apply GST (18%)</label>
                    </div>

                    <hr class="text-secondary">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Sub Total</span>
                        <strong class="text-white" id="display-subtotal">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">GST (18%)</span>
                        <strong class="text-white" id="display-gst">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-4 border-top border-secondary pt-2">
                        <span class="text-white fw-bold">Grand Total</span>
                        <strong class="text-success fs-4" id="display-grandtotal">₹0.00</strong>
                    </div>

                    <button type="submit" name="save_bill" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-receipt me-2"></i>Generate Invoice</button>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-7 col-sm-12">
            <div class="card card-custom mb-4">
                <div class="card-header border-bottom border-secondary-subtle bg-transparent py-3">
                    <h5 class="text-white mb-0"><i class="bi bi-cart-plus-fill text-warning me-2"></i>Add Items to Bill</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 col-sm-12">
                            <label for="product_select" class="form-label text-muted small fw-bold">Select Product</label>
                            <select class="form-select form-select-custom" id="product_select">
                                <option value="" disabled selected>-- Choose Product --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>" data-qty="<?php echo $p['quantity']; ?>">
                                        <?php echo htmlspecialchars($p['name']); ?> (₹<?php echo number_format($p['price'], 2); ?>) - Stock: <?php echo $p['quantity']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label for="product_qty" class="form-label text-muted small fw-bold">Quantity</label>
                            <input type="number" class="form-control form-control-custom" id="product_qty" min="1" value="1">
                        </div>
                        <div class="col-md-3 col-sm-6 d-grid">
                            <button type="button" class="btn btn-outline-warning py-2" id="btn-add-item"><i class="bi bi-plus-lg me-1"></i>Add</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom">
                <div class="card-header border-bottom border-secondary-subtle bg-transparent py-3">
                    <h5 class="text-white mb-0"><i class="bi bi-list-stars text-info me-2"></i>Invoice Line Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover mb-0" id="invoice-items-table">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Price (₹)</th>
                                    <th>Qty</th>
                                    <th>Total (₹)</th>
                                    <th class="text-end">Remove</th>
                                </tr>
                            </thead>
                            <tbody id="invoice-tbody">
                                <tr id="no-items-row">
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                                        No items added. Select products above to start.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const productsList = <?php echo json_encode($products); ?>;

document.addEventListener("DOMContentLoaded", function () {
    const productSelect = document.getElementById("product_select");
    const productQtyInput = document.getElementById("product_qty");
    const btnAddItem = document.getElementById("btn-add-item");
    const invoiceTbody = document.getElementById("invoice-tbody");
    const noItemsRow = document.getElementById("no-items-row");
    const gstSwitch = document.getElementById("gst_enabled");
    
    const displaySubtotal = document.getElementById("display-subtotal");
    const displayGst = document.getElementById("display-gst");
    const displayGrandtotal = document.getElementById("display-grandtotal");
    
    let addedItems = {};

    function updateCalculations() {
        let subtotal = 0;
        for (let pid in addedItems) {
            subtotal += addedItems[pid].price * addedItems[pid].qty;
        }
        
        let gst = 0;
        if (gstSwitch.checked) {
            gst = subtotal * 0.18;
        }
        let grandTotal = subtotal + gst;
        
        displaySubtotal.innerText = "₹" + subtotal.toFixed(2);
        displayGst.innerText = "₹" + gst.toFixed(2);
        displayGrandtotal.innerText = "₹" + grandTotal.toFixed(2);
    }

    function renderItems() {
        const rows = invoiceTbody.querySelectorAll("tr:not(#no-items-row)");
        rows.forEach(r => r.remove());
        
        const pids = Object.keys(addedItems);
        if (pids.length === 0) {
            noItemsRow.style.display = "";
            return;
        }
        
        noItemsRow.style.display = "none";
        
        pids.forEach(pid => {
            const item = addedItems[pid];
            const rowTotal = item.price * item.qty;
            const tr = document.createElement("tr");
            
            tr.innerHTML = `
                <td class="fw-bold text-white">
                    ${item.name}
                    <input type="hidden" name="item_product_id[]" value="${pid}">
                </td>
                <td>₹${item.price.toFixed(2)}</td>
                <td>
                    <input type="number" name="item_qty[]" value="${item.qty}" min="1" max="${item.maxQty}" class="form-control form-control-custom py-1 px-2 item-qty-input" data-id="${pid}" style="width: 80px;">
                </td>
                <td>₹${rowTotal.toFixed(2)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" data-id="${pid}">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </td>
            `;
            invoiceTbody.appendChild(tr);
        });

        document.querySelectorAll(".item-qty-input").forEach(input => {
            input.addEventListener("change", function () {
                const pid = this.getAttribute("data-id");
                let newQty = parseInt(this.value);
                const max = parseInt(this.getAttribute("max"));
                
                if (isNaN(newQty) || newQty < 1) newQty = 1;
                if (newQty > max) {
                    alert("Requested quantity exceeds available stock (" + max + ").");
                    newQty = max;
                }
                
                this.value = newQty;
                addedItems[pid].qty = newQty;
                renderItems();
                updateCalculations();
            });
        });

        document.querySelectorAll(".btn-remove-item").forEach(btn => {
            btn.addEventListener("click", function () {
                const pid = this.getAttribute("data-id");
                delete addedItems[pid];
                renderItems();
                updateCalculations();
            });
        });
    }

    btnAddItem.addEventListener("click", function () {
        const selectVal = productSelect.value;
        if (!selectVal) {
            alert("Please select a product.");
            return;
        }

        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute("data-price"));
        const maxQty = parseInt(selectedOption.getAttribute("data-qty"));
        const name = selectedOption.text.split(" (")[0];
        
        let qty = parseInt(productQtyInput.value);
        if (isNaN(qty) || qty < 1) qty = 1;

        if (addedItems[selectVal]) {
            let combinedQty = addedItems[selectVal].qty + qty;
            if (combinedQty > maxQty) {
                alert(`Cannot add more. Available stock: ${maxQty}. Already in cart: ${addedItems[selectVal].qty}`);
                combinedQty = maxQty;
            }
            addedItems[selectVal].qty = combinedQty;
        } else {
            if (qty > maxQty) {
                alert(`Requested quantity exceeds available stock (${maxQty}).`);
                qty = maxQty;
            }
            addedItems[selectVal] = {
                name: name,
                price: price,
                qty: qty,
                maxQty: maxQty
            };
        }

        renderItems();
        updateCalculations();
        
        productSelect.value = "";
        productQtyInput.value = "1";
    });

    gstSwitch.addEventListener("change", updateCalculations);

    document.getElementById("billing-form").addEventListener("submit", function (e) {
        if (Object.keys(addedItems).length === 0) {
            e.preventDefault();
            alert("Please add at least one product before generating invoice.");
        }
    });
});
</script>

<?php
mysqli_close($conn);
require_once '../includes/footer.php';
?>
