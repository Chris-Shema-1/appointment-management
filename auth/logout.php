<?php
require_once '../config/session.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header("Location: ../index.php ");
exit();
?>
