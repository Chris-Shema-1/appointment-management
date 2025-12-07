<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_timeout = 1800; // 30 minutes

if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    if ($elapsed_time > $session_timeout) {
        session_unset();
        session_destroy();
        header("Location: /appointment-system/index.php?error=session_expired");
        exit();
    }
}

$_SESSION['last_activity'] = time();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /appointment-system/index.php?error=login_required");
        exit();
    }
}

function require_patient() {
    require_login();
    if ($_SESSION['user_type'] !== 'patient') {
        header("Location: /appointment-system/index.php?error=access_denied");
        exit();
    }
}

function require_doctor() {
    require_login();
    if ($_SESSION['user_type'] !== 'doctor') {
        header("Location: /appointment-system/index.php?error=access_denied");
        exit();
    }
}
?>
