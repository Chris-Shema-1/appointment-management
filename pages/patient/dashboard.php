<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/functions.php';

require_patient();

$patient_id = $_SESSION['user_id'];

$total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE patient_id = ?");
$total_stmt->bind_param("i", $patient_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_appointments = $total_result->fetch_assoc()['total'];
$total_stmt->close();

$pending_stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE patient_id = ? AND status = 'pending'");
$pending_stmt->bind_param("i", $patient_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_count = $pending_result->fetch_assoc()['total'];
$pending_stmt->close();

$confirmed_stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE patient_id = ? AND status = 'confirmed' AND appointment_date >= CURDATE()");
$confirmed_stmt->bind_param("i", $patient_id);
$confirmed_stmt->execute();
$confirmed_result = $confirmed_stmt->get_result();
$upcoming_count = $confirmed_result->fetch_assoc()['total'];
$confirmed_stmt->close();

$unread_notifications = get_unread_notification_count($conn, $patient_id);

// Get recent appointments (last 5)
$recent_stmt = $conn->prepare("SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status,
                                       u.full_name as doctor_name, d.specialization
                                FROM appointments a
                                JOIN doctors d ON a.doctor_id = d.doctor_id
                                JOIN users u ON d.user_id = u.user_id
                                WHERE a.patient_id = ?
                                ORDER BY a.created_at DESC LIMIT 5");
$recent_stmt->bind_param("i", $patient_id);
$recent_stmt->execute();
$recent_result = $recent_stmt->get_result();
$recent_appointments = [];
while ($row = $recent_result->fetch_assoc()) {
    $recent_appointments[] = $row;
}
$recent_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - DentalCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="dashboard-container">
        <div class="page-header">
            <h1><i class="fas fa-user-circle"></i> Welcome Back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <p>Manage your dental appointments and stay updated</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-calendar-alt stat-card-icon blue"></i>
                <div class="stat-card-value"><?php echo $total_appointments; ?></div>
                <div class="stat-card-label">Total Appointments</div>
            </div>

            <div class="stat-card warning">
                <i class="fas fa-hourglass-half stat-card-icon orange"></i>
                <div class="stat-card-value"><?php echo $pending_count; ?></div>
                <div class="stat-card-label">Pending Confirmation</div>
            </div>

            <div class="stat-card success">
                <i class="fas fa-check-circle stat-card-icon green"></i>
                <div class="stat-card-value"><?php echo $upcoming_count; ?></div>
                <div class="stat-card-label">Confirmed Appointments</div>
            </div>

            <div class="stat-card danger">
                <i class="fas fa-bell stat-card-icon blue"></i>
                <div class="stat-card-value"><?php echo $unread_notifications; ?></div>
                <div class="stat-card-label">Unread Notifications</div>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
            <a href="book-appointment.php" class="btn btn-primary">
                <i class="fas fa-calendar-plus"></i> Book New Appointment
            </a>
            <a href="my-appointments.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> View All Appointments
            </a>
        </div>

        <div class="table-wrapper">
            <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-calendar-check"></i> Recent Appointments (Last 5)
            </h2>

            <table class="table">
                <thead>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Specialization</th>
                        <th>Date & Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_appointments)): ?>
                        <?php foreach ($recent_appointments as $appt): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($appt['specialization']); ?></td>
                                <td><?php echo format_date($appt['appointment_date']) . ' - ' . format_time($appt['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($appt['reason']); ?></td>
                                <td><?php echo get_status_badge($appt['status']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="window.location.href='my-appointments.php#appointment-<?php echo $appt['appointment_id']; ?>'">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">No appointments found. <a href="book-appointment.php">Book one now!</a></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
