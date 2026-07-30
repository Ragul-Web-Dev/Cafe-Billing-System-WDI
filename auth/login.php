<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_logged_in();

if (isset($_GET['google_login']) && $_GET['google_login'] == 1) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email FROM users WHERE email = 'admin@cafe.com' LIMIT 1");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($user = mysqli_fetch_assoc($result)) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['last_activity'] = time();
        header("Location: ../dashboard/index.php");
        exit();
    }
    mysqli_stmt_close($stmt);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Please fill in all fields.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['last_activity'] = time();

                header("Location: ../dashboard/index.php");
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafe Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="card card-custom p-4 shadow-lg" style="width: 100%; max-width: 420px; background: rgba(35, 22, 19, 0.85); backdrop-filter: blur(10px); border: 1px solid rgba(245, 235, 230, 0.1);">
        <div class="card-body">
            <div class="text-center mb-4">
                <i class="bi bi-cup-hot-fill text-warning fs-1"></i>
                <h3 class="fw-bold text-white mt-2">Bean & Brew</h3>
                <p class="text-muted small">Sign in to manage billing</p>
            </div>

            <?php 
            display_flash_message(); 

            if (isset($_GET['timeout'])) {
                echo '<div class="alert alert-warning py-2 small" role="alert">Session expired. Please login again.</div>';
            }
            ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label text-muted small fw-bold">Email Address</label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email" required placeholder="admin@cafe.com" value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label text-muted small fw-bold">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-custom" id="password" name="password" required placeholder="••••••••">
                        <button class="btn btn-outline-secondary password-toggle border-start-0" type="button" data-target="password" style="background: rgba(23, 15, 13, 0.6); border-color: rgba(245, 235, 230, 0.08); color: #c2b0a7;">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold mb-3">Login</button>

                <div class="d-flex align-items-center my-3">
                    <hr class="flex-grow-1 border-secondary m-0">
                    <span class="mx-2 text-muted small">or</span>
                    <hr class="flex-grow-1 border-secondary m-0">
                </div>

                <a href="login.php?google_login=1" class="btn btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2 py-2 mb-4" style="border-color: rgba(245, 235, 230, 0.25);">
                    <i class="bi bi-google text-danger"></i>
                    <span>Sign in with Google</span>
                </a>

                <div class="text-center">
                    <span class="text-muted small">New administrator?</span>
                    <a href="register.php" class="text-warning small fw-bold ms-1 text-decoration-none">Register here</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>