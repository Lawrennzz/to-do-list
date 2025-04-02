<?php
session_start();

// Ensure session is properly started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate random string
function generateRandomString($length = 6) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Exclude 0, 1, I, O to avoid confusion
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Generate SVG CAPTCHA
function generateCaptcha() {
    // Generate random string
    $captcha = generateRandomString();
    
    // Store in session
    $_SESSION['captcha'] = $captcha;
    
    // Set dimensions
    $width = 200;
    $height = 60;
    
    // Create SVG header
    $svg = "<?xml version='1.0' encoding='UTF-8'?>
    <svg width='" . $width . "' height='" . $height . "' xmlns='http://www.w3.org/2000/svg'>
        <rect width='100%' height='100%' fill='#f0f0f0' />
    ";
    
    // Add noise dots
    for ($i = 0; $i < 100; $i++) {
        $x = rand(0, $width);
        $y = rand(0, $height);
        $size = rand(1, 3);
        $svg .= "<circle cx='" . $x . "' cy='" . $y . "' r='" . $size . "' fill='#e6e6e6' />";
    }
    
    // Add noise lines
    for ($i = 0; $i < 5; $i++) {
        $x1 = rand(0, $width);
        $y1 = rand(0, $height);
        $x2 = rand(0, $width);
        $y2 = rand(0, $height);
        $svg .= "<line x1='" . $x1 . "' y1='" . $y1 . "' x2='" . $x2 . "' y2='" . $y2 . "' 
                stroke='#cccccc' stroke-width='1' />";
    }
    
    // Add border
    $svg .= "<rect x='0' y='0' width='" . ($width - 1) . "' height='" . ($height - 1) . "' 
            stroke='#cccccc' fill='none' />";
    
    // Add text
    $fontSize = 30;
    $x = 20;
    $y = 40;
    
    // Add each character with slight variations
    for ($i = 0; $i < strlen($captcha); $i++) {
        $charX = $x + ($i * 30);
        $charY = $y + rand(-5, 5);
        $charAngle = rand(-5, 5);
        
        // Create text element with transform for rotation
        $svg .= "<text x='" . $charX . "' y='" . $charY . "' 
                font-family='Arial, sans-serif' font-size='" . $fontSize . "' 
                fill='#000000' 
                transform='rotate(" . $charAngle . " " . ($charX + 15) . " " . $charY . ")'>
                " . $captcha[$i] . "
            </text>";
    }
    
    // Close SVG
    $svg .= "</svg>";
    
    // Output SVG
    header('Content-Type: image/svg+xml');
    echo $svg;
}

// Generate and display CAPTCHA
try {
    generateCaptcha();
} catch (Exception $e) {
    error_log("CAPTCHA generation error: " . $e->getMessage());
    header('Content-Type: text/plain');
    echo "Error generating CAPTCHA";
    exit;
}
