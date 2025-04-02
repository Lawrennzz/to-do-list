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

// Pagination setup
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Filter options
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_priority = isset($_GET['priority']) ? $_GET['priority'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'due_date';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Build filter query
$filter_conditions = [];
$filter_params = [];
$filter_types = '';

if ($filter_category) {
    $filter_conditions[] = "category = ?";
    $filter_params[] = $filter_category;
    $filter_types .= 's';
}

if ($filter_priority) {
    $filter_conditions[] = "priority = ?";
    $filter_params[] = $filter_priority;
    $filter_types .= 's';
}

if ($filter_status) {
    $filter_conditions[] = "status = ?";
    $filter_params[] = $filter_status;
    $filter_types .= 's';
}

// Allowed sort columns and order
$allowed_sort_columns = ['title', 'due_date', 'priority', 'category', 'status'];
$allowed_sort_order = ['ASC', 'DESC'];

$sort_by = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'due_date';
$sort_order = in_array($sort_order, $allowed_sort_order) ? $sort_order : 'ASC';

// Construct query
$base_query = "FROM tasks WHERE user_id = ? AND is_archived = 0";
$base_params = [$user_id];
$base_types = 'i';

if (!empty($filter_conditions)) {
    $base_query .= " AND " . implode(" AND ", $filter_conditions);
    $base_params = array_merge($base_params, $filter_params);
    $base_types .= $filter_types;
}

// Count total tasks for pagination
$count_query = "SELECT COUNT(*) as total_tasks " . $base_query;
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param($base_types, ...$base_params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_tasks = $count_result->fetch_assoc()['total_tasks'];
$total_pages = ceil($total_tasks / $results_per_page);

// Fetch tasks
$query = "SELECT * " . $base_query . " ORDER BY $sort_by $sort_order LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$bind_params = array_merge($base_params, [$results_per_page, $offset]);
$bind_types = $base_types . 'ii';

$stmt->bind_param($bind_types, ...$bind_params);
$stmt->execute();
$tasks_result = $stmt->get_result();

// Fetch unique categories and priorities for filter dropdowns
$categories_query = "SELECT DISTINCT category FROM tasks WHERE user_id = ?";
$categories_stmt = $conn->prepare($categories_query);
$categories_stmt->bind_param('i', $user_id);
$categories_stmt->execute();
$categories_result = $categories_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks | TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/tasks.css">
    <script src="js/tasks.js" defer></script>
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
                    <a href="tasks.php" class="menu-link active">
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
                <h2>My Tasks</h2>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item">My Tasks</li>
                </ul>
            </div>

            <!-- Filter and Action Bar -->
            <div class="task-actions">
                <a href="add_task.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add New Task
                </a>
                
                <form class="filter-form" method="GET" action="tasks.php">
                    <select name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="Personal" <?php echo $filter_category === 'Personal' ? 'selected' : ''; ?>>Personal</option>
                        <option value="Assignment" <?php echo $filter_category === 'Assignment' ? 'selected' : ''; ?>>Assignment</option>
                        <option value="Discussion" <?php echo $filter_category === 'Discussion' ? 'selected' : ''; ?>>Discussion</option>
                        <option value="Club Activity" <?php echo $filter_category === 'Club Activity' ? 'selected' : ''; ?>>Club Activity</option>
                        <option value="Examination" <?php echo $filter_category === 'Examination' ? 'selected' : ''; ?>>Examination</option>
                        <option value="Other" <?php echo $filter_category === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>

                    <select name="priority" onchange="this.form.submit()">
                        <option value="">All Priorities</option>
                        <option value="High" <?php echo $filter_priority === 'High' ? 'selected' : ''; ?>>High</option>
                        <option value="Medium" <?php echo $filter_priority === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="Low" <?php echo $filter_priority === 'Low' ? 'selected' : ''; ?>>Low</option>
                    </select>

                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="ongoing" <?php echo $filter_status === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                        <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </form>
            </div>

            <!-- Tasks List -->
            <div class="tasks-list">
                <?php if ($total_tasks > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <a href="?sort=title&order=<?php echo $sort_by === 'title' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                                        Title <?php echo $sort_by === 'title' ? ($sort_order === 'ASC' ? '▲' : '▼') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=category&order=<?php echo $sort_by === 'category' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                                        Category <?php echo $sort_by === 'category' ? ($sort_order === 'ASC' ? '▲' : '▼') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=priority&order=<?php echo $sort_by === 'priority' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                                        Priority <?php echo $sort_by === 'priority' ? ($sort_order === 'ASC' ? '▲' : '▼') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=due_date&order=<?php echo $sort_by === 'due_date' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                                        Due Date <?php echo $sort_by === 'due_date' ? ($sort_order === 'ASC' ? '▲' : '▼') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=status&order=<?php echo $sort_by === 'status' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                                        Status <?php echo $sort_by === 'status' ? ($sort_order === 'ASC' ? '▲' : '▼') : ''; ?>
                                    </a>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($task = $tasks_result->fetch_assoc()): ?>
                                <?php error_log("Task ID: " . $task['task_id'] . ", User ID: " . $_SESSION['user_id']); ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['category']); ?></td>
                                    <td class="priority-<?php echo strtolower($task['priority']); ?>">
                                        <?php echo htmlspecialchars($task['priority']); ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($task['due_date'])); ?></td>
                                    <td class="status-<?php echo strtolower(str_replace('-', '', $task['status'])); ?>">
                                        <?php echo htmlspecialchars($task['status']); ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_task.php?id=<?php echo $task['task_id']; ?>" class="btn btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-archive" data-id="<?php echo $task['task_id']; ?>">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="<?php echo $page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php else: ?>
                    <div class="no-tasks">
                        <p>No tasks found. <a href="add_task.php">Add your first task!</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>