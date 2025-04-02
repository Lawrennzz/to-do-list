<?php
session_start();
// Strict authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db_connect.php';
require_once 'includes/security_functions.php';

$user_id = $_SESSION['user_id'];

// Get current month and year
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Calculate first and last day of the month
$first_day = mktime(0, 0, 0, $current_month, 1, $current_year);
$days_in_month = date('t', $first_day);
$first_day_of_week = date('N', $first_day);

// Fetch tasks for the current month
$tasks_query = "SELECT * FROM tasks 
                WHERE user_id = ? 
                AND MONTH(due_date) = ? 
                AND YEAR(due_date) = ? 
                AND is_archived = 0 
                ORDER BY due_date";
$tasks_stmt = $conn->prepare($tasks_query);
$tasks_stmt->bind_param('iii', $user_id, $current_month, $current_year);
$tasks_stmt->execute();
$tasks_result = $tasks_stmt->get_result();

// Organize tasks by date
$tasks_by_date = [];
while ($task = $tasks_result->fetch_assoc()) {
    $task_date = date('j', strtotime($task['due_date']));
    $tasks_by_date[$task_date][] = $task;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/calendar.css">
    <script src="js/calendar.js" defer></script>
</head>
<body>
<div class="layout-container">
    <!-- Sidebar (same as tasks.php) -->
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
        <!-- Sidebar menu (same as in tasks.php) -->
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
                <a href="calendar.php" class="menu-link active">
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

    <!-- Main Content -->
    <main class="main-content" id="main-content">
        <div class="page-header">
            <h2>Calendar</h2>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Calendar</li>
            </ul>
        </div>

        <!-- Calendar Navigation -->
        <div class="calendar-navigation">
            <a href="?month=<?php echo $current_month == 1 ? 12 : $current_month - 1; ?>&year=<?php echo $current_month == 1 ? $current_year - 1 : $current_year; ?>" class="nav-button">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h3><?php echo date('F Y', $first_day); ?></h3>
            <a href="?month=<?php echo $current_month == 12 ? 1 : $current_month + 1; ?>&year=<?php echo $current_month == 12 ? $current_year + 1 : $current_year; ?>" class="nav-button">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <div class="calendar-header">
                <div>Sun</div>
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
            </div>
            <div class="calendar-days">
                <?php 
                // Add empty cells for days before the first day of the month
                for ($i = 1; $i < $first_day_of_week; $i++) {
                    echo '<div class="calendar-day empty"></div>';
                }

                // Generate calendar days
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $has_tasks = isset($tasks_by_date[$day]);
                    $day_class = $has_tasks ? 'has-tasks' : '';
                    
                    echo "<div class='calendar-day $day_class' data-date='$day'>";
                    echo "<span class='day-number'>$day</span>";
                    
                    // Display tasks for this day
                    if ($has_tasks) {
                        echo "<div class='day-tasks'>";
                        foreach ($tasks_by_date[$day] as $task) {
                            $priority_class = strtolower($task['priority']);
                            $status_class = strtolower(str_replace('-', '', $task['status']));
                            
                            echo "<div class='task-preview priority-{$priority_class} status-{$status_class}' 
                                     data-task-id='{$task['task_id']}'>";
                            echo htmlspecialchars($task['title']);
                            echo "</div>";
                        }
                        echo "</div>";
                    }
                    
                    echo "</div>";
                }

                // Add empty cells to complete the grid
                $remaining_cells = 42 - ($first_day_of_week - 1 + $days_in_month);
                for ($i = 1; $i <= $remaining_cells; $i++) {
                    echo '<div class="calendar-day empty"></div>';
                }
                ?>
            </div>
        </div>

        <!-- Task Details Modal -->
        <div id="task-details-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div id="task-details-container"></div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const taskPreviews = document.querySelectorAll('.task-preview');
    const modal = document.getElementById('task-details-modal');
    const modalContainer = document.getElementById('task-details-container');
    const closeModal = document.querySelector('.close-modal');

    taskPreviews.forEach(preview => {
        preview.addEventListener('click', function() {
            const taskId = this.getAttribute('data-task-id');
            
            // AJAX call to fetch task details
            fetch(`includes/get_task.php?id=${taskId}`)
                .then(response => response.text())
                .then(html => {
                    modalContainer.innerHTML = html;
                    modal.style.display = 'block';
                });
        });
    });

    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
</body>
</html>