<?php
require_once 'db_connect.php';
require_once 'reminder.php';

// Get all reminders that haven't been sent yet
$query = "SELECT r.*, t.*, u.email 
          FROM reminders r 
          JOIN tasks t ON r.task_id = t.task_id 
          JOIN users u ON t.user_id = u.id 
          WHERE r.is_sent = 0";

$result = $conn->query($query);

while ($reminder = $result->fetch_assoc()) {
    // Calculate the reminder date
    $reminder_date = strtotime($reminder['due_date']) - ($reminder['days_before'] * 24 * 60 * 60);
    
    // If it's time to send the reminder
    if (time() >= $reminder_date) {
        // Get user notification preferences
        $prefs = getUserNotificationPreferences();
        
        // Create in-app notification
        if ($prefs['notifications']) {
            $message = "Reminder: Your task '" . $reminder['title'] . "' is due in " . 
                      $reminder['days_before'] . " days.";
            createNotification($reminder['task_id'], $message);
        }
        
        // Send email notification
        if ($prefs['email_notifications']) {
            $subject = "TaskFlow Reminder: " . $reminder['title'];
            $message = "<h2>Task Reminder</h2>
                       <p>Your task '" . htmlspecialchars($reminder['title']) . "' is due in " . 
                       $reminder['days_before'] . " days.</p>
                       <p>Due Date: " . date('F j, Y', strtotime($reminder['due_date'])) . "</p>
                       <p>Category: " . htmlspecialchars($reminder['category']) . "</p>
                       <p>Priority: " . htmlspecialchars($reminder['priority']) . "</p>
                       <p>Status: " . htmlspecialchars($reminder['status']) . "</p>
                       <hr>
                       <p>This is an automated reminder from TaskFlow. Please do not reply to this email.</p>";
            
            sendEmailNotification($reminder['email'], $subject, $message);
        }
        
        // Mark reminder as sent
        markReminderAsSent($reminder['id']);
    }
}
?>
