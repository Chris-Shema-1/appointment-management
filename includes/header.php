<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/functions.php';

// Fetch unread notifications count

$patient_id = $_SESSION['user_id'];
$unread_count = get_unread_notification_count($conn, $patient_id);
 ?>

<header class="navbar">
    <div class="navbar-container">
        <!-- Logo -->
        <div class="navbar-logo">
            <a href="<?php echo $_SESSION['user_type'] === 'doctor' ? '../../pages/doctor/dashboard.php' : '../../pages/patient/dashboard.php'; ?>">
                <i class="fas fa-tooth"></i>
                <span>DentalCare</span>
            </a>
        </div>

        <!-- Hamburger Menu (Mobile) -->
        <button class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Navigation Menu -->
        <nav class="navbar-menu" id="navbarMenu">
            <?php if($_SESSION['user_type'] === 'patient'): ?>
                <a href="../../pages/patient/dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="../../pages/patient/book-appointment.php" class="nav-link">
                    <i class="fas fa-calendar-plus"></i> Book Appointment
                </a>
                <a href="../../pages/patient/my-appointments.php" class="nav-link">
                    <i class="fas fa-list"></i> My Appointments
                </a>
                <a href="../../pages/patient/notifications.php" class="nav-link">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if($unread_count > 0): ?>
                        <span class="badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="../../pages/doctor/dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="../../pages/doctor/appointments.php" class="nav-link">
                    <i class="fas fa-calendar-check"></i> Appointments
                </a>
                <a href="../../pages/doctor/schedule.php" class="nav-link">
                    <i class="fas fa-clock"></i> Schedule
                </a>
                <a href="../../pages/doctor/notifications.php" class="nav-link">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if($unread_count > 0): ?>
                        <span class="badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </nav>

        <!-- User Profile Dropdown -->
        <div class="user-profile-menu">
            <button class="profile-toggle" id="profileToggle">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $_SESSION['full_name'] ?? 'User'; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-info">
                    <p class="profile-name"><?php echo $_SESSION['full_name'] ?? 'User'; ?></p>
                    <p class="profile-email"><?php echo $_SESSION['email'] ?? ''; ?></p>
                </div>
                <hr>
                <a href="../../auth/logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>
