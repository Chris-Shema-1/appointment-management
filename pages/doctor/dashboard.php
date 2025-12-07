<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/functions.php';

require_doctor();

$doctor_id = $_SESSION['doctor_id'];

// Get today's appointments count
$today_stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE()");
$today_stmt->bind_param("i", $doctor_id);
$today_stmt->execute();
$today_count = $today_stmt->get_result()->fetch_assoc()['total'];
$today_stmt->close();

// Get pending requests count
$pending_stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ? AND status = 'pending'");
$pending_stmt->bind_param("i", $doctor_id);
$pending_stmt->execute();
$pending_count = $pending_stmt->get_result()->fetch_assoc()['total'];
$pending_stmt->close();

// Get unique patients count
$patients_stmt = $conn->prepare("SELECT COUNT(DISTINCT patient_id) as total FROM appointments WHERE doctor_id = ?");
$patients_stmt->bind_param("i", $doctor_id);
$patients_stmt->execute();
$patients_count = $patients_stmt->get_result()->fetch_assoc()['total'];
$patients_stmt->close();

// Get this week's appointments count
$week_stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ? AND YEARWEEK(appointment_date) = YEARWEEK(CURDATE())");
$week_stmt->bind_param("i", $doctor_id);
$week_stmt->execute();
$week_count = $week_stmt->get_result()->fetch_assoc()['total'];
$week_stmt->close();

// Get pending appointment requests (detailed)
$pending_detail_stmt = $conn->prepare("SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.created_at,
                                              u.full_name as patient_name, u.phone as patient_phone, u.user_id
                                       FROM appointments a
                                       JOIN users u ON a.patient_id = u.user_id
                                       WHERE a.doctor_id = ? AND a.status = 'pending'
                                       ORDER BY a.created_at DESC LIMIT 3");
$pending_detail_stmt->bind_param("i", $doctor_id);
$pending_detail_stmt->execute();
$pending_requests = [];
$pending_result = $pending_detail_stmt->get_result();
while ($row = $pending_result->fetch_assoc()) {
    $pending_requests[] = $row;
}
$pending_detail_stmt->close();

// Get today's schedule
$today_schedule_stmt = $conn->prepare("SELECT a.appointment_id, a.appointment_time, a.reason, a.status,
                                              u.full_name as patient_name
                                       FROM appointments a
                                       JOIN users u ON a.patient_id = u.user_id
                                       WHERE a.doctor_id = ? AND a.appointment_date = CURDATE() AND a.status IN ('confirmed', 'completed')
                                       ORDER BY a.appointment_time ASC");
$today_schedule_stmt->bind_param("i", $doctor_id);
$today_schedule_stmt->execute();
$today_schedule = [];
$today_result = $today_schedule_stmt->get_result();
while ($row = $today_result->fetch_assoc()) {
    $today_schedule[] = $row;
}
$today_schedule_stmt->close();

$unread_notifications = get_unread_notification_count($conn, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - DentalCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="dashboard-container">
        <div class="page-header">
            <h1><i class="fas fa-user-md"></i> Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <p>Manage your appointments and schedule efficiently</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-calendar-check stat-card-icon blue"></i>
                <div class="stat-card-value"><?php echo $today_count; ?></div>
                <div class="stat-card-label">Today's Appointments</div>
            </div>

            <div class="stat-card warning">
                <i class="fas fa-hourglass-half stat-card-icon orange"></i>
                <div class="stat-card-value"><?php echo $pending_count; ?></div>
                <div class="stat-card-label">Pending Requests</div>
            </div>

            <div class="stat-card success">
                <i class="fas fa-users stat-card-icon green"></i>
                <div class="stat-card-value"><?php echo $patients_count; ?></div>
                <div class="stat-card-label">Total Patients</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-calendar-alt stat-card-icon blue"></i>
                <div class="stat-card-value"><?php echo $week_count; ?></div>
                <div class="stat-card-label">This Week's Appointments</div>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
            <a href="appointments.php" class="btn btn-primary">
                <i class="fas fa-calendar-check"></i> View All Appointments
            </a>
            <a href="schedule.php" class="btn btn-secondary">
                <i class="fas fa-clock"></i> Manage Schedule
            </a>
        </div>

        <div class="table-wrapper">
            <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-hourglass-half"></i> Pending Appointment Requests
            </h2>

            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php if (!empty($pending_requests)): ?>
                    <?php foreach ($pending_requests as $request): ?>
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title"><?php echo htmlspecialchars($request['patient_name']); ?></h3>
                                    <p class="card-subtitle">Patient ID: P-<?php echo str_pad($request['user_id'], 5, '0', STR_PAD_LEFT); ?></p>
                                </div>
                                <span class="status-badge status-pending">Pending</span>
                            </div>

                            <div class="card-content">
                                <div style="margin-bottom: 15px;">
                                    <p class="card-label"><i class="fas fa-calendar-alt"></i> Requested Date & Time</p>
                                    <p class="card-value"><?php echo format_date($request['appointment_date']) . ' - ' . format_time($request['appointment_time']); ?></p>
                                </div>

                                <div style="margin-bottom: 15px;">
                                    <p class="card-label"><i class="fas fa-stethoscope"></i> Reason for Visit</p>
                                    <p class="card-value"><?php echo htmlspecialchars($request['reason']); ?></p>
                                </div>

                                <div>
                                    <p class="card-label"><i class="fas fa-phone"></i> Patient Contact</p>
                                    <p class="card-value"><?php echo htmlspecialchars($request['patient_phone']); ?></p>
                                </div>
                            </div>

                            <div class="card-footer">
                                <form action="../../actions/approve_appointment.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $request['appointment_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this appointment?')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form action="../../actions/reject_appointment.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $request['appointment_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this appointment?')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px; color: var(--text-light);">
                        <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px;"></i>
                        <p>No pending appointment requests</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-wrapper" style="margin-top: 30px;">
            <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-calendar-day"></i> Today's Schedule
            </h2>

            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient Name</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($today_schedule)): ?>
                        <?php foreach ($today_schedule as $appt): ?>
                            <tr>
                                <td><?php echo format_time($appt['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($appt['reason']); ?></td>
                                <td><?php echo get_status_badge($appt['status']); ?></td>
                                <td>
                                    <?php if ($appt['status'] === 'confirmed'): ?>
                                        <form action="../../actions/complete_appointment.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appt['appointment_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-secondary">Mark Complete</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>Completed</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-light);">No appointments scheduled for today</td>
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
