<?php
session_start();
require_once 'db_connect.php';
require_once 'security_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Access denied');
}

$user_id = $_SESSION['user_id'];
$format = $_GET['format'] ?? 'csv';

// Fetch tasks
$query = "SELECT 
    title, 
    category, 
    priority, 
    status, 
    due_date, 
    created_at,
    DATEDIFF(CURDATE(), due_date) as days_overdue
FROM tasks 
WHERE user_id = ? AND is_archived = 0
ORDER BY due_date";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Prepare headers
$headers = ['Title', 'Category', 'Priority', 'Status', 'Due Date', 'Created At', 'Days Overdue'];

// Prepare filename
$filename = 'TaskFlow_Tasks_' . date('Y-m-d');

// Handle different formats
switch ($format) {
    case 'csv':
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create CSV file
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);

        while ($row = $result->fetch_assoc()) {
            $data = [
                $row['title'],
                $row['category'],
                $row['priority'],
                $row['status'],
                date('Y-m-d', strtotime($row['due_date'])),
                date('Y-m-d', strtotime($row['created_at'])),
                $row['days_overdue']
            ];
            fputcsv($output, $data);
        }

        fclose($output);
        exit;

    case 'excel':
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');

        // Create Excel file
        $output = fopen('php://output', 'w');
        
        // Write headers
        foreach ($headers as $header) {
            fwrite($output, $header . '\t');
        }
        fwrite($output, "\n");

        // Write data
        while ($row = $result->fetch_assoc()) {
            $data = [
                $row['title'],
                $row['category'],
                $row['priority'],
                $row['status'],
                date('Y-m-d', strtotime($row['due_date'])),
                date('Y-m-d', strtotime($row['created_at'])),
                $row['days_overdue']
            ];
            
            foreach ($data as $value) {
                fwrite($output, $value . '\t');
            }
            fwrite($output, "\n");
        }

        fclose($output);
        exit;

    default:
        http_response_code(400);
        exit('Invalid format');
}
