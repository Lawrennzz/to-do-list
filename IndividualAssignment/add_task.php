<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Task | TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/add_task.css">
    <script src="js/add_task.js" defer></script>
    <style>
        .popout-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
    </style>
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

    // Process form submission
    $success_message = '';
    $error_message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['add_task'])) {
            // Sanitize inputs
            $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
            $due_date = filter_var($_POST['due_date'], FILTER_SANITIZE_STRING);
            $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
            $priority = filter_var($_POST['priority'], FILTER_SANITIZE_STRING);
            $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

            // Insert task into database
            $insert_query = "INSERT INTO tasks (title, description, due_date, category, priority, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sssssss", $title, $description, $due_date, $category, $priority, $status, $_SESSION['user_id']);

            if ($insert_stmt->execute()) {
                $success_message = 'Task added successfully!';
            } else {
                $error_message = 'Failed to add task.';
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
                    <a href="add_task.php" class="menu-link active">
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
                <h2>Add New Task</h2>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item">Add Task</li>
                </ul>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Details</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success popout-message"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger popout-message"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="add_task.php" class="task-form">
                        <div class="form-group">
                            <label for="title">Task Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="due_date">Due Date <span class="required">*</span></label>
                            <input type="date" id="due_date" name="due_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category <span class="required">*</span></label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="personal">Personal</option>
                                <option value="assignment">Assignment</option>
                                <option value="discussion">Discussion</option>
                                <option value="club_activity">Club Activity</option>
                                <option value="examination">Examination</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="priority">Priority <span class="required">*</span></label>
                            <select id="priority" name="priority" class="form-control" required>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status <span class="required">*</span></label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="pending">Pending</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>