<?php
// Start the session
session_start();

// Include database connection
require_once 'db_connect.php';

// Define constants
define('REMEMBER_COOKIE_NAME', 'studenttaskmanager_remember');
define('REMEMBER_COOKIE_EXPIRY', 30 * 24 * 60 * 60); // 30 days

/**
 * Process login form submission
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithError('Please enter a valid email address.');
    }
    
    // Check if fields are empty
    if (empty($email) || empty($password)) {
        redirectWithError('Please fill in all required fields.');
    }
    
    try {
        // Prepare SQL statement to select user by email
        $stmt = $conn->prepare("SELECT id, fullname, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Check if user exists and verify password
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            
            // Generate a new session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Store user information in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            
            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $tokenHash = password_hash($token, PASSWORD_DEFAULT);
                $expires = time() + REMEMBER_COOKIE_EXPIRY;
                
                // Store token in database
                $stmt = $conn->prepare("INSERT INTO auth_tokens (user_id, token_hash, expires) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user['id'], $tokenHash, $expires);
                $stmt->execute();
                
                // Set cookie with token
                setcookie(
                    REMEMBER_COOKIE_NAME,
                    $user['id'] . ':' . $token,
                    [
                        'expires' => $expires,
                        'path' => '/',
                        'domain' => '',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );
            }
            
            // Update last login timestamp
            $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            
            // Redirect to dashboard
            header('Location: ../dashboard.php');
            exit();
            
        } else {
            // Login failed
            redirectWithError('Invalid email or password.');
        }
        
    } catch (Exception $e) {
        // Log the error (in a production environment)
        error_log('Login error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        redirectWithError('An error occurred during login. Please try again later.');
    }
}

/**
 * Redirect back to login page with error message
 * 
 * @param string $message Error message to display
 * @return void
 */
function redirectWithError($message) {
    header('Location: ../login.php?error=' . urlencode($message));
    exit();
}
?>