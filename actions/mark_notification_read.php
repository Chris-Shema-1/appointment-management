<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification_id = sanitize_input($_POST['notification_id'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (empty($notification_id) || !is_numeric($notification_id)) {
        header("HTTP/1.1 400 Bad Request");
        exit();
    }
    
    // Update notification
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect back
    $referer = $_SERVER['HTTP_REFERER'] ?? '../pages/patient/notifications.php';
    header("Location: " . $referer);
    exit();
} else {
    header("HTTP/1.1 405 Method Not Allowed");
    exit();
}
?>
