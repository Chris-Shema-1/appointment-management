<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $_SESSION['errors'] = [];
    
    if (empty($email) || empty($password)) {
        $_SESSION['errors'][] = "Email and password are required";
    } else {
        // Query user by email
        $stmt = $conn->prepare("SELECT user_id, full_name, email, phone, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (verify_password($password, $user['password'])) {
                // Create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['last_activity'] = time();
                
                // If doctor, get and store doctor_id
                if ($user['user_type'] === 'doctor') {
                    $doctor_stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
                    $doctor_stmt->bind_param("i", $user['user_id']);
                    $doctor_stmt->execute();
                    $doctor_result = $doctor_stmt->get_result();
                    $doctor = $doctor_result->fetch_assoc();
                    $_SESSION['doctor_id'] = $doctor['doctor_id'];
                    $doctor_stmt->close();
                }
                
                // Clear errors
                $_SESSION['errors'] = [];
                
                // Redirect based on user type
                if ($user['user_type'] === 'patient') {
                    header("Location: ../pages/patient/dashboard.php");
                } else {
                    header("Location: ../pages/doctor/dashboard.php");
                }
                exit();
            } else {
                $_SESSION['errors'][] = "Invalid email or password";
            }
        } else {
            $_SESSION['errors'][] = "Invalid email or password";
        }
        
        $stmt->close();
    }
    
    // Redirect back to login with errors
    header("Location: ../index.php");
    exit();
}

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    if (is_patient()) {
        header("Location: ../pages/patient/dashboard.php");
    } else {
        header("Location: ../pages/doctor/dashboard.php");
    }
    exit();
}
?>
