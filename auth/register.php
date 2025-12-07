<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Initialize errors and form data
$_SESSION['errors'] = [];
$_SESSION['form_data'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = sanitize_input($_POST['user_type'] ?? '');
    
    // Store form data for repopulation
    $_SESSION['form_data'] = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'user_type' => $user_type
    ];
    
    // Validate inputs
    if (empty($full_name)) {
        $_SESSION['errors'][] = "Full name is required";
    }
    if (empty($email) || !validate_email($email)) {
        $_SESSION['errors'][] = "Valid email is required";
    }
    if (empty($phone)) {
        $_SESSION['errors'][] = "Phone number is required";
    }
    if (strlen($password) < 6) {
        $_SESSION['errors'][] = "Password must be at least 6 characters";
    }
    if ($password !== $confirm_password) {
        $_SESSION['errors'][] = "Passwords do not match";
    }
    if ($user_type !== 'patient' && $user_type !== 'doctor') {
        $_SESSION['errors'][] = "Invalid user type selected";
    }
    
    if ($user_type === 'doctor') {
        $specialization = sanitize_input($_POST['specialization'] ?? '');
        $qualification = sanitize_input($_POST['qualification'] ?? '');
        $experience_years = (int)($_POST['experience_years'] ?? 0);
        $consultation_fee = (float)($_POST['consultation_fee'] ?? 0);
        
        if (empty($specialization)) {
            $_SESSION['errors'][] = "Specialization is required for doctors";
        }
        if (empty($qualification)) {
            $_SESSION['errors'][] = "Qualification is required for doctors";
        }
        if ($experience_years < 0) {
            $_SESSION['errors'][] = "Years of experience must be a valid number";
        }
        if ($consultation_fee < 0) {
            $_SESSION['errors'][] = "Consultation fee must be a valid number";
        }
    }
    
    // Check if email already exists
    if (empty($_SESSION['errors'])) {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['errors'][] = "Email already registered";
        }
        $check_stmt->close();
    }
    
    // If no errors, proceed with registration
    if (empty($_SESSION['errors'])) {
        $hashed_password = hash_password($password);
        
        // Insert into users table
        $user_stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, user_type) VALUES (?, ?, ?, ?, ?)");
        $user_stmt->bind_param("sssss", $full_name, $email, $phone, $hashed_password, $user_type);
        
        if ($user_stmt->execute()) {
            $user_id = $user_stmt->insert_id;
            $user_stmt->close();
            
            if ($user_type === 'doctor') {
                $specialization = sanitize_input($_POST['specialization'] ?? '');
                $qualification = sanitize_input($_POST['qualification'] ?? '');
                $experience_years = (int)($_POST['experience_years'] ?? 0);
                $consultation_fee = (float)($_POST['consultation_fee'] ?? 0);
                
                $doctor_stmt = $conn->prepare("INSERT INTO doctors (user_id, specialization, qualification, experience_years, consultation_fee) VALUES (?, ?, ?, ?, ?)");
                $doctor_stmt->bind_param("issid", $user_id, $specialization, $qualification, $experience_years, $consultation_fee);
                
                if (!$doctor_stmt->execute()) {
                    $_SESSION['errors'][] = "Failed to create doctor profile";
                    // Optionally log the error
                    error_log("Doctor profile creation error: " . $doctor_stmt->error);
                }
                $doctor_stmt->close();
            }
            
            // Only create welcome notification if no errors occurred
            if (empty($_SESSION['errors'])) {
                // Create welcome notification
                $welcome_msg = "Welcome to Appointment Management System! Your account has been created successfully.";
                create_notification($conn, $user_id, $welcome_msg);
                
                // Clear errors and form data
                $_SESSION['errors'] = [];
                $_SESSION['form_data'] = [];
                
                // Redirect to login with success message
                $_SESSION['success'] = "Registration successful! Please log in.";
                header("Location: ../index.php");
                exit();
            } else {
                // Redirect back with errors
                header("Location: ../register.php");
                exit();
            }
        } else {
            $_SESSION['errors'][] = "Registration failed. Please try again.";
            error_log("User registration error: " . $user_stmt->error);
        }
    }
    
    // Redirect back to registration page with errors
    header("Location: ../register.php");
    exit();
}

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    if (is_patient()) {
        redirect("../pages/patient/dashboard.php");
    } else {
        redirect("../pages/doctor/dashboard.php");
    }
}
?>
