<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_POST['task_id'] ?? null;

if (!$task_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit();
}

// Prepare and execute delete
$query = "DELETE FROM tasks WHERE task_id = ? AND user_id = ? AND is_archived = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $task_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
}
$stmt->close();
$conn->close();