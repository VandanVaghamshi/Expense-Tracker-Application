<?php
/**
 * Authentication Controller
 * 
 * Handles user registration, login, and logout functionality.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/functions.php';

class AuthController {
    private $user;
    
    /**
     * Constructor
     * 
     * @param User $user User model instance
     */
    public function __construct($user) {
        $this->user = $user;
    }
    
    /**
     * Register a new user
     * 
     * @param array $data User registration data
     * @return array Response with status and message
     */
    public function register($data) {
        // Validate input
        if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['confirm_password'])) {
            return [
                'status' => 'error',
                'message' => 'All fields are required',
                'code' => 400
            ];
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'error',
                'message' => 'Invalid email format',
                'code' => 400
            ];
        }
        
        // Check if passwords match
        if ($data['password'] !== $data['confirm_password']) {
            return [
                'status' => 'error',
                'message' => 'Passwords do not match',
                'code' => 400
            ];
        }
        
        // Check if password is strong enough
        if (strlen($data['password']) < 8) {
            return [
                'status' => 'error',
                'message' => 'Password must be at least 8 characters long',
                'code' => 400
            ];
        }
        
        // Check if email already exists
        $this->user->email = $data['email'];
        if ($this->user->emailExists()) {
            return [
                'status' => 'error',
                'message' => 'Email already exists',
                'code' => 409
            ];
        }
        
        // Set user properties
        $this->user->name = $data['name'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        
        // Create the user
        if ($this->user->create()) {
            return [
                'status' => 'success',
                'message' => 'User registered successfully',
                'code' => 201,
                'user_id' => $this->user->id
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Unable to register user',
                'code' => 500
            ];
        }
    }
    
    /**
     * Login a user
     * 
     * @param array $data User login data
     * @return array Response with status and message
     */
    public function login($data) {
        // Validate input
        if (empty($data['email']) || empty($data['password'])) {
            return [
                'status' => 'error',
                'message' => 'Email and password are required',
                'code' => 400
            ];
        }
        
        // Attempt to login
        if ($this->user->login($data['email'], $data['password'])) {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Set session variables
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['user_name'] = $this->user->name;
            $_SESSION['logged_in'] = true;
            
            return [
                'status' => 'success',
                'message' => 'Login successful',
                'code' => 200,
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ]
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Invalid email or password',
                'code' => 401
            ];
        }
    }
    
    /**
     * Logout a user
     * 
     * @return array Response with status and message
     */
    public function logout() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        return [
            'status' => 'success',
            'message' => 'Logout successful',
            'code' => 200
        ];
    }
    
    /**
     * Check if user is logged in
     * 
     * @return boolean True if logged in, false otherwise
     */
    public static function isLoggedIn() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null User ID if logged in, null otherwise
     */
    public static function getCurrentUserId() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Get current user name
     * 
     * @return string|null User name if logged in, null otherwise
     */
    public static function getCurrentUserName() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
    }
}