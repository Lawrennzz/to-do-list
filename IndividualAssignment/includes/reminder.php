<?php
require_once 'db_connect.php';

/**
 * Create a new reminder for a task
 * @param int $task_id
 * @param int $days_before
 * @return bool
 */
function createReminder($task_id, $days_before) {
    global $conn;
    
    $query = "INSERT INTO reminders (task_id, days_before) 
              VALUES (?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $task_id, $days_before);
    
    return $stmt->execute();
}

/**
 * Get all reminders for a user
 * @return array
 */
function getReminders() {
    global $conn;
    
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT r.*, t.title, t.due_date 
              FROM reminders r 
              JOIN tasks t ON r.task_id = t.task_id 
              WHERE t.user_id = ? AND r.is_sent = 0 
              ORDER BY r.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Mark a reminder as sent
 * @param int $reminder_id
 * @return bool
 */
function markReminderAsSent($reminder_id) {
    global $conn;
    
    $query = "UPDATE reminders 
              SET is_sent = 1 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $reminder_id);
    
    return $stmt->execute();
}

/**
 * Delete a reminder
 * @param int $reminder_id
 * @return bool
 */
function deleteReminder($reminder_id) {
    global $conn;
    
    $query = "DELETE FROM reminders 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $reminder_id);
    
    return $stmt->execute();
}

/**
 * Get user notification preferences
 * @return array
 */
function getUserNotificationPreferences() {
    global $conn;
    
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT notifications, email_notifications 
              FROM user_settings 
              WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Update user notification preferences
 * @param bool $notifications
 * @param bool $email_notifications
 * @return bool
 */
function updateUserNotificationPreferences($notifications, $email_notifications) {
    global $conn;
    
    $user_id = $_SESSION['user_id'];
    
    $query = "UPDATE user_settings 
              SET notifications = ?, email_notifications = ? 
              WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iii', $notifications, $email_notifications, $user_id);
    
    return $stmt->execute();
}

/**
 * Create a new notification
 * @param int $task_id
 * @param string $message
 * @return bool
 */
function createNotification($task_id, $message) {
    global $conn;
    
    $user_id = $_SESSION['user_id'];
    
    $query = "INSERT INTO notifications (user_id, task_id, message) 
              VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iis', $user_id, $task_id, $message);
    
    return $stmt->execute();
}

/**
 * Get unread notifications for a user
 * @return array
 */
function getUnreadNotifications() {
    global $conn;
    
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT n.*, t.title 
              FROM notifications n 
              JOIN tasks t ON n.task_id = t.task_id 
              WHERE n.user_id = ? AND n.is_read = 0 
              ORDER BY n.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Mark a notification as read
 * @param int $notification_id
 * @return bool
 */
function markNotificationAsRead($notification_id) {
    global $conn;
    
    $query = "UPDATE notifications 
              SET is_read = 1 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $notification_id);
    
    return $stmt->execute();
}

/**
 * Send email notification
 * @param string $email
 * @param string $subject
 * @param string $message
 * @return bool
 */
function sendEmailNotification($email, $subject, $message) {
    $headers = "From: TaskFlow <noreply@taskflow.com>\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    
    return mail($email, $subject, $message, $headers);
}
?>
