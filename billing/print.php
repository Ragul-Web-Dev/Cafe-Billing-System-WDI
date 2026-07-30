<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_auth();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Invoice Access");
}

$bill_id = intval($_GET['id']);

$bill_query = "SELECT b.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address, u.name as cashier_name 
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
    die("Invoice Not Found");
}

$items_query = "SELECT bi.*, p.name as product_name 
                FROM bill_items bi 
                INNER JOIN products p ON bi.product_id = p.id 
                WHERE bi.bill_id = ?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, "i", $bill_id);
mysqli_stmt_execute($stmt);
$items_res = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice - <?php echo $bill['bill_no']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            color: #000000;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            padding: 20px;
        }
        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            border: 1px dashed #cccccc;
            padding: 15px;
        }
        .text-center {
            text-align: center;
        }
        hr {
            border-top: 1px dashed #000000;
            margin: 10px 0;
            opacity: 1;
        }
        .table {
            margin-bottom: 5px;
        }
        .table th, .table td {
            padding: 4px 0;
            border: none;
            font-size: 13px;
        }
        @media print {
            body {
                padding: 0;
            }
            .receipt-container {
                border: none;
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
</head>
<body onload="window.print();">

<div class="receipt-container">
    <div class="text-center">
        <h4 class="fw-bold mb-0">BEAN & BREW CAFE</h4>
        <p class="mb-1">123 Espresso Ave, Sector 5, Kolkata</p>
        <p class="mb-0">Phone: +91 98765 43210</p>
    </div>
    
    <hr>
    
    <div>
        <p class="mb-1"><strong>Invoice No:</strong> <?php echo htmlspecialchars($bill['bill_no']); ?></p>
        <p class="mb-1"><strong>Date:</strong> <?php echo date('d-m-Y h:i A', strtotime($bill['created_at'])); ?></p>
        <p class="mb-1"><strong>Cashier:</strong> <?php echo htmlspecialchars($bill['cashier_name']); ?></p>
        <p class="mb-0">
            <strong>Customer:</strong> 
            <?php echo !empty($bill['customer_name']) ? htmlspecialchars($bill['customer_name']) : 'Walk-In Customer'; ?>
        </p>
    </div>
    
    <hr>
    
    <table class="table table-sm">
        <thead>
            <tr>
                <th style="width: 50%;">Item</th>
                <th class="text-center" style="width: 15%;">Qty</th>
                <th class="text-end" style="width: 35%;">Amt</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = mysqli_fetch_assoc($items_res)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                    <td class="text-end">₹<?php echo number_format($item['total_amount'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <hr>
    
    <div class="d-flex justify-content-between mb-1">
        <span>Sub Total:</span>
        <span>₹<?php echo number_format($bill['sub_total'], 2); ?></span>
    </div>
    <?php if ($bill['gst_amount'] > 0): ?>
        <div class="d-flex justify-content-between mb-1">
            <span>GST (18%):</span>
            <span>₹<?php echo number_format($bill['gst_amount'], 2); ?></span>
        </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between fw-bold mb-3 fs-6">
        <span>GRAND TOTAL:</span>
        <span>₹<?php echo number_format($bill['grand_total'], 2); ?></span>
    </div>
    
    <div class="d-flex justify-content-between mb-1">
        <span>Payment Mode:</span>
        <span><?php echo $bill['payment_mode']; ?></span>
    </div>
    <div class="d-flex justify-content-between mb-0">
        <span>Payment Status:</span>
        <span class="fw-bold"><?php echo strtoupper($bill['payment_status']); ?></span>
    </div>
    
    <hr>
    
    <div class="text-center mt-3 small">
        <p class="mb-1">Thank you for dining with us!</p>
        <p class="mb-0">Please visit again.</p>
    </div>
</div>

</body>
</html>
