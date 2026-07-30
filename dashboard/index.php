<?php
$page_title = 'System Dashboard';
$active_menu = 'dashboard';
require_once '../includes/header.php';

$prod_count_res = mysqli_query($conn, "SELECT COUNT(id) FROM products");
$total_products = mysqli_fetch_row($prod_count_res)[0] ?? 0;

$cust_count_res = mysqli_query($conn, "SELECT COUNT(id) FROM customers");
$total_customers = mysqli_fetch_row($cust_count_res)[0] ?? 0;

$today_sales_res = mysqli_query($conn, "SELECT SUM(grand_total) FROM bills WHERE DATE(created_at) = CURDATE() AND payment_status = 'Paid'");
$today_sales = mysqli_fetch_row($today_sales_res)[0] ?? 0.00;

$bill_count_res = mysqli_query($conn, "SELECT COUNT(id) FROM bills");
$total_bills = mysqli_fetch_row($bill_count_res)[0] ?? 0;

$daily_sales_query = "SELECT DATE(created_at) as sale_date, SUM(grand_total) as daily_total, COUNT(id) as bill_count 
                      FROM bills 
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND payment_status = 'Paid' 
                      GROUP BY DATE(created_at) 
                      ORDER BY sale_date DESC";
$daily_sales_res = mysqli_query($conn, $daily_sales_query);

$monthly_sales_query = "SELECT DATE_FORMAT(created_at, '%M %Y') as sale_month, SUM(grand_total) as monthly_total 
                        FROM bills 
                        WHERE YEAR(created_at) = YEAR(CURDATE()) AND payment_status = 'Paid' 
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                        ORDER BY created_at DESC";
$monthly_sales_res = mysqli_query($conn, $monthly_sales_query);

$top_products_query = "SELECT p.name, SUM(bi.quantity) as total_qty, SUM(bi.total_amount) as total_revenue 
                       FROM bill_items bi 
                       INNER JOIN products p ON bi.product_id = p.id 
                       GROUP BY bi.product_id 
                       ORDER BY total_qty DESC 
                       LIMIT 5";
$top_products_res = mysqli_query($conn, $top_products_query);

$recent_bills_query = "SELECT b.*, c.name as customer_name, u.name as cashier_name 
                       FROM bills b 
                       LEFT JOIN customers c ON b.customer_id = c.id 
                       INNER JOIN users u ON b.user_id = u.id 
                       ORDER BY b.id DESC 
                       LIMIT 5";
$recent_bills_res = mysqli_query($conn, $recent_bills_query);
?>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card card-custom h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Today's Sales</span>
                    <h3 class="text-success fw-bold m-0">₹<?php echo number_format($today_sales, 2); ?></h3>
                </div>
                <div class="bg-success-subtle p-3 rounded-3 text-success">
                    <i class="bi bi-currency-rupee fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card card-custom h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Invoices</span>
                    <h3 class="text-white fw-bold m-0"><?php echo $total_bills; ?></h3>
                </div>
                <div class="bg-primary-subtle p-3 rounded-3 text-primary">
                    <i class="bi bi-receipt-cutoff fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card card-custom h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Products</span>
                    <h3 class="text-warning fw-bold m-0"><?php echo $total_products; ?></h3>
                </div>
                <div class="bg-warning-subtle p-3 rounded-3 text-warning">
                    <i class="bi bi-egg-fried fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card card-custom h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Customers</span>
                    <h3 class="text-info fw-bold m-0"><?php echo $total_customers; ?></h3>
                </div>
                <div class="bg-info-subtle p-3 rounded-3 text-info">
                    <i class="bi bi-people fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card card-custom h-100">
            <div class="card-header border-bottom border-secondary-subtle bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h5 class="text-white mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Transactions</h5>
                <a href="<?php echo BASE_URL; ?>payments/index.php" class="btn btn-sm btn-outline-light">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Bill No</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total (₹)</th>
                                <th>Mode</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($recent_bills_res) > 0): ?>
                                <?php while ($bill = mysqli_fetch_assoc($recent_bills_res)): ?>
                                    <tr>
                                        <td class="fw-bold text-white"><?php echo htmlspecialchars($bill['bill_no']); ?></td>
                                        <td><?php echo !empty($bill['customer_name']) ? htmlspecialchars($bill['customer_name']) : '<span class="text-muted small">Walk-In</span>'; ?></td>
                                        <td><?php echo date('d M Y', strtotime($bill['created_at'])); ?></td>
                                        <td class="fw-bold">₹<?php echo number_format($bill['grand_total'], 2); ?></td>
                                        <td><?php echo $bill['payment_mode']; ?></td>
                                        <td>
                                            <?php if ($bill['payment_status'] === 'Paid'): ?>
                                                <span class="badge badge-active">Paid</span>
                                            <?php else: ?>
                                                <span class="badge badge-inactive">Unpaid</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a href="<?php echo BASE_URL; ?>billing/invoice.php?id=<?php echo $bill['id']; ?>" class="btn btn-sm btn-outline-info" title="View Invoice">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>billing/print.php?id=<?php echo $bill['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Print Receipt">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-receipt fs-1 d-block mb-3"></i>
                                        No recent transactions.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card card-custom h-100">
            <div class="card-header border-bottom border-secondary-subtle bg-transparent py-3">
                <h5 class="text-white mb-0 fw-bold"><i class="bi bi-trophy me-2 text-warning"></i>Top Selling Products</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (mysqli_num_rows($top_products_res) > 0): ?>
                        <?php 
                        $rank = 1;
                        while ($top = mysqli_fetch_assoc($top_products_res)): 
                        ?>
                            <div class="list-group-item bg-transparent text-white border-bottom border-secondary-subtle py-3 px-4 d-flex align-items-center gap-3">
                                <div class="rank-badge bg-dark border border-secondary text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                    <?php echo $rank++; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="m-0 text-white fw-bold"><?php echo htmlspecialchars($top['name']); ?></h6>
                                    <small class="text-muted"><?php echo $top['total_qty']; ?> units sold</small>
                                </div>
                                <strong class="text-success small">₹<?php echo number_format($top['total_revenue'], 2); ?></strong>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-award fs-1 d-block mb-2"></i>
                            No sales records available yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-sm-12">
        <div class="card card-custom">
            <div class="card-header border-bottom border-secondary-subtle bg-transparent py-3">
                <h5 class="text-white mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-info"></i>Daily Sales Report (Last 7 Days)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders Count</th>
                                <th class="text-end">Revenue (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($daily_sales_res) > 0): ?>
                                <?php while ($d = mysqli_fetch_assoc($daily_sales_res)): ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($d['sale_date'])); ?></td>
                                        <td><?php echo $d['bill_count']; ?></td>
                                        <td class="text-end fw-bold text-success">₹<?php echo number_format($d['daily_total'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No sales in the last 7 days.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12">
        <div class="card card-custom">
            <div class="card-header border-bottom border-secondary-subtle bg-transparent py-3">
                <h5 class="text-white mb-0 fw-bold"><i class="bi bi-calendar-month me-2 text-success"></i>Monthly Sales Report (Current Year)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Revenue (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($monthly_sales_res) > 0): ?>
                                <?php while ($m = mysqli_fetch_assoc($monthly_sales_res)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($m['sale_month']); ?></td>
                                        <td class="text-end fw-bold text-success">₹<?php echo number_format($m['monthly_total'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted">No sales this year.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
require_once '../includes/footer.php';
?>