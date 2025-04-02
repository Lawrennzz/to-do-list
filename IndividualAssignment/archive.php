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
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'completion_date';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Build filter query
$filter_conditions = ["is_archived = 1"];
$filter_params = [$user_id];
$filter_types = 'i';

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

// Allowed sort columns and order
$allowed_sort_columns = ['title', 'completion_date', 'priority', 'category'];
$allowed_sort_order = ['ASC', 'DESC'];

$sort_by = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'completion_date';
$sort_order = in_array($sort_order, $allowed_sort_order) ? $sort_order : 'DESC';

// Construct query
$base_query = "FROM tasks WHERE user_id = ? AND " . implode(" AND ", $filter_conditions);
$base_params = $filter_params;
$base_types = $filter_types;

// Count total archived tasks for pagination
$count_query = "SELECT COUNT(*) as total_tasks " . $base_query;
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param($base_types, ...$base_params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_tasks = $count_result->fetch_assoc()['total_tasks'];
$total_pages = ceil($total_tasks / $results_per_page);

// Fetch archived tasks
$query = "SELECT * " . $base_query . " ORDER BY $sort_by $sort_order LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$bind_params = array_merge($base_params, [$results_per_page, $offset]);
$bind_types = $base_types . 'ii';

$stmt->bind_param($bind_types, ...$bind_params);
$stmt->execute();
$tasks_result = $stmt->get_result();

// Fetch unique categories for filter dropdown
$categories_query = "SELECT DISTINCT category FROM tasks WHERE user_id = ? AND is_archived = 1";
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
    <title>Task Archive | TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/archive.css">
    <script src="js/archive.js" defer></script>
    <script src="js/sort.js" defer></script>
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
                    <a href="archive.php" class="menu-link active">
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
                <h2>Task Archive</h2>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item">Task Archive</li>
                </ul>
            </div>

            <!-- Filter and Sort Bar -->
            <div class="archive-actions">
                <form class="filter-form" method="GET" action="archive.php">
                    <div class="filter-group">
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
                    </div>

                    <div class="sort-group">
                        <select name="sort" onchange="this.form.submit()">
                            <option value="completion_date" <?php echo $sort_by === 'completion_date' ? 'selected' : ''; ?>>Completion Date</option>
                            <option value="title" <?php echo $sort_by === 'title' ? 'selected' : ''; ?>>Title</option>
                            <option value="priority" <?php echo $sort_by === 'priority' ? 'selected' : ''; ?>>Priority</option>
                            <option value="category" <?php echo $sort_by === 'category' ? 'selected' : ''; ?>>Category</option>
                        </select>
                        <button type="button" class="sort-order-btn" onclick="toggleSortOrder()">
                            <i class="fas fa-sort-amount-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?>"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Archived Tasks List -->
            <div class="archive-list">
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
                                    <a href="?sort=completion_date&order=<?php echo $sort_by === 'completion_date' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                                        Completed On <?php echo $sort_by === 'completion_date' ? ($sort_order === 'ASC' ? '▲' : '▼') : ''; ?>
                                    </a>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($task = $tasks_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['category']); ?></td>
                                    <td class="priority-<?php echo strtolower($task['priority']); ?>">
                                        <?php echo htmlspecialchars($task['priority']); ?>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($task['completion_date'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-restore" data-id="<?php echo $task['task_id']; ?>">
                                                <i class="fas fa-undo"></i> Restore
                                            </button>
                                            <button class="btn btn-delete" data-id="<?php echo $task['task_id']; ?>">
                                                <i class="fas fa-trash"></i> Delete
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
                    <div class="no-archived-tasks">
                        <p>No archived tasks. Completed tasks will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>