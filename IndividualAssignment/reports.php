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

// Fetch task statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN priority = 'High' THEN 1 ELSE 0 END) as high_priority_tasks,
        AVG(DATEDIFF(CURDATE(), due_date)) as avg_task_delay
    FROM tasks 
    WHERE user_id = ? AND is_archived = 0
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param('i', $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result()->fetch_assoc();

// Fetch tasks for export
$export_query = "
    SELECT 
        title, 
        category, 
        priority, 
        status, 
        due_date, 
        created_at,
        DATEDIFF(CURDATE(), due_date) as days_overdue
    FROM tasks 
    WHERE user_id = ? AND is_archived = 0
    ORDER BY due_date
";
$export_stmt = $conn->prepare($export_query);
$export_stmt->bind_param('i', $user_id);
$export_stmt->execute();
$export_result = $export_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Reports | TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/reports.css">
    <script src="js/reports.js" defer></script>
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
                    <a href="reports.php" class="menu-link active">
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
            <h2>Task Reports</h2>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Reports</li>
            </ul>
        </div>

        <!-- Task Statistics -->
        <section class="task-statistics">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-list-check"></i>
                    <h3>Total Tasks</h3>
                    <p><?php echo $stats_result['total_tasks']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Completed Tasks</h3>
                    <p><?php echo $stats_result['completed_tasks']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>High Priority Tasks</h3>
                    <p><?php echo $stats_result['high_priority_tasks']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Avg Task Delay</h3>
                    <p><?php echo round($stats_result['avg_task_delay'], 1); ?> days</p>
                </div>
            </div>
        </section>

        <!-- Export Section -->
        <section class="export-section">
            <h3>Export Task Data</h3>
            <div class="export-options">
                <a href="includes/export.php?format=excel" class="btn btn-primary" id="export-excel">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
                <a href="includes/export.php?format=csv" class="btn btn-secondary" id="export-csv">
                    <i class="fas fa-file-csv"></i> Export to CSV
                </a>
            </div>
        </section>

        <!-- Task Data Table -->
        <section class="task-data-table">
            <h3>Task Details</h3>
            <div class="table-responsive">
                <table id="tasks-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Created At</th>
                            <th>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($task = $export_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['category']); ?></td>
                                <td class="priority-<?php echo strtolower($task['priority']); ?>">
                                    <?php echo htmlspecialchars($task['priority']); ?>
                                </td>
                                <td class="status-<?php echo strtolower(str_replace('-', '', $task['status'])); ?>">
                                    <?php echo htmlspecialchars($task['status']); ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($task['due_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($task['created_at'])); ?></td>
                                <td><?php echo $task['days_overdue'] > 0 ? $task['days_overdue'] : '0'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>