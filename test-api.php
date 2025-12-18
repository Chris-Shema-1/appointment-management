<?php
require_once 'config/database.php';

// Simulate the AJAX requests to test get_doctors.php
$_GET['action'] = 'get_available_slots';
$_GET['doctor_id'] = 1;
$_GET['date'] = date('Y-m-d', strtotime('next Monday'));

require_once 'actions/get_doctors.php';
?>
