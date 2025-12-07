<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

require_doctor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = sanitize_input($_POST['appointment_id'] ?? '');
    $doctor_id = $_SESSION['doctor_id'];
    
    if (empty($appointment_id) || !is_numeric($appointment_id)) {
        $_SESSION['errors'] = ["Invalid appointment ID"];
        header("Location: ../pages/doctor/appointments.php");
        exit();
    }
    
    // Verify appointment belongs to doctor and is pending
    $verify_stmt = $conn->prepare("SELECT patient_id FROM appointments WHERE appointment_id = ? AND doctor_id = ? AND status = 'pending'");
    $verify_stmt->bind_param("ii", $appointment_id, $doctor_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        $_SESSION['errors'] = ["Appointment not found or not pending"];
        header("Location: ../pages/doctor/appointments.php");
        exit();
    }
    
    $appointment = $verify_result->fetch_assoc();
    $verify_stmt->close();
    
    // Update appointment status
    $update_stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed', updated_at = NOW() WHERE appointment_id = ?");
    $update_stmt->bind_param("i", $appointment_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        
        // Create notification for patient
        $patient_id = $appointment['patient_id'];
        $doctor_name = $_SESSION['full_name'];
        $patient_msg = "Your appointment request has been approved by Dr. $doctor_name";
        create_notification($conn, $patient_id, $patient_msg);
        
        $_SESSION['success'] = "Appointment approved successfully";
    } else {
        $_SESSION['errors'] = ["Failed to approve appointment"];
    }
    
    header("Location: ../pages/doctor/appointments.php");
    exit();
} else {
    header("Location: ../pages/doctor/appointments.php");
    exit();
}
?>
