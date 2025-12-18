<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

require_patient();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_SESSION['user_id'];
    $doctor_id = sanitize_input($_POST['doctor_id'] ?? '');
    $appointment_date = sanitize_input($_POST['appointment_date'] ?? '');
    $appointment_time = sanitize_input($_POST['appointment_time'] ?? '');
    $reason = sanitize_input($_POST['reason'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    $errors = [];
    
    if (empty($doctor_id) || !is_numeric($doctor_id)) {
        $errors[] = "Valid doctor selection required";
    }
    if (empty($appointment_date) || is_past_date($appointment_date)) {
        $errors[] = "Appointment date cannot be in the past";
    }
    if (empty($appointment_time)) {
        $errors[] = "Appointment time is required";
    }
    if (strlen($reason) < 10) {
        $errors[] = "Reason must be at least 10 characters";
    }
    
    if (empty($errors)) {
        // Check if doctor exists
        $doctor_check = $conn->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
        $doctor_check->bind_param("i", $doctor_id);
        $doctor_check->execute();
        $doctor_result = $doctor_check->get_result();
        
        if ($doctor_result->num_rows === 0) {
            $errors[] = "Invalid doctor selected";
        }
        $doctor_check->close();
    }
    
    if (empty($errors)) {
        // Check if patient already has appointment at this time
        $patient_check = $conn->prepare("SELECT appointment_id FROM appointments 
                                         WHERE patient_id = ? AND appointment_date = ? AND appointment_time = ?");
        $patient_check->bind_param("iss", $patient_id, $appointment_date, $appointment_time);
        $patient_check->execute();
        if ($patient_check->get_result()->num_rows > 0) {
            $errors[] = "You already have an appointment at this time";
        }
        $patient_check->close();
    }
    
    if (empty($errors)) {
        // Get the day of week for the appointment date
        $day_of_week = date('l', strtotime($appointment_date)); // Returns e.g., 'Monday', 'Tuesday', etc.
        
        // Check if doctor is available on this day
        $availability_check = $conn->prepare("SELECT schedule_id, start_time, end_time FROM doctor_schedule 
                                              WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1");
        $availability_check->bind_param("is", $doctor_id, $day_of_week);
        $availability_check->execute();
        $availability_result = $availability_check->get_result();
        
        if ($availability_result->num_rows === 0) {
            $errors[] = "Dr. " . get_doctor_name($conn, $doctor_id) . " is not available on " . $day_of_week . "s. Please select a different date.";
        } else {
            // Doctor is available on this day, check if the requested time is within working hours
            $schedule = $availability_result->fetch_assoc();
            $start_time = $schedule['start_time'];
            $end_time = $schedule['end_time'];
            $appointment_end_time = date('H:i:s', strtotime($appointment_time . ' +30 minutes'));
            
            if ($appointment_time < $start_time || $appointment_end_time > $end_time) {
                $errors[] = "The selected time is outside of Dr. " . get_doctor_name($conn, $doctor_id) . "'s working hours (" . format_time($start_time) . " - " . format_time($end_time) . "). Please choose a different time.";
            }
        }
        $availability_check->close();
    }
    
    if (empty($errors)) {
        // Check for overlapping appointments (30-minute duration)
        $appointment_end_time = date('H:i:s', strtotime($appointment_time . ' +30 minutes'));
        
        // Find all appointments that overlap with the 30-minute window
        $conflict_check = $conn->prepare("
            SELECT a.appointment_id, 
                   TIME_FORMAT(a.appointment_time, '%H:%i') as booked_time,
                   DATE_FORMAT(a.appointment_date, '%M %d, %Y') as booked_date,
                   u.full_name as patient_name
            FROM appointments a
            JOIN users u ON a.patient_id = u.user_id
            WHERE a.doctor_id = ? 
            AND a.appointment_date = ? 
            AND a.status IN ('pending', 'confirmed')
            AND (
                (a.appointment_time < ? AND DATE_ADD(a.appointment_time, INTERVAL 30 MINUTE) > ?)
            )
        ");
        $conflict_check->bind_param("isss", $doctor_id, $appointment_date, $appointment_end_time, $appointment_time);
        $conflict_check->execute();
        $conflict_result = $conflict_check->get_result();
        
        if ($conflict_result->num_rows > 0) {
            $conflict = $conflict_result->fetch_assoc();
            $errors[] = "This time slot conflicts with an existing appointment. " . 
                       "Dr. " . get_doctor_name($conn, $doctor_id) . " has an appointment at " . 
                       $conflict['booked_time'] . " on " . $conflict['booked_date'] . ". " .
                       "Please choose a different time (appointments are 30 minutes each).";
        }
        $conflict_check->close();
    }
    
    if (empty($errors)) {
        // Insert appointment
        $insert_stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, 
                                                                 appointment_time, reason, status) 
                                       VALUES (?, ?, ?, ?, ?, 'pending')");
        $insert_stmt->bind_param("iisss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $reason);
        
        if ($insert_stmt->execute()) {
            // Get doctor's user_id
            $doctor_user_query = $conn->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
            $doctor_user_query->bind_param("i", $doctor_id);
            $doctor_user_query->execute();
            $doctor_user_result = $doctor_user_query->get_result();
            $doctor_user = $doctor_user_result->fetch_assoc();
            $doctor_user_id = $doctor_user['user_id'];
            $doctor_user_query->close();
            
            // Create notifications
            $patient_name = $_SESSION['full_name'];
            $doctor_msg = "New appointment request from $patient_name for " . format_date($appointment_date) . " at " . format_time($appointment_time);
            $patient_msg = "Your appointment request has been submitted and is pending doctor approval";
            
            create_notification($conn, $doctor_user_id, $doctor_msg);
            create_notification($conn, $patient_id, $patient_msg);
            
            $_SESSION['success'] = "Appointment booked successfully! Awaiting doctor confirmation.";
            header("Location: ../pages/patient/my-appointments.php");
            exit();
        } else {
            $errors[] = "Failed to book appointment. Please try again.";
        }
        
        $insert_stmt->close();
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../pages/patient/book-appointment.php");
        exit();
    }
} else {
    header("Location: ../pages/patient/book-appointment.php");
    exit();
}
?>
