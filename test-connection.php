<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "=== Database Connection Test ===\n\n";

// Check doctor_schedule table
$schedule_result = $conn->query("SELECT COUNT(*) as count FROM doctor_schedule");
$schedule_row = $schedule_result->fetch_assoc();
echo "Doctor schedule records: " . $schedule_row['count'] . "\n";

// Check if there are doctors
$doctors_result = $conn->query("SELECT COUNT(*) as count FROM doctors");
$doctors_row = $doctors_result->fetch_assoc();
echo "Total doctors: " . $doctors_row['count'] . "\n";

// Check appointments
$appt_result = $conn->query("SELECT COUNT(*) as count FROM appointments");
$appt_row = $appt_result->fetch_assoc();
echo "Total appointments: " . $appt_row['count'] . "\n\n";

// Show sample schedule if available
echo "=== Sample Doctor Schedule ===\n";
$sample = $conn->query("
    SELECT d.doctor_id, u.full_name, ds.day_of_week, ds.start_time, ds.end_time 
    FROM doctor_schedule ds
    JOIN doctors d ON ds.doctor_id = d.doctor_id
    JOIN users u ON d.user_id = u.user_id
    LIMIT 5
");

if ($sample->num_rows > 0) {
    while ($row = $sample->fetch_assoc()) {
        echo "Dr. " . $row['full_name'] . " - " . $row['day_of_week'] . ": " . $row['start_time'] . " - " . $row['end_time'] . "\n";
    }
} else {
    echo "No schedule data found. You may need to populate doctor_schedule table.\n";
}

echo "\nDatabase connection successful!\n";
$conn->close();
?>
