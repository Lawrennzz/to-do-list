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
    <style>
        /* Main Content Styles */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .section-title h2 {
            font-size: 32px;
            color: #2b3044;
            margin-bottom: 15px;
        }
        
        .intro-text {
            text-align: center;
            max-width: 900px;
            margin: 40px auto;
            font-size: 18px;
            color: #4f4f4f;
        }
        
        /* Features Section */
        .features {
            display: flex;
            flex-direction: column;
            gap: 30px;
            margin-top: auto; 
            margin-bottom: auto; 
        }
        
        .feature {
            display: flex;
            gap: 20px;
            align-items: flex-start;
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .feature-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .feature-content {
            flex-grow: 1;
        }
        
        .feature-content h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #2b3044;
        }
        
        .feature-content p {
            color: #4f4f4f;
        }
        
        /* How It Works Section */
        .how-it-works {
            margin-top: auto;
            margin-bottom: auto;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 25px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 30px;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background-color: #4361ee;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .step h3 {
            font-size: 20px;
            color: #2b3044;
        }
        
        .step p {
            color: #4f4f4f;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .feature {
                flex-direction: column;
            }
            
            .nav-buttons {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
<body>
    <!-- Header with Logo and Navigation -->
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

    <!-- Main Content -->
    <main>
        <!-- Why Section -->
        <section>
            <div class="section-title">
                <h2>Why Student Task Manager?</h2>
                <div class="underline"></div>
            </div>
            
            <p class="intro-text">
                Student Task Manager was designed by students, for students. We understand the unique challenges of balancing academics, extracurricular activities, and personal life. Our platform provides a comprehensive solution to help you stay organized, meet deadlines, and reduce stress during your university journey.
            </p>
            
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <div class="feature-content">
                        <h3>Built for Academic Life</h3>
                        <p>Specially designed categories for assignments, exams, group projects, and club activities</p>
                    </div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">🧠</div>
                    <div class="feature-content">
                        <h3>Reduce Mental Load</h3>
                        <p>Stop keeping track of deadlines in your head - let our system remember for you</p>
                    </div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">📈</div>
                    <div class="feature-content">
                        <h3>Improve Performance</h3>
                        <p>Students who use task management tools show improved academic performance</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- How It Works Section -->
        <section class="how-it-works">
            <div class="section-title">
                <h2>How It Works</h2>
                <div class="underline"></div>
            </div>
            
            <div class="step">
                <div class="step-number">1</div>
                <h3>Sign Up for Free</h3>
                <p>Create your account with your university email in just a few seconds</p>
            </div>
            
            <!-- Additional steps would go here -->
        </section>
    </main>
    <footer>
    <p> 2025 Student Task Manager. This business is fictitious and part of a university course.</p>
</footer>
</body>
</html>