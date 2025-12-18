<?php
require_once 'config/database.php';

echo "=== Doctor Schedule Data ===\n\n";

$r = $conn->query("SELECT * FROM doctor_schedule");
while ($row = $r->fetch_assoc()) {
    echo "Doctor: " . $row['doctor_id'] . ", Day: " . $row['day_of_week'] . ", Available: " . $row['is_available'] . ", Times: " . $row['start_time'] . " - " . $row['end_time'] . "\n";
}

$conn->close();
?>
