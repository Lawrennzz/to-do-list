<?php
// Start the session to check if the user is logged in
session_start();
// If the user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Task Manager - Your Academic Companion</title>
    <link rel="stylesheet" href="styles/landing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
        <div class="container">
            <nav>
                <div class="brand-title">
                    <img src="images/education.png" alt="Student Task Manager Logo" class="logo">
                    <div class="brand-name">Student Task Manager</div>
                </div>
                
                <div class="auth-buttons">
                    <a href="index.php" class="btn btn-outline">Home</a>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container" style="display: flex; align-items: center; gap: 3rem;">
            <div class="hero-content">
                <h1>Manage Your Academic Life with Ease</h1>
                <p class="hero-description">Keep track of assignments, exams, club activities, and more with our specialized to-do list for university students.</p>
                
                <div class="hero-buttons">
                    <a href="login.php" class="btn btn-primary">Get Started</a>
                    <a href="learn_more.php" class="btn btn-outline">Learn More</a>
                </div>
            </div>
            
            <div class="hero-image">
            <img src="images/student.jpg" alt="Student using Task Manager">
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Features Designed for Students</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <span class="feature-icon">📝</span>
                    <h3 class="feature-title">Task Recording</h3>
                    <p class="feature-description">Add assignments, discussions, club activities, and exams with detailed information.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">👁️</span>
                    <h3 class="feature-title">Task Monitoring</h3>
                    <p class="feature-description">View all tasks in a structured way with filtering options by category, priority, or due date.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">🔄</span>
                    <h3 class="feature-title">Status Management</h3>
                    <p class="feature-description">Mark tasks as "On-going," "Pending," or "Completed" to track your progress.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">📦</span>
                    <h3 class="feature-title">Task Archiving</h3>
                    <p class="feature-description">Store completed tasks in an archive for future reference instead of permanent deletion.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">⭐</span>
                    <h3 class="feature-title">Task Prioritization</h3>
                    <p class="feature-description">Assign priority levels to focus on what matters most – "High," "Medium," or "Low."</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">🔔</span>
                    <h3 class="feature-title">Smart Reminders</h3>
                    <p class="feature-description">Get notifications before deadlines to ensure you never miss an important submission.</p>
                </div>
            </div>
        </div>
    </section>
    <footer>
    <p> 2025 Student Task Manager. This business is fictitious and part of a university course.</p>
</footer>
</body>
</html>