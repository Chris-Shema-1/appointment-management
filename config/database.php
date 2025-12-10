<?php
/**
 * Database configuration — read from environment variables for Docker compatibility.
 * Use DB_HOST set to the database service name in docker-compose (for example: 'mysql').
 */

$env_db_host = getenv('DB_HOST');
// also check $_SERVER/$_ENV in case Apache/PHP-FPM passes through env differently
if ($env_db_host === false || $env_db_host === null || $env_db_host === '') {
    if (isset($_SERVER['DB_HOST']) && $_SERVER['DB_HOST'] !== '') {
        $env_db_host = $_SERVER['DB_HOST'];
    } elseif (isset($_ENV['DB_HOST']) && $_ENV['DB_HOST'] !== '') {
        $env_db_host = $_ENV['DB_HOST'];
    }
}

// Read environment variables (fallbacks for local XAMPP)
$db_host = $env_db_host !== false && $env_db_host !== null && $env_db_host !== '' ? $env_db_host : '127.0.0.1';
$db_user = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db_name = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'appointment_system';
$db_port = getenv('DB_PORT') !== false && is_numeric(getenv('DB_PORT')) ? intval(getenv('DB_PORT')) : (isset($_SERVER['DB_PORT']) && is_numeric($_SERVER['DB_PORT']) ? intval($_SERVER['DB_PORT']) : 3306);

// Normalize host: avoid 'localhost' which causes mysqli to use Unix socket on some platforms.
// Force TCP by using 127.0.0.1 when localhost is provided.
if (in_array(strtolower($db_host), ['localhost', '::1', '0.0.0.0'], true)) {
    $db_host = '127.0.0.1';
}

// Try to connect (catch exceptions if mysqli is configured to throw them)
// Attempt connection. If it fails with a socket error, try to provide helpful debugging info
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
} catch (mysqli_sql_exception $e) {
    // mysqli may throw; include host/port in message to aid debugging
    $msg = $e->getMessage();
    die("Connection failed when connecting to {$db_host}:{$db_port} — " . $msg);
}

if ($conn->connect_errno) {
    // Common socket error (2002) can appear as "No such file or directory" when PHP tries a socket.
    $errno = $conn->connect_errno;
    $err = $conn->connect_error;
    if (strpos($err, 'No such file or directory') !== false || $errno === 2002) {
        // Try fallback: if host was a hostname, attempt to resolve it to an IP and reconnect.
        $resolved = @gethostbyname($db_host);
        if ($resolved && $resolved !== $db_host) {
            try {
                $conn = new mysqli($resolved, $db_user, $db_pass, $db_name, $db_port);
            } catch (mysqli_sql_exception $e) {
                die("Connection failed to {$db_host} ({$resolved}): {$e->getMessage()}");
            }
            if ($conn->connect_errno) {
                die("Connection failed to {$db_host} ({$resolved}): ({$conn->connect_errno}) {$conn->connect_error}");
            }
        }

        // Still failing — give explicit guidance
        die("Connection failed to {$db_host}:{$db_port} — {$err}. If you are running in Docker, set DB_HOST to your database service name (for example 'mysql') or an IP address (127.0.0.1) in your web service environment.");
    }

    die("Connection failed: ({$errno}) {$err}");
}

$conn->set_charset('utf8mb4');

function closeConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

?>
