<?php
function assets($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}
// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Home</title>
    <link href="<?php echo assets('public/assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
</head>
<body>
    <h1>Welcome to KYC Service</h1>

    <?php if ($isLoggedIn): ?>
        <!-- Show options for authenticated users -->
        <div class="d-flex">
            <a class="btn btn-primary" href="<?php echo  BASE_URL .'/views/records.php'; ?>">Go to Records</a>
            <form action="index.php?action=logout" method="post">
                <button class="btn btn-primary" type="submit">Logout</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Show options for non-authenticated users -->
        <button class="btn btn-primary" onclick="location.href='index.php?action=signup'">Sign Up</button>
        <button class="btn btn-primary" onclick="location.href='index.php?action=login'">Login</button>
    <?php endif; ?>
    <script src="<?php echo assets('public/assets/js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>
