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
$task_id = $_GET['task_id'] ?? '';

// Validate inputs
if (empty($task_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing task ID']);
    exit();
}

try {
    // Fetch task details
    $query = "SELECT t.*, c.category_name 
              FROM tasks t 
              LEFT JOIN categories c ON t.category_id = c.category_id 
              WHERE t.task_id = ? AND t.user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
        
        // Format dates
        $task['created_at'] = date('F j, Y', strtotime($task['created_at']));
        $task['updated_at'] = date('F j, Y', strtotime($task['updated_at']));
        $task['due_date'] = date('F j, Y', strtotime($task['due_date']));
        
        // Format status
        $task['status'] = ucfirst(str_replace('_', ' ', $task['status']));
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'task' => $task]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Task not found or unauthorized']);
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
