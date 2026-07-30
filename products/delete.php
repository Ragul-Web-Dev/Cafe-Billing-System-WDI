<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_auth();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = mysqli_prepare($conn, "SELECT id FROM bill_items WHERE product_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $is_billed = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($is_billed) {
        $stmt = mysqli_prepare($conn, "UPDATE products SET status = 'Inactive' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash_message('warning', 'Product cannot be deleted because it has billing history. It has been deactivated instead.');
        } else {
            set_flash_message('danger', 'Failed to deactivate product.');
        }
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash_message('success', 'Product deleted successfully.');
        } else {
            set_flash_message('danger', 'Failed to delete product.');
        }
        mysqli_stmt_close($stmt);
    }
} else {
    set_flash_message('danger', 'Invalid product ID.');
}

mysqli_close($conn);
echo "<script>window.location.href = 'index.php';</script>";
exit();
?>
