<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/functions.php';

require_doctor();

$doctor_id = $_SESSION['doctor_id'];
$filter = $_GET['filter'] ?? 'pending';

$query = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status, a.created_at,
                 u.full_name as patient_name, u.phone as patient_phone, u.user_id
          FROM appointments a
          JOIN users u ON a.patient_id = u.user_id
          WHERE a.doctor_id = ?";

if ($filter === 'pending') {
    $query .= " AND a.status = 'pending'";
} elseif ($filter === 'confirmed') {
    $query .= " AND a.status = 'confirmed'";
} elseif ($filter === 'completed') {
    $query .= " AND a.status = 'completed'";
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - DentalCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="dashboard-container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-check"></i> Manage Appointments</h1>
            <p>View and manage all patient appointments</p>
        </div>

        <div style="margin-bottom: 20px;">
            <div class="tabs">
                <a href="?filter=pending" class="tab <?php echo ($filter === 'pending' ? 'active' : ''); ?>">Pending Requests</a>
                <a href="?filter=confirmed" class="tab <?php echo ($filter === 'confirmed' ? 'active' : ''); ?>">Confirmed</a>
                <a href="?filter=completed" class="tab <?php echo ($filter === 'completed' ? 'active' : ''); ?>">Completed</a>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th><?php echo ($filter === 'pending' ? 'Requested' : ''); ?> Date & Time</th>
                        <th>Reason</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php foreach ($appointments as $appt): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($appt['patient_name']); ?></strong><br>
                                    <small style="color: var(--text-light);">Patient ID: P-<?php echo str_pad($appt['user_id'], 5, '0', STR_PAD_LEFT); ?></small>
                                </td>
                                <td><?php echo format_date($appt['appointment_date']) . '<br>' . format_time($appt['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($appt['reason']); ?></td>
                                <td><?php echo htmlspecialchars($appt['patient_phone']); ?></td>
                                <td><?php echo get_status_badge($appt['status']); ?></td>
                                <td>
                                    <?php if ($filter === 'pending'): ?>
                                        <form action="../../actions/approve_appointment.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appt['appointment_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve?')">Approve</button>
                                        </form>
                                        <form action="../../actions/reject_appointment.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appt['appointment_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject?')">Reject</button>
                                        </form>
                                    <?php elseif ($filter === 'confirmed'): ?>
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
                            <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-light);">No <?php echo $filter; ?> appointments found</td>
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
