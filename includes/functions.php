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

/**
 * Get doctor's full name by doctor_id
 * @param object $conn - Database connection
 * @param int $doctor_id - Doctor ID
 * @return string - Doctor's full name or empty string if not found
 */
function get_doctor_name($conn, $doctor_id) {
    $stmt = $conn->prepare("SELECT u.full_name FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.doctor_id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return htmlspecialchars($row['full_name']);
    }
    $stmt->close();
    return "Unknown Doctor";
}

/**
 * Get available time slots for a doctor on a specific date (30-minute intervals)
 * @param object $conn - Database connection
 * @param int $doctor_id - Doctor ID
 * @param string $date - Date in Y-m-d format
 * @return array - Array of available times (HH:MM format)
 */
function get_available_slots($conn, $doctor_id, $date) {
    $day_of_week = date('l', strtotime($date));
    
    // Get doctor's working hours for this day
    $schedule_stmt = $conn->prepare("SELECT start_time, end_time FROM doctor_schedule 
                                     WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1");
    $schedule_stmt->bind_param("is", $doctor_id, $day_of_week);
    $schedule_stmt->execute();
    $schedule_result = $schedule_stmt->get_result();
    
    if ($schedule_result->num_rows === 0) {
        return []; // Doctor not available this day
    }
    
    $schedule = $schedule_result->fetch_assoc();
    $start_time = strtotime($schedule['start_time']);
    $end_time = strtotime($schedule['end_time']);
    $schedule_stmt->close();
    
    // Get all booked appointments for this doctor on this date
    $booked_stmt = $conn->prepare("
        SELECT appointment_time FROM appointments 
        WHERE doctor_id = ? AND appointment_date = ? AND status IN ('pending', 'confirmed')
    ");
    $booked_stmt->bind_param("is", $doctor_id, $date);
    $booked_stmt->execute();
    $booked_result = $booked_stmt->get_result();
    
    $booked_times = [];
    while ($row = $booked_result->fetch_assoc()) {
        $booked_times[] = strtotime($row['appointment_time']);
    }
    $booked_stmt->close();
    
    // Generate all possible 30-minute slots
    $available_slots = [];
    $current_time = $start_time;
    
    while ($current_time < $end_time) {
        $slot_end = $current_time + (30 * 60); // 30 minutes
        
        // Check if this slot is available (not booked and within working hours)
        $is_available = true;
        foreach ($booked_times as $booked) {
            // Check for overlap
            if ($current_time < $booked + (30 * 60) && $slot_end > $booked) {
                $is_available = false;
                break;
            }
        }
        
        if ($is_available && $slot_end <= $end_time) {
            $available_slots[] = date('H:i', $current_time);
        }
        
        $current_time += (30 * 60); // Move to next 30-minute slot
    }
    
    return $available_slots;
}
?>
