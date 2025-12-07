<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/functions.php';

require_patient();

$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

$query = "SELECT notification_id, message, is_read, created_at
         FROM notifications
         WHERE user_id = ?";

if ($filter === 'unread') {
    $query .= " AND is_read = 0";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

$unread_count = get_unread_notification_count($conn, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - DentalCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="dashboard-container">
        <div class="page-header">
            <h1><i class="fas fa-bell"></i> Notifications</h1>
            <p>Stay updated with your appointments and system messages</p>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <?php if ($unread_count > 0): ?>
                <form action="../../actions/mark_all_notifications_read.php" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 20px;">
            <div class="tabs">
                <a href="?filter=all" class="tab <?php echo ($filter === 'all' ? 'active' : ''); ?>">All</a>
                <a href="?filter=unread" class="tab <?php echo ($filter === 'unread' ? 'active' : ''); ?>">Unread (<?php echo $unread_count; ?>)</a>
            </div>
        </div>

        <div id="all-notifications" class="tab-content active">
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item <?php echo ($notif['is_read'] ? 'read' : 'unread'); ?>" data-notification-id="<?php echo $notif['notification_id']; ?>">
                            <div style="display: flex; gap: 15px;">
                                <div class="notification-icon" style="background-color: rgba(14, 165, 233, 0.2); color: var(--primary-blue);">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h3 class="notification-title">Appointment Update</h3>
                                    <p class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <p class="notification-time">
                                        <i class="fas fa-clock"></i> <?php echo time_ago($notif['created_at']); ?>
                                    </p>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-end;">
                                    <?php if (!$notif['is_read']): ?>
                                        <span class="badge" style="background-color: var(--primary-blue); color: white; font-size: 10px; padding: 4px 8px; border-radius: 4px;">New</span>
                                    <?php endif; ?>
                                    <?php if (!$notif['is_read']): ?>
                                        <form action="../../actions/mark_notification_read.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="notification_id" value="<?php echo $notif['notification_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-icon" title="Mark as read">
                                                <i class="fas fa-envelope-open"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <i class="fas fa-bell-slash" style="font-size: 48px; color: var(--text-light); margin-bottom: 20px;"></i>
                        <h3 style="color: var(--text-dark); margin-bottom: 10px;">No Notifications</h3>
                        <p style="color: var(--text-light);">You're all caught up!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
    <style>
        .notification-item {
            background-color: var(--bg-white);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--border-gray);
        }

        .notification-item.unread {
            background-color: rgba(14, 165, 233, 0.05);
            border-left-color: var(--primary-blue);
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .notification-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0 0 8px 0;
        }

        .notification-message {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.5;
            margin: 0 0 10px 0;
        }

        .notification-time {
            color: var(--text-light);
            font-size: 12px;
            margin: 0;
        }
    </style>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
