<?php
session_start();

require_once 'config.php';
require_once 'core/Database.php';
require_once 'core/Session.php';
require_once 'models/User.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once 'controllers/RecordController.php';

$db = (new Database())->connect();
$authController = new AuthController($db);
$recordController = new RecordController($db);

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['id']);

// Get the requested action
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

// Route handling
switch ($action) {
    case 'signup':
        include 'views/signup.php';;
        break;

        case 'login':
            include 'views/login.php'; // This is where the login logic is now
            break;

    case 'logout':
        handleLogout($authController);
        break;

    case 'records':
        handleRecords($isLoggedIn, $recordController);
        break;

    case 'home':
        include 'views/home.php';
        break;

    default:
        echo "Invalid action";
}



function handleLogout($authController) {
    $authController->logout();
    header('Location: ' . BASE_URL . '/index.php?action=login');
    exit();
}

function handleRecords($isLoggedIn, $recordController) {
    // Check if user is logged in
    if (!$isLoggedIn) {
        header('Location: ' . BASE_URL . '/index.php?action=login');
        exit();
    }

    // Include the records view
    include 'views/records.php';
}
?>
