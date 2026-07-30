<?php
$page_title = 'Payment History';
$active_menu = 'payments';
require_once '../includes/header.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$payments_query = "SELECT p.*, b.bill_no, b.grand_total, c.name as customer_name 
                   FROM payments p 
                   INNER JOIN bills b ON p.bill_id = b.id 
                   LEFT JOIN customers c ON b.customer_id = c.id";

if (!empty($search)) {
    $payments_query .= " WHERE b.bill_no LIKE ? OR c.name LIKE ? OR p.payment_mode LIKE ? ORDER BY p.id DESC";
    $stmt = mysqli_prepare($conn, $payments_query);
    $search_param = "%$search%";
    mysqli_stmt_bind_param($stmt, "sss", $search_param, $search_param, $search_param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $payments_query .= " ORDER BY p.id DESC";
    $result = mysqli_query($conn, $payments_query);
}
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6 col-sm-12">
        <h3 class="text-white fw-bold mb-0">Payment Ledger</h3>
        <p class="text-muted small mb-md-0">View all recorded sales transactions and payment modes</p>
    </div>
</div>

<div class="card card-custom mb-4">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <div class="col-md-10 col-sm-9">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-custom" placeholder="Search by bill number, customer name, or payment mode (Cash, UPI, Card)..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2 col-sm-3 d-grid">
                <button type="submit" class="btn btn-outline-light">Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Bill No</th>
                        <th>Customer</th>
                        <th>Amount Paid (₹)</th>
                        <th>Payment Mode</th>
                        <th>Transaction Date</th>
                        <th class="text-end">Invoice Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>TRX-<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td class="fw-bold text-white"><?php echo htmlspecialchars($row['bill_no']); ?></td>
                                <td><?php echo !empty($row['customer_name']) ? htmlspecialchars($row['customer_name']) : '<span class="text-muted small">Walk-In</span>'; ?></td>
                                <td class="text-success fw-bold">₹<?php echo number_format($row['amount'], 2); ?></td>
                                <td>
                                    <?php 
                                    $mode = $row['payment_mode'];
                                    if ($mode === 'Cash') {
                                        echo '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-cash me-1"></i>Cash</span>';
                                    } elseif ($mode === 'UPI') {
                                        echo '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-phone-vibrate me-1"></i>UPI</span>';
                                    } else {
                                        echo '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="bi bi-credit-card me-1"></i>Card</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('d M Y, h:i A', strtotime($row['payment_date'])); ?></td>
                                <td class="text-end">
                                    <a href="<?php echo BASE_URL; ?>billing/invoice.php?id=<?php echo $row['bill_id']; ?>" class="btn btn-sm btn-outline-info" title="View Full Invoice">
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-stack fs-1 d-block mb-3"></i>
                                No payment records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
require_once '../includes/footer.php';
?>
