<?php
require_once '../config/config.php';

check_logged_in();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Cafe Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="card card-custom p-4 shadow-lg text-center" style="width: 100%; max-width: 420px; background: rgba(35, 22, 19, 0.85); backdrop-filter: blur(10px); border: 1px solid rgba(245, 235, 230, 0.1);">
        <div class="card-body">
            <h3 class="fw-bold text-white mb-3">Forgot Password?</h3>
            <i class="bi bi-shield-lock-fill text-warning fs-1 d-block mb-3"></i>
            
            <p class="text-muted small mb-4">
                For security reasons, password self-service resets are disabled. Please contact the primary system administrator or DB custodian to reset your credentials.
            </p>

            <div class="alert alert-info text-start small mb-4" role="alert" style="background-color: rgba(180, 83, 9, 0.15); border-color: rgba(180, 83, 9, 0.2); color: #c2b0a7;">
                <i class="bi bi-info-circle-fill me-2 text-warning"></i>
                Database Admin Seed:<br>
                <strong>admin@cafe.com</strong> / <strong>admin123</strong>
            </div>

            <a href="login.php" class="btn btn-primary w-100 py-2 fw-bold">Back to Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
