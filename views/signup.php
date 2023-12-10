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
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet">
    <title>Sign Up</title>
</head>
<body>
<div class="container-fluid">
    <h1 class="text-center">Sign Up</h1>
    <div class="container text-center">
        <form action="index.php?action=signup" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Sign Up</button>
                        <p>
                            Have an account? <button class="btn btn-small btn-primary" onclick="location.href='index.php?action=login'">Login</button>
                        </p>
            </form>
    </div>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $result = $authController->signup($email, $password);

            if ($result === 'success') {
                // Redirect to login page after successful signup
                header('Location: index.php?action=login');
                exit();
            } elseif ($result === 'user_exists') {
                echo '<p class="text-danger">User with this email already exists. Please choose a different email.</p>';
            } else {
                echo '<p class="text-danger">Signup failed. Please try again.</p>';
            }
        }
        ?>
</div>

    <script src="public/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
