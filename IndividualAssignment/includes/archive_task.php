<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_POST['task_id'] ?? null;

// Debug logging
error_log("Archive Task - User ID: $user_id, Task ID: $task_id");

if (!$task_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit();
}

// Prepare and execute update
$query = "UPDATE tasks SET is_archived = 1, updated_at = NOW() WHERE task_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    error_log("Failed to prepare statement: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param('ii', $task_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    error_log("Failed to execute statement: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to archive task: ' . $stmt->error]);
}

$stmt->close();
$conn->close();