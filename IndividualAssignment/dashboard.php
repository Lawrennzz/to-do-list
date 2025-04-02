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

// Fetch dashboard statistics
$stats_queries = [
    'total_tasks' => "SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND is_archived = 0",
    'completed_tasks' => "SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = 'completed' AND is_archived = 0",
    'overdue_tasks' => "SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND due_date < CURDATE() AND status != 'completed' AND is_archived = 0",
    'high_priority_tasks' => "SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND priority = 'High' AND status != 'completed' AND is_archived = 0"
];

$dashboard_stats = [];
foreach ($stats_queries as $key => $query) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dashboard_stats[$key] = $result->fetch_assoc()['count'];
}

// Fetch recent tasks
$recent_tasks_query = "SELECT * FROM tasks WHERE user_id = ? AND is_archived = 0 ORDER BY due_date ASC LIMIT 5";
$recent_tasks_stmt = $conn->prepare($recent_tasks_query);
$recent_tasks_stmt->bind_param('i', $user_id);
$recent_tasks_stmt->execute();
$recent_tasks_result = $recent_tasks_stmt->get_result();

// Fetch tasks by category
$category_tasks_query = "SELECT category, COUNT(*) as count FROM tasks WHERE user_id = ? AND is_archived = 0 GROUP BY category";
$category_tasks_stmt = $conn->prepare($category_tasks_query);
$category_tasks_stmt->bind_param('i', $user_id);
$category_tasks_stmt->execute();
$category_tasks_result = $category_tasks_stmt->get_result();
$category_tasks = [];
while ($row = $category_tasks_result->fetch_assoc()) {
    $category_tasks[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <script src="js/dashboard.js" defer></script>
</head>
<body>
<div class="layout-container">
    <!-- Sidebar (identical to tasks.php) -->
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
                <a href="dashboard.php" class="menu-link active">
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
            <h2>Dashboard</h2>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Dashboard</li>
            </ul>
        </div>

        <!-- Dashboard Statistics -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div class="stat-content">
                    <h3><?php echo $dashboard_stats['total_tasks']; ?></h3>
                    <p>Total Tasks</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-content">
                    <h3><?php echo $dashboard_stats['completed_tasks']; ?></h3>
                    <p>Completed Tasks</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-content">
                    <h3><?php echo $dashboard_stats['overdue_tasks']; ?></h3>
                    <p>Overdue Tasks</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-content">
                    <h3><?php echo $dashboard_stats['high_priority_tasks']; ?></h3>
                    <p>High Priority Tasks</p>
                </div>
            </div>
        </div>

        <!-- Recent Tasks and Task Categories -->
        <div class="dashboard-content">
            <div class="recent-tasks">
                <h3>Recent Tasks</h3>
                <?php if ($recent_tasks_result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($task = $recent_tasks_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['category']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($task['due_date'])); ?></td>
                                    <td class="status-<?php echo strtolower(str_replace('-', '', $task['status'])); ?>">
                                        <?php echo htmlspecialchars($task['status']); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No recent tasks. <a href="add_task.php">Add a task</a></p>
                <?php endif; ?>
            </div>

            <div class="task-categories">
                <h3>Task Categories</h3>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Category Chart
    const categoryData = <?php echo json_encode($category_tasks); ?>;
    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: categoryData.map(cat => cat.category),
            datasets: [{
                data: categoryData.map(cat => cat.count),
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', 
                    '#4BC0C0', '#9966FF', '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Task Categories'
            }
        }
    });
</script>
</body>
</html>