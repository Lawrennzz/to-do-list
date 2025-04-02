<?php
/**
 * Security Functions for TaskFlow Application
 */

/**
 * Secure input by preventing SQL injection
 * @param $input The input to sanitize
 * @return string The sanitized input
 */
function secureInput($input) {
    if (is_array($input)) {
        return array_map('secureInput', $input);
    }
    if (!isset($input)) {
        return '';
    }
    $search = array(
        '@<script[^>]*?>.*?</script>@si',      // Remove JavaScript
        '@<[\!/]*?[^<>]*?>@si',              // Remove HTML tags
        '@<style[^>]*?>.*?</style>@siU',      // Remove CSS
        '@<!\[CDATA\[.*?\]\]@si',           // Remove CDATA sections
        '@<\!--.*?-->@si',                    // Remove comments
        '@<\?php.*?\?>@si',                  // Remove PHP tags
        '@<\?xml.*?\?>@si'                   // Remove XML tags
    );
    $output = preg_replace($search, '', $input);
    return htmlspecialchars(strip_tags($output), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate and sanitize email address
 * @param $email The email address to validate
 * @return string|false The sanitized email or false if invalid
 */
function validateEmail($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return secureInput($email);
    }
    return false;
}

/**
 * Validate and sanitize URL
 * @param $url The URL to validate
 * @return string|false The sanitized URL or false if invalid
 */
function validateUrl($url) {
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return secureInput($url);
    }
    return false;
}

/**
 * Generate secure random string
 * @param int $length Length of the string
 * @return string Secure random string
 */
function generateSecureString($length = 32) {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length/2));
    }
    return bin2hex(openssl_random_pseudo_bytes($length/2));
}

/**
 * Secure password hashing
 * @param $password The password to hash
 * @return string The hashed password
 */
function securePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param $password The password to verify
 * @param $hash The stored hash
 * @return bool True if password matches hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Prevent CSRF attacks
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateSecureString();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param $token The token to validate
 * @return bool True if token is valid
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Prevent XSS attacks
 * @param $data The data to sanitize
 * @return string Sanitized data
 */
function sanitizeXss($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Prevent SQL injection
 * @param $data The data to sanitize
 * @return string Sanitized data
 */
function sanitizeSql($data) {
    if (is_array($data)) {
        return array_map('sanitizeSql', $data);
    }
    return mysqli_real_escape_string($GLOBALS['conn'], $data);
}
