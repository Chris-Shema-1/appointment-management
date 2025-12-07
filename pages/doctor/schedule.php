<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/functions.php';

require_doctor();

$doctor_id = $_SESSION['doctor_id'];

// Get doctor's current schedule
$schedule_stmt = $conn->prepare("SELECT schedule_id, day_of_week, start_time, end_time, is_available
                                FROM doctor_schedule
                                WHERE doctor_id = ?
                                ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
$schedule_stmt->bind_param("i", $doctor_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();
$schedule = [];
while ($row = $schedule_result->fetch_assoc()) {
    $schedule[$row['day_of_week']] = $row;
}
$schedule_stmt->close();

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedule - DentalCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="dashboard-container">
        <div class="page-header">
            <h1><i class="fas fa-clock"></i> Manage Your Schedule</h1>
            <p>Set your working hours and availability</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; max-width: 1200px;">
            <div class="table-wrapper" style="padding: 30px;">
                <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-calendar-plus"></i> Add/Edit Schedule
                </h2>

                <form action="../../actions/update_schedule.php" method="POST" id="scheduleForm" class="schedule-form">
                    <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">

                    <div class="form-group">
                        <label for="day_of_week">Select Day</label>
                        <select name="day_of_week" id="day_of_week" required>
                            <option value="">Choose a day...</option>
                            <?php foreach ($days as $day): ?>
                                <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time" required>
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" id="end_time" required>
                    </div>

                    <div class="form-group checkbox">
                        <input type="checkbox" id="is_available" name="is_available" value="1" checked>
                        <label for="is_available">Available for appointments on this day</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Save Schedule
                    </button>
                </form>

                <hr style="margin: 30px 0;">

                <h3 style="margin-bottom: 15px; font-size: 16px;">Quick Templates</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="button" class="btn btn-secondary btn-block" onclick="applyWeekdayTemplate()">
                        <i class="fas fa-clock"></i> Standard Weekday (9 AM - 6 PM)
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="applyMorningTemplate()">
                        <i class="fas fa-sunrise"></i> Morning Shift (8 AM - 1 PM)
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="applyAfternoonTemplate()">
                        <i class="fas fa-sun"></i> Afternoon Shift (1 PM - 6 PM)
                    </button>
                </div>
            </div>

            <div class="table-wrapper" style="padding: 30px;">
                <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-calendar-week"></i> Your Weekly Schedule
                </h2>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($days as $day): ?>
                        <?php 
                        $day_schedule = $schedule[$day] ?? null;
                        $border_color = ($day_schedule && $day_schedule['is_available']) ? 'var(--primary-blue)' : 'var(--danger-red)';
                        $badge_color = ($day_schedule && $day_schedule['is_available']) ? 'var(--secondary-green)' : 'var(--danger-red)';
                        $badge_text = ($day_schedule && $day_schedule['is_available']) ? 'Available' : 'Unavailable';
                        $time_text = $day_schedule ? format_time($day_schedule['start_time']) . ' - ' . format_time($day_schedule['end_time']) : 'Not set';
                        ?>
                        <div style="padding: 15px; background-color: var(--bg-light); border-radius: var(--radius-md); border-left: 4px solid <?php echo $border_color; ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <h4 style="margin: 0; font-weight: 600;"><?php echo $day; ?></h4>
                                <span class="badge" style="background-color: <?php echo $badge_color; ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?php echo $badge_text; ?></span>
                            </div>
                            <p style="margin: 0; color: var(--text-light); font-size: 14px;"><?php echo $time_text; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
    <script src="../../assets/js/script.js"></script>
    <script>
        function applyWeekdayTemplate() {
            document.getElementById('start_time').value = '09:00';
            document.getElementById('end_time').value = '18:00';
            document.getElementById('is_available').checked = true;
        }

        function applyMorningTemplate() {
            document.getElementById('start_time').value = '08:00';
            document.getElementById('end_time').value = '13:00';
            document.getElementById('is_available').checked = true;
        }

        function applyAfternoonTemplate() {
            document.getElementById('start_time').value = '13:00';
            document.getElementById('end_time').value = '18:00';
            document.getElementById('is_available').checked = true;
        }

        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;

            if (startTime >= endTime) {
                e.preventDefault();
                alert('End time must be after start time');
            }
        });
    </script>
</body>
</html>
