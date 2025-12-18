<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "=== Testing Available Slots Function ===\n\n";

// Get a doctor
$doc_result = $conn->query("SELECT doctor_id FROM doctors LIMIT 1");
if ($doc_result->num_rows > 0) {
    $doc_row = $doc_result->fetch_assoc();
    $doctor_id = $doc_row['doctor_id'];
    
    // Test with different dates
    $test_dates = [
        date('Y-m-d', strtotime('next Monday')),
        date('Y-m-d', strtotime('next Tuesday')),
        date('Y-m-d', strtotime('next Saturday')),
    ];
    
    foreach ($test_dates as $date) {
        $slots = get_available_slots($conn, $doctor_id, $date);
        $day_name = date('l', strtotime($date));
        echo "Date: " . $date . " (" . $day_name . ")\n";
        echo "Available slots: " . count($slots) . "\n";
        if (count($slots) > 0) {
            echo "Slots: " . implode(", ", array_slice($slots, 0, 5));
            if (count($slots) > 5) {
                echo "... and " . (count($slots) - 5) . " more";
            }
            echo "\n";
        }
        echo "\n";
    }
} else {
    echo "No doctors found in database!\n";
}

$conn->close();
?>
