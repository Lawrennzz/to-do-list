<?php
session_start();
// Strict authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include secure database connection
require_once 'includes/db_connect.php';
require_once 'includes/security_functions.php';

// Sanitize and validate task ID
$task_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$task_id) {
    $_SESSION['error_message'] = "Invalid task ID";
    header("Location: tasks.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch task with prepared statement
    $query = "SELECT * FROM tasks WHERE task_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $task_result = $stmt->get_result();

    if ($task_result->num_rows === 0) {
        $_SESSION['error_message'] = "Task not found or unauthorized access";
        header("Location: tasks.php");
        exit();
    }

    $task = $task_result->fetch_assoc();

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize inputs
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $due_date = $_POST['due_date'];
        $priority = $_POST['priority'];
        $category = $_POST['category'];
        $status = $_POST['status'];

        // Validate inputs
        $errors = [];

        if (empty($title) || strlen($title) < 3) {
            $errors[] = "Task title must be at least 3 characters";
        }

        if (empty($description) || strlen($description) < 10) {
            $errors[] = "Description must be at least 10 characters";
        }

        if (empty($due_date)) {
            $errors[] = "Due date is required";
        }

        // If no validation errors, update task
        if (empty($errors)) {
            $update_query = "UPDATE tasks SET 
                             title = ?, 
                             description = ?, 
                             due_date = ?, 
                             priority = ?, 
                             category = ?, 
                             status = ?,
                             updated_at = NOW()
                             WHERE task_id = ? AND user_id = ?";

            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssssssii", 
                $title, $description, $due_date, $priority, 
                $category, $status, $task_id, $user_id
            );

            if ($update_stmt->execute()) {
                $_SESSION['success_message'] = "Task updated successfully!";
                header("Location: tasks.php");
                exit();
            } else {
                $errors[] = "Database error: Failed to update task";
            }
        }
    }
} catch (Exception $e) {
    // Log error securely
    error_log("Task Edit Error: " . $e->getMessage());
    $_SESSION['error_message'] = "An unexpected error occurred";
    header("Location: tasks.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task | TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/edit_task.css">
    <script src="js/edit_task.js" defer></script>
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
                <h2>Edit Task</h2>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item">My Tasks</li>
                    <li class="breadcrumb-item">Edit Task</li>
                </ul>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Details</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form id="edit-task-form" method="POST" action="edit_task.php?id=<?php echo $task_id; ?>" class="task-form">
                        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                        <div class="form-group">
                            <label for="title">Task Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                            <div id="title-error" class="error-message"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($task['description']); ?></textarea>
                            <div id="description-error" class="error-message"></div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="due_date">Due Date <span class="required">*</span></label>
                                <input type="date" id="due_date" name="due_date" class="form-control" value="<?php echo htmlspecialchars($task['due_date']); ?>" required>
                                <div id="due-date-error" class="error-message"></div>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="priority">Priority <span class="required">*</span></label>
                                <select id="priority" name="priority" class="form-control" required>
                                    <option value="Low" <?php echo ($task['priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                                    <option value="Medium" <?php echo ($task['priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="High" <?php echo ($task['priority'] == 'High') ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="category">Category <span class="required">*</span></label>
                                <select id="category" name="category" class="form-control" required>
                                    <option value="Personal" <?php echo ($task['category'] == 'Personal') ? 'selected' : ''; ?>>Personal</option>
                                    <option value="Assignment" <?php echo ($task['category'] == 'Assignment') ? 'selected' : ''; ?>>Assignment</option>
                                    <option value="Discussion" <?php echo ($task['category'] == 'Discussion') ? 'selected' : ''; ?>>Discussion</option>
                                    <option value="Club Activity" <?php echo ($task['category'] == 'Club Activity') ? 'selected' : ''; ?>>Club Activity</option>
                                    <option value="Examination" <?php echo ($task['category'] == 'Examination') ? 'selected' : ''; ?>>Examination</option>
                                    <option value="Other" <?php echo ($task['category'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="pending" <?php echo ($task['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="ongoing" <?php echo ($task['status'] == 'ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="completed" <?php echo ($task['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo ($task['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Task</button>
                            <a href="tasks.php" class="btn btn-secondary">Cancel</a>
                            <button type="button" class="btn btn-danger" id="delete-task" data-id="<?php echo $task_id; ?>">Delete Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Task Confirmation Modal -->
    <div class="modal" id="delete-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirm Delete</h4>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this task? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cancel</button>
                <a href="#" id="confirm-delete" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleBtn = document.getElementById('toggle-sidebar');
        
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('expanded');
        });
        
        // Mobile menu toggle
        const menuToggle = window.matchMedia('(max-width: 768px)');
        
        function handleScreenChange(e) {
            if (e.matches) {
                sidebar.classList.add('mobile-menu');
                mainContent.classList.add('mobile-expanded');
            } else {
                sidebar.classList.remove('mobile-menu');
                mainContent.classList.remove('mobile-expanded');
            }
        }
        
        menuToggle.addListener(handleScreenChange);
        handleScreenChange(menuToggle);
    </script>
</body>
</html>