<?php
$config_path = dirname(__DIR__) . '/config/config.php';
$db_path = dirname(__DIR__) . '/config/database.php';

if (file_exists($config_path)) {
    require_once $config_path;
}
if (file_exists($db_path)) {
    require_once $db_path;
}

check_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Cafe Billing System'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include_once 'sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include_once 'navbar.php'; ?>
            
            <div class="container-fluid py-4 px-4">
                <?php display_flash_message(); ?>
