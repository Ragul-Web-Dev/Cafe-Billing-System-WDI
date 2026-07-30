<?php
$page_title = 'Add Customer';
$active_menu = 'customers';
require_once '../includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_customer'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $address = sanitize($_POST['address']);

    if (empty($name)) {
        $errors[] = "Customer name is required.";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $phone, $email, $address);

        if (mysqli_stmt_execute($stmt)) {
            set_flash_message('success', 'Customer added successfully!');
            echo "<script>window.location.href = 'index.php';</script>";
            exit();
        } else {
            $errors[] = "Failed to add customer. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-white fw-bold mb-0">Add New Customer</h3>
        <p class="text-muted small">Create a customer profile to track billing history</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-sm-12">
        <div class="card card-custom">
            <div class="card-body p-4">
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add.php">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label text-muted small fw-bold">Customer Name</label>
                            <input type="text" class="form-control form-control-custom" id="name" name="name" required placeholder="e.g. John Doe" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label text-muted small fw-bold">Phone Number</label>
                            <input type="text" class="form-control form-control-custom" id="phone" name="phone" required placeholder="e.g. 9876543210" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small fw-bold">Email Address (Optional)</label>
                        <input type="email" class="form-control form-control-custom" id="email" name="email" placeholder="john@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="mb-4">
                        <label for="address" class="form-label text-muted small fw-bold">Billing Address (Optional)</label>
                        <textarea class="form-control form-control-custom" id="address" name="address" rows="3" placeholder="Enter street address..."><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="index.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" name="save_customer" class="btn btn-primary px-4">Save Customer</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
require_once '../includes/footer.php';
?>
