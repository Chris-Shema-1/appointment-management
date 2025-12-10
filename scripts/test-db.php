<?php
// Simple DB connection tester. Run inside the web container: php scripts/test-db.php
require_once __DIR__ . '/../config/database.php';

if (isset($conn) && $conn instanceof mysqli) {
    echo "Connected to DB ({$conn->host_info})\n";
    closeConnection();
    exit(0);
}

echo "No mysqli connection available\n";
exit(1);
?>