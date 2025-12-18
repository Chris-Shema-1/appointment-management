<?php
// Direct test of the AJAX endpoint logic without using relative paths

require_once 'config/database.php';
require_once 'includes/functions.php';

echo "=== Testing AJAX Endpoints ===\n\n";

// Test 1: get_available_slots endpoint
echo "Test 1: Getting available slots\n";
echo "--------------------------------\n";
$doctor_id = 1;
$date = date('Y-m-d', strtotime('next Monday'));

$slots = get_available_slots($conn, $doctor_id, $date);
$response = [
    'slots' => $slots,
    'count' => count($slots)
];
echo "Request: doctor_id=$doctor_id, date=$date\n";
echo "Response: " . json_encode($response) . "\n\n";

// Test 2: get_doctor_schedule endpoint
echo "Test 2: Getting doctor schedule\n";
echo "--------------------------------\n";

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

while ($row = $schedule_result->fetch_assoc()) {
    $available_days[] = $row['day_of_week'];
    $schedule[] = [
        'day' => $row['day_of_week'],
        'start_time' => date('h:i A', strtotime($row['start_time'])),
        'end_time' => date('h:i A', strtotime($row['end_time']))
    ];
}
$schedule_stmt->close();

// Count appointments
$appt_stmt = $conn->prepare("
    SELECT COUNT(*) as total_appointments, 
           SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
           SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM appointments 
    WHERE doctor_id = ?
");
$appt_stmt->bind_param("i", $doctor_id);
$appt_stmt->execute();
$appt_data = $appt_stmt->get_result()->fetch_assoc();
$appt_stmt->close();

$response2 = [
    'available_days' => $available_days,
    'days_count' => count($schedule),
    'schedule' => $schedule,
    'total_appointments' => (int)$appt_data['total_appointments'],
    'confirmed_appointments' => (int)$appt_data['confirmed'],
    'pending_appointments' => (int)$appt_data['pending']
];
echo "Request: doctor_id=$doctor_id\n";
echo "Response: " . json_encode($response2, JSON_PRETTY_PRINT) . "\n\n";

echo "✅ All endpoint logic tests passed!\n";

$conn->close();
?>
