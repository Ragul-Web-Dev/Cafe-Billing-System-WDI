<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_auth();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = mysqli_prepare($conn, "SELECT id FROM bills WHERE customer_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $has_bills = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($has_bills) {
        set_flash_message('danger', 'Customer cannot be deleted because they have associated billing records.');
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM customers WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash_message('success', 'Customer deleted successfully.');
        } else {
            set_flash_message('danger', 'Failed to delete customer.');
        }
        mysqli_stmt_close($stmt);
    }
} else {
    set_flash_message('danger', 'Invalid customer ID.');
}

mysqli_close($conn);
echo "<script>window.location.href = 'index.php';</script>";
exit();
?>
