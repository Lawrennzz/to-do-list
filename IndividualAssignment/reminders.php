<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/reminder.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle reminder actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                if (isset($_POST['task_id']) && isset($_POST['days_before'])) {
                    createReminder($_POST['task_id'], $_POST['days_before']);
                }
                break;
            case 'delete':
                if (isset($_POST['reminder_id'])) {
                    deleteReminder($_POST['reminder_id']);
                }
                break;
        }
    }
}

// Get reminders and notification preferences
$reminders = getReminders();
$notification_prefs = getUserNotificationPreferences();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders | TaskFlow</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/reminders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
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
                    <a href="reminders.php" class="menu-link active">
                        <i class="fas fa-bell menu-icon"></i>
                        <span class="menu-text">Reminders</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="profile.php" class="menu-link">
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

        <main class="main-content">
            <div class="page-header">
                <h2>Reminders</h2>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item active">Reminders</li>
                </ul>
            </div>

            <div class="notification-settings">
                <h3>Notification Preferences</h3>
                <form method="POST" action="settings.php">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="notifications" 
                                   <?php echo $notification_prefs['notifications'] ? 'checked' : ''; ?>>
                            Enable In-App Notifications
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="email_notifications" 
                                   <?php echo $notification_prefs['email_notifications'] ? 'checked' : ''; ?>>
                            Enable Email Notifications
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                </form>
            </div>

            <div class="reminders-section">
                <h3>Upcoming Reminders</h3>
                <div class="reminders-list">
                    <?php foreach ($reminders as $reminder): ?>
                        <div class="reminder-card">
                            <div class="reminder-content">
                                <h4><?php echo htmlspecialchars($reminder['title']); ?></h4>
                                <p>Due Date: <?php echo date('F j, Y', strtotime($reminder['due_date'])); ?></p>
                                <p>Reminder: <?php echo $reminder['days_before']; ?> days before due date</p>
                            </div>
                            <div class="reminder-actions">
                                <form method="POST" class="delete-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="reminder_id" value="<?php echo $reminder['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="add-reminder">
                    <h4>Add New Reminder</h4>
                    <form method="POST" class="reminder-form">
                        <input type="hidden" name="action" value="create">
                        <div class="form-group">
                            <label for="task_id">Select Task</label>
                            <select name="task_id" id="task_id" required>
                                <option value="">Choose a task</option>
                                <?php
                                $task_query = "SELECT task_id, title FROM tasks WHERE user_id = ? AND is_archived = 0";
                                $stmt = $conn->prepare($task_query);
                                $stmt->bind_param('i', $_SESSION['user_id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                while ($task = $result->fetch_assoc()):
                                ?>
                                <option value="<?php echo $task['task_id']; ?>">
                                    <?php echo htmlspecialchars($task['title']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="days_before">Days Before Due Date</label>
                            <input type="number" name="days_before" id="days_before" min="1" max="30" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Reminder</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="js/reminders.js"></script>
</body>
</html>
