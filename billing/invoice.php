<?php
$page_title = 'Invoice Details';
$active_menu = 'billing';
require_once '../includes/header.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('danger', 'Invalid Invoice ID.');
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

$bill_id = intval($_GET['id']);

$bill_query = "SELECT b.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email, c.address as customer_address, u.name as cashier_name 
               FROM bills b 
               LEFT JOIN customers c ON b.customer_id = c.id 
               INNER JOIN users u ON b.user_id = u.id 
               WHERE b.id = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $bill_query);
mysqli_stmt_bind_param($stmt, "i", $bill_id);
mysqli_stmt_execute($stmt);
$bill_res = mysqli_stmt_get_result($stmt);
$bill = mysqli_fetch_assoc($bill_res);
mysqli_stmt_close($stmt);

if (!$bill) {
    set_flash_message('danger', 'Invoice not found.');
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

$items_query = "SELECT bi.*, p.name as product_name, p.category 
                FROM bill_items bi 
                INNER JOIN products p ON bi.product_id = p.id 
                WHERE bi.bill_id = ?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, "i", $bill_id);
mysqli_stmt_execute($stmt);
$items_res = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>

<div class="row mb-4 align-items-center no-print">
    <div class="col-md-6 col-sm-12">
        <h3 class="text-white fw-bold mb-0">Invoice Preview</h3>
        <p class="text-muted small mb-md-0">Review details of Invoice <?php echo htmlspecialchars($bill['bill_no']); ?></p>
    </div>
    <div class="col-md-6 col-sm-12 text-md-end d-flex gap-2 justify-content-md-end mt-2 mt-md-0">
        <a href="index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i>
            <span>New Bill</span>
        </a>
        <a href="print.php?id=<?php echo $bill_id; ?>" target="_blank" class="btn btn-success d-inline-flex align-items-center gap-2">
            <i class="bi bi-printer-fill"></i>
            <span>Print Invoice</span>
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-11 col-md-12">
        <div class="card card-custom p-4 shadow-lg border border-secondary-subtle">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6 col-sm-12">
                        <h4 class="text-white fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-cup-hot-fill text-warning"></i>
                            <span>Bean & Brew Cafe</span>
                        </h4>
                        <p class="text-muted small mb-0">
                            123 Espresso Avenue, Sector 5<br>
                            Kolkata, WB 700091<br>
                            Phone: +91 98765 43210
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 text-md-end mt-3 mt-md-0">
                        <h5 class="text-warning fw-bold mb-1">INVOICE</h5>
                        <p class="text-white-50 small mb-1"><span class="text-muted">Invoice No:</span> <?php echo htmlspecialchars($bill['bill_no']); ?></p>
                        <p class="text-white-50 small mb-1"><span class="text-muted">Date:</span> <?php echo date('d M Y, h:i A', strtotime($bill['created_at'])); ?></p>
                        <p class="text-white-50 small mb-0"><span class="text-muted">Cashier:</span> <?php echo htmlspecialchars($bill['cashier_name']); ?></p>
                    </div>
                </div>

                <hr class="text-secondary mb-4">

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-white-50 small text-uppercase fw-bold mb-2">Billed To:</h6>
                        <?php if (!empty($bill['customer_name'])): ?>
                            <h5 class="text-white fw-bold mb-1"><?php echo htmlspecialchars($bill['customer_name']); ?></h5>
                            <p class="text-muted small mb-0">
                                Phone: <?php echo htmlspecialchars($bill['customer_phone']); ?><br>
                                <?php if(!empty($bill['customer_email'])): ?>Email: <?php echo htmlspecialchars($bill['customer_email']); ?><br><?php endif; ?>
                                <?php if(!empty($bill['customer_address'])): ?>Address: <?php echo htmlspecialchars($bill['customer_address']); ?><?php endif; ?>
                            </p>
                        <?php else: ?>
                            <h5 class="text-white fw-bold mb-1">Walk-In Customer</h5>
                            <p class="text-muted small mb-0">Anonymous Sales</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-custom border-bottom border-secondary-subtle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Details</th>
                                <th>Category</th>
                                <th class="text-end">Price (₹)</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            while ($item = mysqli_fetch_assoc($items_res)): 
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td class="fw-bold text-white"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td class="text-end">₹<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end fw-bold text-white">₹<?php echo number_format($item['total_amount'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-5 col-sm-12">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Sub Total</span>
                            <span class="text-white">₹<?php echo number_format($bill['sub_total'], 2); ?></span>
                        </div>
                        <?php if ($bill['gst_amount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">GST (18%)</span>
                                <span class="text-white">₹<?php echo number_format($bill['gst_amount'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-3 border-top border-secondary pt-2">
                            <span class="text-white fw-bold">Grand Total</span>
                            <span class="text-success fw-bold fs-5">₹<?php echo number_format($bill['grand_total'], 2); ?></span>
                        </div>
                        
                        <div class="p-3 bg-dark rounded border border-secondary-subtle">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted">Payment Mode:</span>
                                <strong class="text-white"><?php echo $bill['payment_mode']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Status:</span>
                                <?php if ($bill['payment_status'] === 'Paid'): ?>
                                    <strong class="text-success">PAID</strong>
                                <?php else: ?>
                                    <strong class="text-danger">UNPAID</strong>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="text-secondary mt-5 mb-4">
                
                <div class="text-center text-muted small">
                    <p class="mb-1">Thank you for visiting Bean & Brew Cafe!</p>
                    <p class="mb-0 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">This is a computer generated invoice receipt</p>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
require_once '../includes/footer.php';
?>
