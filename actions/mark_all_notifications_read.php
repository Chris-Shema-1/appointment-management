<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Update all unread notifications
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
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
