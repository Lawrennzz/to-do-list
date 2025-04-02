<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/security_functions.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access";
    exit();
}

// Validate task ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid task ID";
    exit();
}

$task_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch task details with user authentication
$query = "SELECT * FROM tasks WHERE task_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($task = $result->fetch_assoc()) {
?>
    <div class="task-details">
        <h2><?php echo htmlspecialchars($task['title']); ?></h2>
        <div class="task-meta">
            <p><strong>Category:</strong> <?php echo htmlspecialchars($task['category']); ?></p>
            <p><strong>Priority:</strong> <span class="priority-<?php echo strtolower($task['priority']); ?>">
                <?php echo htmlspecialchars($task['priority']); ?>
            </span></p>
            <p><strong>Status:</strong> <span class="status-<?php echo strtolower(str_replace('-', '', $task['status'])); ?>">
                <?php echo htmlspecialchars($task['status']); ?>
            </span></p>
            <p><strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($task['due_date'])); ?></p>
        </div>
        <?php if (!empty($task['description'])): ?>
            <div class="task-description">
                <h3>Description</h3>
                <p><?php echo htmlspecialchars($task['description']); ?></p>
            </div>
        <?php endif; ?>
        <div class="task-actions">
            <a href="edit_task.php?id=<?php echo $task['task_id']; ?>" class="btn btn-edit">
                <i class="fas fa-edit"></i> Edit
            </a>
            <button class="btn btn-archive" data-id="<?php echo $task['task_id']; ?>">
                <i class="fas fa-archive"></i> Archive
            </button>
        </div>
    </div>
<?php 
} else {
    echo "Task not found or you do not have permission to view this task.";
}
$stmt->close();
$conn->close();
?>