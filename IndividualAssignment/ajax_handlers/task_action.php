<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$task_id = $_POST['task_id'] ?? '';

// Validate inputs
if (empty($action) || empty($task_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Ensure task belongs to the user
$check_query = "SELECT * FROM tasks WHERE task_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $task_id, $user_id);
$check_stmt->execute();
$task = $check_stmt->get_result()->fetch_assoc();

if (!$task) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Task not found or unauthorized']);
    exit();
}

try {
    switch ($action) {
        case 'delete':
            $query = "DELETE FROM tasks WHERE task_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $task_id, $user_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
            }
            break;

        case 'archive':
            $query = "UPDATE tasks SET is_archived = 1 WHERE task_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $task_id, $user_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Task archived successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to archive task']);
            }
            break;

        case 'status_update':
            $new_status = $_POST['new_status'] ?? '';
            if (empty($new_status)) {
                echo json_encode(['success' => false, 'message' => 'Missing new status']);
                exit();
            }

            $query = "UPDATE tasks SET status = ?, updated_at = NOW() WHERE task_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sii", $new_status, $task_id, $user_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Task status updated successfully',
                    'new_status' => $new_status
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
            }
            break;

        case 'complete':
            $query = "UPDATE tasks SET status = 'Completed', completion_date = NOW(), updated_at = NOW() WHERE task_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $task_id, $user_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Task marked as completed',
                    'new_status' => 'Completed'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to mark task as completed']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
