<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../common.php';

require_once __DIR__ .'/../core/Database.php';
require_once __DIR__ .'/../core/Session.php';
require_once __DIR__ .'/../models/KYCRecord.php'; // Adjust the model name as needed
require_once __DIR__ .'/../controllers/RecordController.php';

// require_once __DIR__ . '/../common.php'; 
function assets($path)
{
    return BASE_URL . '/' . ltrim($path, '/');
}

$currentScript = basename($_SERVER['PHP_SELF']);
// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
// Your database connection and RecordController instantiation go here
// if (!isset($recordController)) {
//     require_once 'controllers/RecordController.php';
//     $recordController = new RecordController($db);
// }



$db = (new Database())->connect();
$recordController = new RecordController($db);

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process the form data here

    $name = $_POST['name'];
    $mobilePhone = $_POST['mobile_phone'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];

    // Assuming you have a method to handle file uploads in your RecordController
    $cvPath = $recordController->handleFileUpload($_FILES['cv']);

    // Validate the form data (you may need more thorough validation)
    if (empty($name) || empty($mobilePhone) || empty($email) || empty($dob) || empty($address) || empty($cvPath)) {
        $errors[] = "All fields are required.";
    } else {
        // Process the form data, e.g., save to the database
        $user_id = $_SESSION['user_id'];
        $result = $recordController->createRecord($user_id, $name, $mobilePhone, $email, $dob, $address, $cvPath);

        if ($result) {
            // Record added successfully
            $successMessage = "Record added successfully!";
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "' . BASE_URL . '/views/records.php";
                    }, 2000);
                 </script>';
        } else {
            $errors[] = "Failed to add the record. Please try again.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add</title>
    <!-- Bootstrap CSS -->
    <link href="<?php echo assets('public/assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">KYCRecord</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li
                        class="nav-item <?php echo ($currentScript == 'index.php' || empty($currentScript)) ? 'active' : ''; ?>">
                        <a class="nav-link" href="<?php echo BASE_URL . '/index.php'; ?>">Home</a>
                    </li>
                    <li class="nav-item <?php echo ($currentScript == 'records.php') ? 'active' : ''; ?>">
                        <a class="nav-link" href="records.php">Add Records</a>
                    </li>
                    <li class="nav-item <?php echo ($currentScript == 'edit_record.php') ? 'active' : ''; ?>">
                        <a class="nav-link" href="edit_record.php">Edit Record</a>
                    </li>


                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <?php echo $_SESSION['email']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item"
                                        href="<?php echo BASE_URL; ?>/index.php?action=logout">Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Main Content -->
    <div class="main-content">

    <div class="container mt-5">
        <h1 class="mb-4">Add Records</h1>

        <?php
            // Display errors if any
            if (!empty($errors)) {
                echo '<div class="alert alert-danger" role="alert">';
                foreach ($errors as $error) {
                    echo $error . '<br>';
                }
                echo '</div>';
            }

            // Display success message if any
            if (!empty($successMessage)) {
                echo '<div class="alert alert-success" role="alert">' . $successMessage . '</div>';
            }
        ?>


        <form action="records.php" method="post" enctype="multipart/form-data">
            <!-- Bootstrap form styling -->
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="mobile_phone" class="form-label">Mobile Phone No:</label>
                <input type="text" name="mobile_phone" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="dob" class="form-label">Date of Birth:</label>
                <input type="date" name="dob" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address:</label>
                <textarea name="address" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
                <label for="cv" class="form-label">Upload CV:</label>
                <input type="file" name="cv" class="form-control" accept=".pdf, .doc, .docx" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Record</button>
        </form>
    </div>


    </div>

    <!-- Bootstrap JS (optional, for certain components) -->
    <script src="<?php echo assets('public/assets/js/bootstrap.bundle.min.js'); ?>"></script>

</body>

</html>