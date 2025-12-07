<?php
// Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Check if logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check user type
function is_patient() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'patient';
}

function is_doctor() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'doctor';
}

// Redirect
function redirect($page) {
    header("Location: $page");
    exit();
}

// Format date
function format_date($date) {
    return date('F j, Y', strtotime($date));
}

// Format time
function format_time($time) {
    return date('h:i A', strtotime($time));
}

// Get status badge
function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'confirmed' => '<span class="badge badge-success">Confirmed</span>',
        'completed' => '<span class="badge badge-info">Completed</span>',
        'cancelled' => '<span class="badge badge-secondary">Cancelled</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

// Create notification
function create_notification($conn, $user_id, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Get unread notification count
function get_unread_notification_count($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'];
}

// Check if date is past
function is_past_date($date) {
    return strtotime($date) < strtotime(date('Y-m-d'));
}

// Time ago display
function time_ago($date) {
    $time = strtotime($date);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return "just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " minute" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } else {
        return format_date($date);
    }
}
?>
