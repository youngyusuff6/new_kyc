<?php
require_once 'config.php';
require_once 'core/Database.php';
require_once 'models/User.php';
require_once 'controllers/AuthController.php';
require_once 'models/KYCRecord.php';
require_once __DIR__ . '/controllers/RecordController.php';
require_once __DIR__ . '/vendor/autoload.php'; // Include Composer's autoloader for Firebase JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Initialize the database connection
$db = (new Database())->connect();

// Initialize the AuthController and RecordController
$authController = new AuthController($db);
$recordController = new RecordController($db);

// Check the action parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Perform actions based on the HTTP method and action parameter
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        // Handle POST requests
        if ($action === 'login') {
            handleLogin($authController);
        } elseif ($action === 'add_record') {
            handleAddRecord($recordController);
        }
        break;
    case 'PUT':
        // Handle PUT requests (for editing records)
        if ($action === 'edit_record') {
            $recordId = isset($_GET['record_id']) ? $_GET['record_id'] : null;
            handleEditRecord($recordController, $recordId);
        }
        break;
    case 'DELETE':
        // Handle DELETE requests (for deleting records)
        if ($action === 'delete_record') {
            $recordId = isset($_GET['record_id']) ? $_GET['record_id'] : null;
            handleDeleteRecord($recordController, $recordId);
        }
        break;
    default:
        // Handle other cases or send an error response
        http_response_code(400);
        echo json_encode(array('message' => 'Invalid request method'));
}

// Function to handle login and set the session
function handleLogin($authController) {
    // Get the input data (email and password)
    $data = json_decode(file_get_contents("php://input"));

    // Validate input
    if (empty($data->email) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(array('message' => 'Invalid input: Email and password are required'));
        return;
    }

    // Call the login method
    $loginResult = $authController->login($data->email, $data->password);

    if ($loginResult) {
        // Generate JWT token
        $tokenInfo = generateToken($loginResult);

        http_response_code(200);
        echo json_encode(array('message' => 'Login successful', 'token' => $tokenInfo['token']));
    } else {
        http_response_code(401);
        echo json_encode(array('message' => 'Login failed: Invalid credentials'));
    }
}

// Function to handle addition of records
function handleAddRecord($recordController) {
    // Get the JWT token from the headers
    $headers = getallheaders();
    $jwtToken = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    // Validate the JWT token
    if (!isValidToken($jwtToken)) {
        http_response_code(401);
        echo json_encode(array('message' => 'Token not valid'));
        return;
    }

    // Get the input data for adding a record from $_POST
    $name = $_POST['name'] ?? '';
    $mobile_phone = $_POST['mobile_phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $address = $_POST['address'] ?? '';

    // Handle file upload for CV
    $cv_path = handleFileUpload($_FILES['cv_path']);

    // Validate input
    if (empty($name) || empty($mobile_phone) || empty($email) ||
        empty($date_of_birth) || empty($address) || empty($cv_path)) {
        http_response_code(400);
        echo json_encode(array('message' => 'Invalid input: All fields are required'));
        exit;
    }

    // Call the method to add a record
    $result = $recordController->createRecord(
        isValidToken($jwtToken), // Use user_id from the token
        $name,
        $mobile_phone,
        $email,
        $date_of_birth,
        $address,
        $cv_path
    );

    // Check the result and send the appropriate response
    if ($result) {
        http_response_code(201); // Created
        echo json_encode(array('message' => 'Record added successfully'));
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(array('message' => 'Failed to add record'));
    }
}

// Function to handle editing a record
function handleEditRecord($recordController, $recordId) {
    // Get the JWT token from the headers
    $headers = getallheaders();
    $jwtToken = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    // Validate the JWT token
    if (!isValidToken($jwtToken)) {
        http_response_code(401);
        echo json_encode(array('message' => 'Token not valid'));
        return;
    }

    // Check if the record belongs to the logged-in user
    $userIdFromToken = isValidToken($jwtToken);
    $recordOwner = $recordController->getRecordOwner($recordId);

    if ($userIdFromToken !== $recordOwner) {
        http_response_code(403);
        echo json_encode(array('message' => 'Forbidden: You do not have permission to edit this record'));
        return;
    }

    // Get existing record data
    $existingRecord = $recordController->getRecordById($recordId);

    // Check if content type is JSON or form data
    $isJson = $_SERVER['CONTENT_TYPE'] === 'application/json';

    // Initialize variables for form data
    $name = $mobile_phone = $email = $date_of_birth = $address = $cv_path = null;

    // Get the update data from the request body
    if ($isJson) {
        $formData = json_decode(file_get_contents("php://input"));
        // Extract fields from JSON data
        $name = $formData->name ?? $existingRecord['name'];
        $mobile_phone = $formData->mobile_phone ?? $existingRecord['mobile_phone'];
        $email = $formData->email ?? $existingRecord['email'];
        $date_of_birth = $formData->date_of_birth ?? $existingRecord['date_of_birth'];
        $address = $formData->address ?? $existingRecord['address'];
    } else {
        // For form data, you can access $_POST directly
        $name = $_POST['name'] ?? $existingRecord['name'];
        $mobile_phone = $_POST['mobile_phone'] ?? $existingRecord['mobile_phone'];
        $email = $_POST['email'] ?? $existingRecord['email'];
        $date_of_birth = $_POST['date_of_birth'] ?? $existingRecord['date_of_birth'];
        $address = $_POST['address'] ?? $existingRecord['address'];

        // Check if file is uploaded and handle it
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] == UPLOAD_ERR_OK) {
            $cv_path = $recordController->handleFileUpload($_FILES['cv']);
        }
    }

    // Call the method to update the record
    $result = $recordController->updateRecord(
        $recordId,
        $name,
        $mobile_phone,
        $email,
        $date_of_birth,
        $address,
        $cv_path ?? $existingRecord['cv_path']
    );

    // Check the result and send the appropriate response
    if ($result) {
        http_response_code(200);
        echo json_encode(array('message' => 'Record updated successfully'));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Failed to update record'));
    }
}






// Function to handle the deletion of a record
function handleDeleteRecord($recordController, $recordId) {
    // Get the JWT token from the headers
    $headers = getallheaders();
    $jwtToken = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    // Validate the JWT token
    if (!isValidToken($jwtToken)) {
        http_response_code(401);
        echo json_encode(array('message' => 'Token not valid'));
        return;
    }
    // Check if the record belongs to the logged-in user
    $userIdFromToken = isValidToken($jwtToken);
    $recordOwner = $recordController->getRecordOwner($recordId);

    if ($userIdFromToken !== $recordOwner) {
        http_response_code(403);
        echo json_encode(array('message' => 'Forbidden: You do not have permission to delete this record'));
        return;
    }

    // Call the method to delete the record
    $result = $recordController->deleteRecord($recordId);

    // Check the result and send the appropriate response
    if ($result) {
        http_response_code(200);
        echo json_encode(array('message' => 'Record deleted successfully'));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Failed to delete record'));
    }
}


// Function to handle file upload
function handleFileUpload($file) {
    $uploadDirectory = '../public/uploads/';

    // Create the target directory if it doesn't exist
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    // Generate a unique filename to avoid overwriting existing files
    $uniqueFilename = "UserCV" . uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDirectory . $uniqueFilename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    } else {
        return false;
    }
}

// Function to detokenize the JWT token and get user information
function isValidToken($jwtToken) {
    // Your secret key for signing the token
    $secretKey = 'bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew='; 

     // Extract the token from the "Bearer" prefix
     $token = str_replace('Bearer ', '', $jwtToken);
     try {
         // Decode the token
         $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
 
         // Assuming your JWT includes a 'user_id' claim
         $userId = $decoded->user_id;

         // You can store the user information in a global variable or return it
         // for further processing in your application
         return $userId;
    } catch (Exception $e) {
        return false;
    }
}

// Function to generate a JWT token
function generateToken($userData) {
    $secretKey = 'bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew='; 

    // Check if $userData is an array before accessing its elements
    if (is_array($userData)) {
        $userId = isset($userData['id']) ? $userData['id'] : null;
        $email = isset($userData['email']) ? $userData['email'] : null;
    }

    $tokenData = [
        'user_id' => $userId,
        'email'   => $email,
    ];

    $token = JWT::encode($tokenData, $secretKey, 'HS256');

    return [
        'token' => $token
    ];
}

?>
