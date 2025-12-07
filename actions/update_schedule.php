<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

require_doctor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = sanitize_input($_POST['doctor_id'] ?? '');
    $day_of_week = sanitize_input($_POST['day_of_week'] ?? '');
    $start_time = sanitize_input($_POST['start_time'] ?? '');
    $end_time = sanitize_input($_POST['end_time'] ?? '');
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($day_of_week)) {
        $errors[] = "Please select a day";
    }
    if (empty($start_time)) {
        $errors[] = "Start time is required";
    }
    if (empty($end_time)) {
        $errors[] = "End time is required";
    }
    if ($start_time >= $end_time) {
        $errors[] = "End time must be after start time";
    }
    
    if (empty($errors)) {
        // Check if schedule already exists for this day
        $check_stmt = $conn->prepare("SELECT schedule_id FROM doctor_schedule WHERE doctor_id = ? AND day_of_week = ?");
        $check_stmt->bind_param("is", $doctor_id, $day_of_week);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing
            $schedule = $check_result->fetch_assoc();
            $update_stmt = $conn->prepare("UPDATE doctor_schedule SET start_time = ?, end_time = ?, is_available = ? WHERE schedule_id = ?");
            $update_stmt->bind_param("ssii", $start_time, $end_time, $is_available, $schedule['schedule_id']);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Schedule updated successfully";
            } else {
                $errors[] = "Failed to update schedule";
            }
            $update_stmt->close();
        } else {
            // Insert new
            $insert_stmt = $conn->prepare("INSERT INTO doctor_schedule (doctor_id, day_of_week, start_time, end_time, is_available) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("isssi", $doctor_id, $day_of_week, $start_time, $end_time, $is_available);
            
            if ($insert_stmt->execute()) {
                $_SESSION['success'] = "Schedule saved successfully";
            } else {
                $errors[] = "Failed to save schedule";
            }
            $insert_stmt->close();
        }
        
        $check_stmt->close();
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    
    header("Location: ../pages/doctor/schedule.php");
    exit();
} else {
    header("Location: ../pages/doctor/schedule.php");
    exit();
}
?>
