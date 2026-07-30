<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_logged_in();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "This email is already registered.";
        }
        mysqli_stmt_close($stmt);

        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                set_flash_message('success', 'Registration successful! You can now log in.');
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Registration failed. Please try again later.";
            }
            mysqli_stmt_close($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cafe Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="card card-custom p-4 shadow-lg" style="width: 100%; max-width: 450px; background: rgba(35, 22, 19, 0.85); backdrop-filter: blur(10px); border: 1px solid rgba(245, 235, 230, 0.1);">
        <div class="card-body">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus-fill text-warning fs-1"></i>
                <h3 class="fw-bold text-white mt-2">Create Account</h3>
                <p class="text-muted small">Register as a Cafe Administrator</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label text-muted small fw-bold">Full Name</label>
                    <input type="text" class="form-control form-control-custom" id="name" name="name" required placeholder="Enter full name" value="<?php echo isset($_POST['name']) ? sanitize($_POST['name']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label text-muted small fw-bold">Email Address</label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email" required placeholder="name@example.com" value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label text-muted small fw-bold">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-custom" id="password" name="password" required placeholder="At least 6 characters">
                        <button class="btn btn-outline-secondary password-toggle border-start-0" type="button" data-target="password" style="background: rgba(23, 15, 13, 0.6); border-color: rgba(245, 235, 230, 0.08); color: #c2b0a7;">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label text-muted small fw-bold">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
                        <button class="btn btn-outline-secondary password-toggle border-start-0" type="button" data-target="confirm_password" style="background: rgba(23, 15, 13, 0.6); border-color: rgba(245, 235, 230, 0.08); color: #c2b0a7;">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="register" class="btn btn-primary w-100 py-2 fw-bold mb-3">Register</button>

                <div class="text-center">
                    <span class="text-muted small">Already have an account?</span>
                    <a href="login.php" class="text-warning small fw-bold ms-1 text-decoration-none">Login here</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
