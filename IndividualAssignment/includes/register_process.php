<?php
// Include database connection
require_once 'db_connect.php';

/**
 * Process registration form submission
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $university = filter_input(INPUT_POST, 'university', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Validate inputs
    $errors = [];
    
    // Check if fields are empty
    if (empty($fullname)) $errors[] = 'Full name is required.';
    if (empty($email)) $errors[] = 'Email is required.';
    if (empty($university)) $errors[] = 'University is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if (empty($confirm_password)) $errors[] = 'Please confirm your password.';
    if (!$terms) $errors[] = 'You must agree to the Terms & Conditions.';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Validate password strength
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    
    // If there are validation errors, redirect back with error message
    if (!empty($errors)) {
        $errorMessage = implode(' ', $errors);
        header('Location: ../register.php?error=' . urlencode($errorMessage));
        exit();
    }
    
    try {
        // Validate input data
        if (empty($fullname) || empty($email) || empty($university) || empty($password)) {
            header('Location: ../register.php?error=' . urlencode('All fields are required.')); 
            exit();
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            header('Location: ../register.php?error=' . urlencode('Email already in use. Please use a different email or login.'));
            exit();
        }

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into database
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, university, password, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $fullname, $email, $university, $passwordHash);
        if ($stmt->execute()) {
            header('Location: ../login.php?success=' . urlencode('Registration successful! Please login.'));
            exit();
        } else {
            error_log('Database insert error: ' . $stmt->error);
            header('Location: ../register.php?error=' . urlencode('An error occurred during registration. Please try again.'));
            exit();
        }

    } catch (Exception $e) {
        // Log and display the error
        error_log('Registration error: ' . $e->getMessage());
        header('Location: ../register.php?error=' . urlencode('Error: ' . $e->getMessage()));
        exit();
    }
}