<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

require_patient();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_SESSION['user_id'];
    $appointment_id = sanitize_input($_POST['appointment_id'] ?? '');
    
    if (empty($appointment_id) || !is_numeric($appointment_id)) {
        $_SESSION['errors'] = ["Invalid appointment ID"];
        header("Location: ../pages/patient/my-appointments.php");
        exit();
    }
    
    // Verify appointment belongs to patient
    $verify_stmt = $conn->prepare("SELECT status, doctor_id FROM appointments WHERE appointment_id = ? AND patient_id = ?");
    $verify_stmt->bind_param("ii", $appointment_id, $patient_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        $_SESSION['errors'] = ["Appointment not found"];
        header("Location: ../pages/patient/my-appointments.php");
        exit();
    }
    
    $appointment = $verify_result->fetch_assoc();
    $verify_stmt->close();
    
    // Check if appointment can be cancelled
    if (!in_array($appointment['status'], ['pending', 'confirmed'])) {
        $_SESSION['errors'] = ["This appointment cannot be cancelled"];
        header("Location: ../pages/patient/my-appointments.php");
        exit();
    }
    
    // Update appointment status
    $update_stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled', updated_at = NOW() WHERE appointment_id = ?");
    $update_stmt->bind_param("i", $appointment_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        
        // Get doctor's user_id
        $doctor_user_query = $conn->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
        $doctor_user_query->bind_param("i", $appointment['doctor_id']);
        $doctor_user_query->execute();
        $doctor_user_result = $doctor_user_query->get_result();
        $doctor_user = $doctor_user_result->fetch_assoc();
        $doctor_user_query->close();
        
        // Create notifications
        $patient_name = $_SESSION['full_name'];
        $doctor_msg = "Appointment with $patient_name has been cancelled";
        $patient_msg = "Your appointment has been cancelled successfully";
        
        create_notification($conn, $doctor_user['user_id'], $doctor_msg);
        create_notification($conn, $patient_id, $patient_msg);
        
        $_SESSION['success'] = "Appointment cancelled successfully";
    } else {
        $_SESSION['errors'] = ["Failed to cancel appointment"];
    }
    
    header("Location: ../pages/patient/my-appointments.php");
    exit();
} else {
    header("Location: ../pages/patient/my-appointments.php");
    exit();
}
?>
