<?php
// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// If logged in, redirect to the dashboard
if ($isLoggedIn) {
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    <div class="container-fluid">
        <h2 class="text-center">Login</h2>
        <div class="container">
            <form action="<?php echo BASE_URL; ?>/index.php?action=login" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
                <p>
                            New User? <button class="btn btn-small btn-primary" onclick="location.href='index.php?action=signup'">Sign up</button>
                </p>
            </form>

        </div>
    </div>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($authController->login($email, $password)) {
             // Redirect to dashboard page after successful login
                header('Location: views/dashboard.php');
                exit();
        } else {
            echo '<p class="text-danger text-center">Login failed. Please check your email and password.</p>';
        }
    }
    ?>
    <script src="public/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

