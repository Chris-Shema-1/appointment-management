<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

try {
    // Handle getting available slots for a specific doctor and date
    if (isset($_GET['action']) && $_GET['action'] === 'get_available_slots') {
        $doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
        $date = isset($_GET['date']) ? sanitize_input($_GET['date']) : '';
        
        // Validate inputs
        if (!$doctor_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid doctor ID']);
            exit;
        }
        
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid date format (use YYYY-MM-DD)']);
            exit;
        }
        
        // Check if date is in the past
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot book appointments in the past']);
            exit;
        }
        
        // Verify doctor exists
        $doctor_check = $conn->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
        $doctor_check->bind_param("i", $doctor_id);
        $doctor_check->execute();
        $check_result = $doctor_check->get_result();
        
        if ($check_result->num_rows === 0) {
            $doctor_check->close();
            http_response_code(400);
            echo json_encode(['error' => 'Doctor not found']);
            exit;
        }
        $doctor_check->close();
        
        // Get available slots
        $slots = get_available_slots($conn, $doctor_id, $date);
        
        echo json_encode([
            'slots' => $slots,
            'count' => count($slots)
        ]);
        exit;
    }
    
    // Handle getting doctor schedule information
    if (isset($_GET['action']) && $_GET['action'] === 'get_doctor_schedule') {
        $doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
        
        if (!$doctor_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid doctor ID']);
            exit;
        }
        
        // Get doctor's schedule
        $schedule_stmt = $conn->prepare("
            SELECT day_of_week, start_time, end_time, is_available 
            FROM doctor_schedule 
            WHERE doctor_id = ? AND is_available = 1
            ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
        ");
        $schedule_stmt->bind_param("i", $doctor_id);
        $schedule_stmt->execute();
        $schedule_result = $schedule_stmt->get_result();
        
        $schedule = [];
        $available_days = [];
        $days_count = 0;
        
        while ($row = $schedule_result->fetch_assoc()) {
            $available_days[] = $row['day_of_week'];
            $schedule[] = [
                'day' => $row['day_of_week'],
                'start_time' => date('h:i A', strtotime($row['start_time'])),
                'end_time' => date('h:i A', strtotime($row['end_time']))
            ];
            $days_count++;
        }
        $schedule_stmt->close();
        
        // Count total appointments for the doctor
        $appt_stmt = $conn->prepare("
            SELECT COUNT(*) as total_appointments, 
                   SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            FROM appointments 
            WHERE doctor_id = ?
        ");
        $appt_stmt->bind_param("i", $doctor_id);
        $appt_stmt->execute();
        $appt_result = $appt_stmt->get_result();
        $appt_data = $appt_result->fetch_assoc();
        $appt_stmt->close();
        
        echo json_encode([
            'available_days' => $available_days,
            'days_count' => $days_count,
            'schedule' => $schedule,
            'total_appointments' => (int)$appt_data['total_appointments'],
            'confirmed_appointments' => (int)$appt_data['confirmed'],
            'pending_appointments' => (int)$appt_data['pending']
        ]);
        exit;
    }
    
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
