<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT * FROM {$this->table} WHERE email = :email AND password = :password";
        $stmt = $this->conn->prepare($query);

        $hashedPassword = md5($password);

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);

        $stmt->execute();

        return $stmt;
    }

    public function signup($email, $password) {
        // Check if the email already exists
        if ($this->emailExists($email)) {
            return 'user_exists'; // Indicate that the user already exists
        }

        //  // Password validation
        //  if (!$this->isValidPassword($password)) {
        //     return 'invalid_password'; // Indicate that the password doesn't meet the requirements
        // }

        // Proceed with signup if the email doesn't exist
        $query = "INSERT INTO {$this->table} (email, password) VALUES (:email, :password)";
        $stmt = $this->conn->prepare($query);

        $hashedPassword = md5($password); // Note: Using md5 for demonstration purposes; use a secure hash function in production

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);

        try {
            $stmt->execute();
            return 'success'; // Indicate successful signup
        } catch (PDOException $e) {
            // Handle other potential errors here
            return 'error';
        }
    }

    public function getEmailById($user_id) {
        $query = "SELECT email FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['email'] ?? null;
    }

    private function emailExists($email) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    // private function isValidPassword($password) {
    //     // Password should contain at least one letter, one number, and be at least 5 characters long
    //     $pattern = '/^(?=.*[A-Za-z])(?=.*\d).{5,}$/';
    //     return preg_match($pattern, $password);
    // }
}
