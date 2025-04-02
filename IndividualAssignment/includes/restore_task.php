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

if (!$task_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit();
}

// Prepare and execute update
$query = "UPDATE tasks SET is_archived = 0, status = 'not started', updated_at = NOW() WHERE task_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $task_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to restore task: ' . $conn->error]);
}
$stmt->close();
$conn->close();