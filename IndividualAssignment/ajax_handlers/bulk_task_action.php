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
$task_ids = $_POST['task_ids'] ?? '';

// Validate inputs
if (empty($action) || empty($task_ids)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Convert task_ids to array
$task_ids = explode(',', $task_ids);

try {
    // Start transaction
    $conn->begin_transaction();

    switch ($action) {
        case 'delete':
            $query = "DELETE FROM tasks WHERE task_id IN (?) AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", implode(',', $task_ids), $user_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Tasks deleted successfully',
                    'affected_rows' => $stmt->affected_rows
                ]);
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'No tasks were deleted']);
            }
            break;

        case 'archive':
            $query = "UPDATE tasks SET is_archived = 1 WHERE task_id IN (?) AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", implode(',', $task_ids), $user_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Tasks archived successfully',
                    'affected_rows' => $stmt->affected_rows
                ]);
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'No tasks were archived']);
            }
            break;

        case 'complete':
            $query = "UPDATE tasks SET status = 'Completed', completion_date = NOW(), updated_at = NOW() WHERE task_id IN (?) AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", implode(',', $task_ids), $user_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Tasks marked as completed',
                    'affected_rows' => $stmt->affected_rows
                ]);
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'No tasks were completed']);
            }
            break;

        default:
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    $conn->rollback();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
