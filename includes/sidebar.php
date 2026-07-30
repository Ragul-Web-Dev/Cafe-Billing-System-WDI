<div class="bg-dark-sidebar border-end border-secondary-subtle" id="sidebar-wrapper">
    <div class="sidebar-heading border-bottom border-secondary-subtle py-4 px-4 d-flex align-items-center gap-2">
        <i class="bi bi-cup-hot-fill fs-3 text-warning"></i>
        <div>
            <h5 class="m-0 text-white fw-bold">Bean & Brew</h5>
            <small class="text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">Billing System</small>
        </div>
    </div>
    <div class="list-group list-group-flush px-3 py-4 gap-2">
        <a href="<?php echo BASE_URL; ?>dashboard/index.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3 <?php echo (isset($active_menu) && $active_menu === 'dashboard') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2 fs-5"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>products/index.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3 <?php echo (isset($active_menu) && $active_menu === 'products') ? 'active' : ''; ?>">
            <i class="bi bi-egg-fried fs-5"></i>
            <span>Products</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>customers/index.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3 <?php echo (isset($active_menu) && $active_menu === 'customers') ? 'active' : ''; ?>">
            <i class="bi bi-people fs-5"></i>
            <span>Customers</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>billing/index.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3 <?php echo (isset($active_menu) && $active_menu === 'billing') ? 'active' : ''; ?>">
            <i class="bi bi-receipt-cutoff fs-5"></i>
            <span>New Bill</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>payments/index.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3 <?php echo (isset($active_menu) && $active_menu === 'payments') ? 'active' : ''; ?>">
            <i class="bi bi-wallet2 fs-5"></i>
            <span>Payments</span>
        </a>
        
        <hr class="text-secondary my-3">
        
        <a href="<?php echo BASE_URL; ?>auth/logout.php" class="list-group-item list-group-item-action bg-transparent text-danger border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3">
            <i class="bi bi-box-arrow-right fs-5"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
