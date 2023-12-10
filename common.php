<?php

session_start(); 

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// If not logged in, redirect to the login page
if (!$isLoggedIn) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// If logged in, redirect to the dashboard
// if ($isLoggedIn) {
//     header('Location: ' . BASE_URL . '/views/dashboard.php');
//     exit();
// }

// Check if the last activity timestamp is set
if (isset($_SESSION['last_activity'])) {
    $inactiveTime = 600; // Set the inactive time in seconds (e.g., 600 seconds = 10 minutes)

    // Calculate the time difference
    $currentTime = time();
    $lastActivityTime = $_SESSION['last_activity'];

    if (($currentTime - $lastActivityTime) > $inactiveTime) {
        // User has been inactive for too long, log them out
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}

// Update the last activity timestamp
$_SESSION['last_activity'] = time();

?>
