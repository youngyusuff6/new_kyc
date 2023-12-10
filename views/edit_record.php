<?php
require_once __DIR__ . '/../config.php'; 
require_once __DIR__ . '/../common.php'; 
require_once '../core/Database.php';
require_once '../core/Session.php';
require_once '../models/KYCRecord.php';
require_once '../controllers/RecordController.php';

// require_once __DIR__ . '/../common.php'; 
function assets($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}


$currentScript = basename($_SERVER['PHP_SELF']);
// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);


$db = (new Database())->connect();
$recordController = new RecordController($db);

$errors = [];
$successMessage = '';

// Handle form submission (add/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if edit or delete button is clicked
    if (isset($_POST['edit_record'])) {
        $record_id = $_POST['edit_record'];
    
        // Retrieve the form data
        $name = $_POST['name'];
        $email = $_POST['email'];
        $mobile_phone = $_POST['mobile_phone'];
        $date_of_birth = $_POST['date_of_birth'];
        $address = $_POST['address'];
    
        // Check if a new file has been uploaded
        if (isset($_FILES['cv_path']) && $_FILES['cv_path']['error'] == UPLOAD_ERR_OK) {
            // Assuming you have a method to handle file uploads in your RecordController
            $cv_path = $recordController->handleFileUpload($_FILES['cv_path']);
        } else {
            // If no new file is uploaded, retain the existing CV path
            $cv_path = $_POST['existing_cv_path'];
        }
    
        // Validate the form data (you may need more thorough validation)
        if (empty($name) || empty($email) || empty($date_of_birth) || empty($address)) {
            $errors[] = "All fields are required.";
        } else {
            // Update the record in the database
            $result = $recordController->updateRecord($record_id, $name, $mobile_phone, $email, $date_of_birth, $address, $cv_path);
    
            if ($result) {
                // Record updated successfully, you can redirect or show a success message
                $successMessage = "Record updated successfully!";
            } else {
                $errors[] = "Failed to update the record. Please try again.";
            }
        }
    }
    elseif (isset($_POST['delete_record'])) {
        // Implement delete logic
         // Check if the record exists before attempting to delete
         $recordDetails = $recordController->getRecordById($record_id);

         if ($recordDetails) {
             // Record exists, proceed with deletion
             $result = $recordController->deleteRecord($record_id);
 
             if ($result) {
                 // Record deleted successfully
                 $successMessage = "Record deleted successfully!";
             } else {
                 $errors[] = "Failed to delete the record. Please try again.";
             }
         } else {
             $errors[] = "Record not found.";
         }
    }
}


$user_id = $_SESSION['user_id'];
$allRecords = $recordController->getAllRecordsForUser($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Records</title>
    <!-- Include Bootstrap CSS (adjust the path as needed) -->
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

<div class="container mt-5">
    <h1>Records</h1>

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
    <!-- Bootstrap table for displaying records -->
    <table class="table mt-3">
        <thead>
        <tr>
            <th scope="col">S/N</th>
            <th scope="col">Name</th>
            <th scope="col">Mobile Phone</th>
            <th scope="col">Email</th>
            <th scope="col">Date of Birth</th>
            <th scope="col">Address</th>
            <th scope="col">CV Path</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php $index = 1; ?>
        <?php foreach ($allRecords as $record): ?>
            <tr>
                <th scope="row"><?= $index++ ?></th>
                <td><?= $record['name'] ?></td>
                <td><?= $record['mobile_phone'] ?></td>
                <td><?= $record['email'] ?></td>
                <td><?= $record['date_of_birth'] ?></td>
                <td><?= $record['address'] ?></td>
                <td><?= $record['cv_path'] ?></td>
                <td>
                    <!-- Edit button triggers the modal -->
                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $record['id'] ?>">
                        Edit
                    </button>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="delete_record" value="<?= $record['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
           <!-- Modal for editing record -->
        <div class="modal fade" id="editModal<?= $record['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $record['id'] ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel<?= $record['id'] ?>">Edit Record</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Edit form inside the modal -->
                        <form method="post" enctype="multipart/form-data" action="">
                            <input type="hidden" name="edit_record" value="<?= $record['id'] ?>">

                            <!-- Your form fields here, pre-filled with existing record data -->
                            <label for="name">Name:</label>
                            <input type="text" name="name" value="<?= $record['name'] ?>" required>
                            <br>
                            <label for="email">Email:</label>
                            <input type="email" name="email" value="<?= $record['email'] ?>" required>
                            <br>
                            <label for="email">Mobile Phone:</label>
                            <input type="text" name="mobile_phone" value="<?= $record['mobile_phone'] ?>" required>
                            <br>
                            <label for="date_of_birth">Date of Birth:</label>
                            <input type="date" name="date_of_birth" value="<?= $record['date_of_birth'] ?>" required>
                            <br>
                            <label for="address">Address:</label>
                            <input type="text" name="address" value="<?= $record['address'] ?>" required>
                            <br>
                            <label for="cv_path">CV Path:</label>
                            <input type="file" name="cv_path">
                            <br>
                            <!-- Display the current file path -->
                            <p>Current CV Path: <?= $record['cv_path'] ?></p>
                            <input type="hidden" name="existing_cv_path" value="<?= $record['cv_path'] ?>">

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Include Bootstrap JS (adjust the path as needed) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
                setTimeout(function () {
                    document.querySelector('.alert').style.display = 'none';
                }, 5000);
</script>
</body>
</html>
