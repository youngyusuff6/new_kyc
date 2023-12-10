<?php
require_once __DIR__ . '/../models/User.php'; 
require_once __DIR__ . '/../core/Session.php'; 


class AuthController {
    private $user;

    public function __construct($db) {
        $this->user = new User($db);
    }

    public function login($email, $password) {
        $stmt = $this->user->login($email, $password);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Set the session variables
            Session::start();
            Session::set('user_id', $user['id']);
            Session::set('email', $user['email']);
            Session::set('logged_in', true);


            return $user;
        } else {
            return false;
        }
    }

    public function logout() {
        Session::start();
        Session::destroy();
    }

    public function signup($email, $password) {
        $result = $this->user->signup($email, $password);

        // case 'success':
        //     return 'success'; // Successfully signed up
        // case 'user_exists':
        //     return 'user_exists'; // User with this email already exists
        // case 'invalid_password':
        //     return 'invalid_password'; // Invalid password
        // case 'error':
        // default:
        //     return 'error'; // Other error
        // }

        if ($result === 'success') {
            return 'success';
        } elseif ($result === 'user_exists') {
            return 'user_exists';
        } else {
            return 'error';
        }
    }
    
}
