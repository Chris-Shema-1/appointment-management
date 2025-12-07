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
        // Check if doctor has another appointment at this time
        $doctor_conflict = $conn->prepare("SELECT appointment_id FROM appointments 
                                          WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?");
        $doctor_conflict->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
        $doctor_conflict->execute();
        if ($doctor_conflict->get_result()->num_rows > 0) {
            $errors[] = "This time slot is not available for this doctor";
        }
        $doctor_conflict->close();
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
