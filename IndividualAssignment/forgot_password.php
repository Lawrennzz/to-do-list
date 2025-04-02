<?php
// Include database connection
require_once 'includes/db_connect.php';

session_start();

// Initialize variables
$message = '';
$messageClass = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CAPTCHA for email recovery
    if (isset($_POST['email']) && isset($_POST['captcha']) && isset($_POST['captcha-value'])) {
        if (empty($_POST['captcha']) || $_POST['captcha'] !== $_POST['captcha-value']) {
            $message = "Incorrect CAPTCHA. Please try again.";
            $messageClass = "error";
        } else {
            // Email recovery form
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Check if email exists in database
                $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    
                    // Generate a unique token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Store token in database
                    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $user['id'], $token, $expires);
                    
                    if ($stmt->execute()) {
                        // Send reset email
                        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
                        $message = "A password reset link has been sent to your email address.";
                        $messageClass = "success";
                    } else {
                        $message = "An error occurred while processing your request.";
                        $messageClass = "error";
                    }
                } else {
                    // Don't reveal if the email exists or not
                    $message = "If this email address is registered, you will receive a password reset link.";
                    $messageClass = "success";
                }
            } else {
                $message = "Please enter a valid email address.";
                $messageClass = "error";
            }
        }
    } elseif (isset($_POST['phone'])) {
        // Phone recovery form
        $phone = preg_replace('/[^0-9+]/', '', $_POST['phone']);
        
        // Basic phone validation
        if (preg_match('/^\+?[1-9]\d{9,14}$/', $phone)) {
            // Check if phone exists in database
            $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE phone = ?");
            $stmt->bind_param("s", $phone);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Generate verification code
                $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                // Store code in database
                $stmt = $conn->prepare("INSERT INTO phone_verification_codes (user_id, code, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user['id'], $verificationCode, $expires);
                
                if ($stmt->execute()) {
                    $message = "A verification code has been sent to your phone number.";
                    $messageClass = "success";
                } else {
                    $message = "An error occurred while processing your request.";
                    $messageClass = "error";
                }
            } else {
                // Don't reveal if the phone exists or not
                $message = "If this phone number is registered, you will receive a verification code.";
                $messageClass = "success";
            }
        } else {
            $message = "Please enter a valid phone number.";
            $messageClass = "error";
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/forgot_password.css">
</head>
<body>
    <div class="forgot-password-container">
        <div class="forgot-password-form" id="email-recovery-form">
            <div class="forgot-password-title">
                <h2>Forgot Password</h2>
                <p>Enter your email address to reset your password</p>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageClass; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                </div>

                <div class="captcha-container">
                    <object type="image/svg+xml" data="includes/captcha_svg.php" class="captcha-image">
                        <img src="includes/captcha_svg.php" alt="CAPTCHA" class="captcha-image">
                    </object>
                    <button type="button" id="refresh-captcha" class="refresh-captcha">
                        <i class="fas fa-sync"></i>
                    </button>
                    <input type="hidden" name="captcha-value" value="<?php echo isset($_SESSION['captcha']) ? $_SESSION['captcha'] : ''; ?>">
                    <div class="form-group">
                        <label for="captcha">Enter CAPTCHA</label>
                        <input type="text" id="captcha" name="captcha" required placeholder="Enter the code above">
                    </div>
                </div>

                <button type="submit" class="btn-primary">Send Reset Link</button>
            </form>

            <div class="alternative-recovery">
                <button type="button" id="phone-recovery-toggle" class="btn-secondary">
                    Recover Using Phone Number
                </button>
            </div>

            <a href="login.php" class="back-link">Back to Login</a>
        </div>

        <!-- Phone Recovery Form -->
        <div class="forgot-password-form hidden" id="phone-recovery-form">
            <div class="forgot-password-title">
                <h2>Forgot Password</h2>
                <p>Enter your phone number to reset your password</p>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageClass; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
                    <small class="help-text">Format: +1XXXXXXXXXX (include country code)</small>
                </div>

                <button type="submit" class="btn-primary">Send Verification Code</button>
            </form>

            <div class="alternative-recovery">
                <button type="button" id="email-recovery-toggle" class="btn-secondary">
                    Recover Using Email
                </button>
            </div>

            <a href="login.php" class="back-link">Back to Login</a>
        </div>
    </div>

    <script src="js/forgot_password.js"></script>
</body>
</html>