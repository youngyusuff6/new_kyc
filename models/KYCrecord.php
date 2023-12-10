<?php
class KYCRecord {
    private $conn;
    private $table = 'kyc_records';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllRecords() {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAllRecordsForUser($userId) {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // public function getRecordById($id) {
    //     $query = "SELECT * FROM {$this->table} WHERE id = :id";
    //     $stmt = $this->conn->prepare($query);
    //     $stmt->bindParam(':id', $id);
    //     $stmt->execute();
    //     return $stmt;
    // }
    public function getRecordById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createRecord($user_id, $name, $mobile_phone, $email, $date_of_birth, $address, $cv_path) {
        $query = "INSERT INTO {$this->table} (user_id, name, mobile_phone, email, date_of_birth, address, cv_path) VALUES (:user_id, :name, :mobile_phone, :email, :date_of_birth, :address, :cv_path)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':mobile_phone', $mobile_phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':date_of_birth', $date_of_birth);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':cv_path', $cv_path);

        $stmt->execute();

        return $stmt;
    }

    public function updateRecord($id, $name, $mobile_phone, $email, $date_of_birth, $address, $cv_path) {
        $query = "UPDATE {$this->table} SET name = :name, mobile_phone = :mobile_phone, email = :email, date_of_birth = :date_of_birth, address = :address, cv_path = :cv_path WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':mobile_phone', $mobile_phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':date_of_birth', $date_of_birth);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':cv_path', $cv_path);
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        return $stmt;
    }

    public function deleteRecord($id) {
        // Retrieve the record to get the file path
        $record = $this->getRecordById($id);

        if ($record) {
            // Assuming $record['cv_path'] contains the file path
            $cvPath = $record['cv_path'];  // This is likely where the error occurs

            // Delete the file
            if (file_exists($cvPath)) {
                unlink($cvPath);
            }

            // Now, proceed to delete the record from the database
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt;
        } else {
            return false; // Record not found
        }

    }
}