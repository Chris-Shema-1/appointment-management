<?php
/**
 * Database Configuration File for Docker
 * Uses environment variables from docker-compose.yml
 */

// Database credentials from environment variables
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'app_user');
define('DB_PASS', getenv('DB_PASS') ?: 'app_password');
define('DB_NAME', getenv('DB_NAME') ?: 'appointment_system');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper character encoding
$conn->set_charset("utf8mb4");

/**
 * Function to safely close database connection
 */
function closeConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

// Optional: Uncomment to see successful connection (for testing only)
// echo "Connected successfully to database: " . DB_NAME;

?>