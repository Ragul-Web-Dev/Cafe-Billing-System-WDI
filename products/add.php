<?php
$page_title = 'Add Product';
$active_menu = 'products';
require_once '../includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $name = sanitize($_POST['name']);
    $category = sanitize($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $status = sanitize($_POST['status']);

    if (empty($name)) {
        $errors[] = "Product name is required.";
    }
    if (empty($category)) {
        $errors[] = "Category is required.";
    }
    if ($price <= 0) {
        $errors[] = "Price must be a positive number.";
    }
    if ($quantity < 0) {
        $errors[] = "Quantity cannot be negative.";
    }
    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO products (name, category, price, quantity, status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssdis", $name, $category, $price, $quantity, $status);

        if (mysqli_stmt_execute($stmt)) {
            set_flash_message('success', 'Product added successfully!');
            echo "<script>window.location.href = 'index.php';</script>";
            exit();
        } else {
            $errors[] = "Failed to add product. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-white fw-bold mb-0">Add New Product</h3>
        <p class="text-muted small">Enter product specifications to add it to inventory</p>
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
                    <div class="mb-3">
                        <label for="name" class="form-label text-muted small fw-bold">Product Name</label>
                        <input type="text" class="form-control form-control-custom" id="name" name="name" required placeholder="e.g. Cappuccino" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label text-muted small fw-bold">Category</label>
                            <select class="form-select form-select-custom" id="category" name="category" required>
                                <option value="" disabled selected>Select Category</option>
                                <option value="Hot Coffee" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Hot Coffee') ? 'selected' : ''; ?>>Hot Coffee</option>
                                <option value="Cold Coffee" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Cold Coffee') ? 'selected' : ''; ?>>Cold Coffee</option>
                                <option value="Tea" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Tea') ? 'selected' : ''; ?>>Tea/Tisanes</option>
                                <option value="Bakery" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Bakery') ? 'selected' : ''; ?>>Bakery & Pastries</option>
                                <option value="Snacks" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Snacks') ? 'selected' : ''; ?>>Snacks & Appetizers</option>
                                <option value="Beverages" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Beverages') ? 'selected' : ''; ?>>Other Beverages</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label text-muted small fw-bold">Price (₹)</label>
                            <input type="number" step="0.01" class="form-control form-control-custom" id="price" name="price" required placeholder="e.g. 75.00" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label text-muted small fw-bold">Stock Quantity</label>
                            <input type="number" class="form-control form-control-custom" id="quantity" name="quantity" required placeholder="e.g. 100" value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '0'; ?>">
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="status" class="form-label text-muted small fw-bold">Status</label>
                            <select class="form-select form-select-custom" id="status" name="status" required>
                                <option value="Active" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="index.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" name="save_product" class="btn btn-primary px-4">Save Product</button>
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
