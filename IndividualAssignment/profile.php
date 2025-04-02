<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/profile-styles.css">
    <script src="js/profile.js" defer></script>
</head>
<body>
    <?php
    session_start();
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    // Include database connection
    require_once 'includes/db_connect.php';
    
    // Get user information
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Process form submission
    $success_message = '';
    $error_message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['update_profile'])) {
            // Sanitize inputs
            $fullname = filter_var($_POST['fullname'], FILTER_SANITIZE_STRING);
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
            $job_title = filter_var($_POST['job_title'], FILTER_SANITIZE_STRING);
            $bio = filter_var($_POST['bio'], FILTER_SANITIZE_STRING);
            
            // Update profile information
            $update_query = "UPDATE users SET fullname = ?, email = ?, phone = ?, job_title = ?, bio = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssssi", $fullname, $email, $phone, $job_title, $bio, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Profile updated successfully!";
                // Refresh user data
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error_message = "Error updating profile: " . $conn->error;
            }
        } else if (isset($_POST['change_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                // Check if new passwords match
                if ($new_password === $confirm_password) {
                    // Hash new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password
                    $password_query = "UPDATE users SET password = ? WHERE id = ?";
                    $password_stmt = $conn->prepare($password_query);
                    $password_stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($password_stmt->execute()) {
                        $success_message = "Password changed successfully!";
                    } else {
                        $error_message = "Error changing password: " . $conn->error;
                    }
                } else {
                    $error_message = "New passwords do not match.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        }
    }
    ?>

    <div class="layout-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-tasks fa-lg" style="color: var(--primary-color); margin-right: 0.75rem;"></i>
                    <h1>TaskFlow</h1>
                </div>
                <button class="toggle-sidebar" id="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <ul class="sidebar-menu">
                <li class="menu-item">
                    <a href="dashboard.php" class="menu-link">
                        <i class="fas fa-home menu-icon"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="add_task.php" class="menu-link">
                        <i class="fas fa-plus-circle menu-icon"></i>
                        <span class="menu-text">Add Task</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="tasks.php" class="menu-link">
                        <i class="fas fa-list-check menu-icon"></i>
                        <span class="menu-text">My Tasks</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="archive.php" class="menu-link">
                        <i class="fas fa-archive menu-icon"></i>
                        <span class="menu-text">Archive</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="calendar.php" class="menu-link">
                        <i class="fas fa-calendar-alt menu-icon"></i>
                        <span class="menu-text">Calendar</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="reports.php" class="menu-link">
                        <i class="fas fa-chart-pie menu-icon"></i>
                        <span class="menu-text">Reports</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="reminders.php" class="menu-link">
                        <i class="fas fa-bell menu-icon"></i>
                        <span class="menu-text">Reminders</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="profile.php" class="menu-link active">
                        <i class="fas fa-user menu-icon"></i>
                        <span class="menu-text">Profile</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="logout.php" class="menu-link">
                        <i class="fas fa-sign-out-alt menu-icon"></i>
                        <span class="menu-text">Logout</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                TaskFlow v1.0 | &copy; <?php echo date('Y'); ?>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="main-content">
            <div class="page-header">
                <h2>My Profile</h2>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item">Profile</li>
                </ul>
            </div>
            
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php
                        $initials = substr($user['fullname'], 0, 1);
                        echo $initials;
                        ?>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($user['fullname']); ?></h3>
                        <p><?php echo htmlspecialchars($user['job_title'] ?? 'TaskFlow User'); ?></p>
                    </div>
                </div>
                
                <div class="profile-tabs">
                    <button class="tab-button active" data-tab="profile-info">Profile Information</button>
                    <button class="tab-button" data-tab="change-password">Change Password</button>
                    <button class="tab-button" data-tab="notification-settings">Notification Settings</button>
                </div>
                
                <div class="tab-content active" id="profile-info">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Profile Information</h3>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="POST">
                                <div class="form-group">
                                    <label for="fullname">Full Name</label>
                                    <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="job_title">Job Title</label>
                                    <input type="text" class="form-control" id="job_title" name="job_title" value="<?php echo htmlspecialchars($user['job_title'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="date_joined">Date Joined</label>
                                    <input type="text" class="form-control" id="date_joined" value="<?php echo htmlspecialchars($user['created_at'] ?? date('Y-m-d')); ?>" readonly>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="change-password">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Change Password</h3>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="POST">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="password-strength-meter">
                                    <div class="meter-bar">
                                        <div class="meter-fill" id="password-strength"></div>
                                    </div>
                                    <div class="meter-text" id="password-strength-text">Password strength</div>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="notification-settings">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Notification Settings</h3>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="POST">
                                <div class="form-group">
                                    <label class="form-switch">
                                        <input type="checkbox" name="email_notifications" <?php echo (isset($user['email_notifications']) && $user['email_notifications'] ? 'checked' : ''); ?>>
                                        <span class="switch-slider"></span>
                                        <span class="switch-text">Email Notifications</span>
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label class="form-switch">
                                        <input type="checkbox" name="task_reminders" <?php echo (isset($user['task_reminders']) && $user['task_reminders'] ? 'checked' : ''); ?>>
                                        <span class="switch-slider"></span>
                                        <span class="switch-text">Task Reminders</span>
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label class="form-switch">
                                        <input type="checkbox" name="deadline_alerts" <?php echo (isset($user['deadline_alerts']) && $user['deadline_alerts'] ? 'checked' : ''); ?>>
                                        <span class="switch-slider"></span>
                                        <span class="switch-text">Deadline Alerts</span>
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label class="form-switch">
                                        <input type="checkbox" name="system_notifications" <?php echo (isset($user['system_notifications']) && $user['system_notifications'] ? 'checked' : ''); ?>>
                                        <span class="switch-slider"></span>
                                        <span class="switch-text">System Notifications</span>
                                    </label>
                                </div>
                                <button type="submit" name="update_notifications" class="btn btn-primary">Save Preferences</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Account Activity</h3>
                    </div>
                    <div class="card-body">
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-sign-in-alt"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">Last Login</div>
                                    <div class="activity-time"><?php echo date('M d, Y h:i A'); ?></div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">Tasks Created</div>
                                    <div class="activity-info">
                                        <?php 
                                        $tasks_query = "SELECT COUNT(*) as task_count FROM tasks WHERE user_id = ?";
                                        $tasks_stmt = $conn->prepare($tasks_query);
                                        $tasks_stmt->bind_param("i", $user_id);
                                        $tasks_stmt->execute();
                                        $tasks_result = $tasks_stmt->get_result();
                                        $tasks_count = $tasks_result->fetch_assoc()['task_count'];
                                        echo $tasks_count;
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">Tasks Completed</div>
                                    <div class="activity-info">
                                        <?php 
                                        $completed_query = "SELECT COUNT(*) as completed_count FROM tasks WHERE user_id = ? AND status = 'Completed'";
                                        $completed_stmt = $conn->prepare($completed_query);
                                        $completed_stmt->bind_param("i", $user_id);
                                        $completed_stmt->execute();
                                        $completed_result = $completed_stmt->get_result();
                                        $completed_count = $completed_result->fetch_assoc()['completed_count'];
                                        echo $completed_count;
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">Profile Last Updated</div>
                                    <div class="activity-time"><?php echo isset($user['updated_at']) ? date('M d, Y h:i A', strtotime($user['updated_at'])) : 'Never'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>