<?php
require_once 'config/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
} else {
    header("Location: auth/login.php");
}
exit();
?>
