<?php
require_once __DIR__ . '/../config.php'; 
require_once __DIR__ . '/../common.php'; 

// require_once __DIR__ . '/../common.php'; 
function assets($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}


$currentScript = basename($_SERVER['PHP_SELF']);
// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="<?php echo assets('public/assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">KYCRecord</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item <?php echo ($currentScript == 'index.php' || empty($currentScript)) ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo BASE_URL . '/index.php';?>">Home</a>
                </li>
                <li class="nav-item <?php echo ($currentScript == 'records.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="records.php">Add Records</a>
                </li>
                <li class="nav-item <?php echo ($currentScript == 'edit_record.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="edit_record.php">Edit Record</a>
                </li>
               
                
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $_SESSION['email']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/index.php?action=logout">Logout</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>


    <!-- Main Content -->
    <div class="main-content">
        <!-- Content will be dynamically loaded here -->
    </div>

    <!-- Bootstrap JS (optional, for certain components) -->
    <script src="<?php echo assets('public/assets/js/bootstrap.bundle.min.js'); ?>"></script>
   <!-- <script>
        // Toggle sidebar
        $('#sidebarCollapse').on('click', function () {
            $('.sidebar').toggleClass('active');
            $('.main-content').toggleClass('active');
        });
    </script> -->
</body>
</html>
