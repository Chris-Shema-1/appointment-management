<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/functions.php';

require_patient();

$patient_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

$query = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status, a.created_at,
                 u.full_name as doctor_name, u.phone as doctor_phone,
                 d.specialization, d.consultation_fee
          FROM appointments a
          JOIN doctors d ON a.doctor_id = d.doctor_id
          JOIN users u ON d.user_id = u.user_id
          WHERE a.patient_id = ?";

if ($filter === 'pending') {
    $query .= " AND a.status = 'pending'";
} elseif ($filter === 'confirmed') {
    $query .= " AND a.status = 'confirmed'";
} elseif ($filter === 'completed') {
    $query .= " AND a.status = 'completed'";
} elseif ($filter === 'cancelled') {
    $query .= " AND a.status = 'cancelled'";
} elseif ($filter === 'rejected') {
    $query .= " AND a.status = 'rejected'";
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
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
    <title>My Appointments - DentalCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="dashboard-container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-check"></i> My Appointments</h1>
            <p>View and manage all your dental appointments</p>
        </div>

        <div style="margin-bottom: 20px;">
            <div class="tabs">
                <a href="?filter=all" class="tab <?php echo ($filter === 'all' ? 'active' : ''); ?>">All</a>
                <a href="?filter=pending" class="tab <?php echo ($filter === 'pending' ? 'active' : ''); ?>">Pending</a>
                <a href="?filter=confirmed" class="tab <?php echo ($filter === 'confirmed' ? 'active' : ''); ?>">Confirmed</a>
                <a href="?filter=completed" class="tab <?php echo ($filter === 'completed' ? 'active' : ''); ?>">Completed</a>
                <a href="?filter=cancelled" class="tab <?php echo ($filter === 'cancelled' ? 'active' : ''); ?>">Cancelled</a>
            </div>
        </div>

        <div class="cards-grid">
            <?php if (!empty($appointments)): ?>
                <?php foreach ($appointments as $appt): ?>
                    <div class="card" id="appointment-<?php echo $appt['appointment_id']; ?>">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Dr. <?php echo htmlspecialchars($appt['doctor_name']); ?></h3>
                                <p class="card-subtitle"><?php echo htmlspecialchars($appt['specialization']); ?></p>
                            </div>
                            <?php echo get_status_badge($appt['status']); ?>
                        </div>

                        <div class="card-content">
                            <div style="margin-bottom: 15px;">
                                <p class="card-label"><i class="fas fa-calendar-alt"></i> Date & Time</p>
                                <p class="card-value"><?php echo format_date($appt['appointment_date']) . ' - ' . format_time($appt['appointment_time']); ?></p>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <p class="card-label"><i class="fas fa-stethoscope"></i> Reason</p>
                                <p class="card-value"><?php echo htmlspecialchars($appt['reason']); ?></p>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <p class="card-label"><i class="fas fa-phone"></i> Doctor Phone</p>
                                <p class="card-value"><?php echo htmlspecialchars($appt['doctor_phone']); ?></p>
                            </div>

                            <div>
                                <p class="card-label"><i class="fas fa-money-bill"></i> Consultation Fee</p>
                                <p class="card-value">RWF <?php echo number_format($appt['consultation_fee'], 2); ?></p>
                            </div>
                        </div>

                        <div class="card-footer">
                            <?php if (in_array($appt['status'], ['pending', 'confirmed'])): ?>
                                <form action="../../actions/cancel_appointment.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appt['appointment_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fas fa-inbox" style="font-size: 48px; color: var(--text-light); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--text-dark); margin-bottom: 10px;">No Appointments Found</h3>
                    <p style="color: var(--text-light); margin-bottom: 20px;">You don't have any <?php echo ($filter !== 'all' ? $filter : ''); ?> appointments yet.</p>
                    <a href="book-appointment.php" class="btn btn-primary">Book an Appointment</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
