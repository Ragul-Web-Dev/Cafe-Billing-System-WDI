<nav class="navbar navbar-expand-lg navbar-dark bg-dark-custom border-bottom border-secondary-subtle py-3 px-4">
    <div class="container-fluid p-0">
        <button class="btn btn-outline-light border-0 me-3" id="menu-toggle" type="button">
            <i class="bi bi-justify fs-4"></i>
        </button>
        
        <h5 class="m-0 text-white fw-bold d-none d-sm-inline-block">
            <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
        </h5>

        <div class="ms-auto">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                            <?php 
                            if (isset($_SESSION['user_name'])) {
                                echo strtoupper(substr($_SESSION['user_name'], 0, 1)); 
                            } else {
                                echo 'A';
                            }
                            ?>
                        </div>
                        <span class="d-none d-md-inline small fw-medium">
                            <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border border-secondary shadow-lg mt-2" aria-labelledby="navbarDropdown">
                        <li>
                            <div class="px-3 py-2 text-muted small border-bottom border-secondary">
                                Signed in as<br>
                                <strong class="text-white small"><?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?></strong>
                            </div>
                        </li>
                        <li><a class="dropdown-item py-2 text-danger small" href="<?php echo BASE_URL; ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
