<?php
require_once __DIR__ . '/../models/KYCRecord.php';

class RecordController {
    private $record;

    public function __construct($db) {
        $this->record = new KYCRecord($db);
    }

    public function getAllRecords() {
        return $this->record->getAllRecords();
    }

    public function getRecordById($id) {
        return $this->record->getRecordById($id);
    }

    public function createRecord($user_id, $name, $mobile_phone, $email, $date_of_birth, $address, $cv_path) {
        return $this->record->createRecord($user_id, $name, $mobile_phone, $email, $date_of_birth, $address, $cv_path);
    }

    public function updateRecord($id, $name, $mobile_phone, $email, $date_of_birth, $address, $cv_path) {
        return $this->record->updateRecord($id, $name, $mobile_phone, $email, $date_of_birth, $address, $cv_path);
    }

    public function deleteRecord($id) {
        return $this->record->deleteRecord($id);
    }

    public function getRecordOwner($recordId) {
        // Implement the logic to fetch the owner ID of the record from your database
        // You may need to modify this based on your database structure
        $record = $this->record->getRecordById($recordId);
    
        // Check if the result is an array
        if (is_array($record) && isset($record['user_id'])) {
            // Assuming there's a 'user_id' field in the record table
            return $record['user_id'];
        }
    }
    public function getAllRecordsForUser($userId) {
        return $this->record->getAllRecordsForUser($userId);
    }
    
    public function handleFileUpload($file) {
        $uploadDirectory = '../public/uploads/';

        // Create the target directory if it doesn't exist
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        // Generate a unique filename to avoid overwriting existing files
        $uniqueFilename = "UserCV".uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDirectory . $uniqueFilename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $targetPath;
        } else {
            return false;
        }
    }
}

