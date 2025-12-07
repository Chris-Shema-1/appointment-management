<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/functions.php';

require_patient();

$doctors_stmt = $conn->prepare("SELECT d.doctor_id, d.specialization, d.qualification, 
                                       d.experience_years, d.consultation_fee,
                                       u.full_name, u.email, u.phone
                                FROM doctors d
                                JOIN users u ON d.user_id = u.user_id
                                WHERE d.specialization != 'Not Set'
                                ORDER BY u.full_name ASC");
$doctors_stmt->execute();
$doctors_result = $doctors_stmt->get_result();
$doctors = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}
$doctors_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - DentalCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="dashboard-container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-plus"></i> Book an Appointment</h1>
            <p>Schedule your next dental visit</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; max-width: 1000px;">
            <div class="table-wrapper" style="grid-column: 1 / -1; padding: 30px;">
                <form action="../../actions/book_appointment.php" method="POST" id="bookingForm" class="booking-form">
                    <input type="hidden" name="patient_id" value="<?php echo $_SESSION['user_id']; ?>">

                    <div class="form-group">
                        <label for="doctor_id">Select Doctor</label>
                        <select name="doctor_id" id="doctor_id" required onchange="loadDoctorDetails(this.value)">
                            <option value="">Choose a doctor...</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['doctor_id']; ?>" 
                                        data-specialization="<?php echo htmlspecialchars($doctor['specialization']); ?>"
                                        data-experience="<?php echo $doctor['experience_years']; ?>"
                                        data-fee="<?php echo $doctor['consultation_fee']; ?>">
                                    Dr. <?php echo htmlspecialchars($doctor['full_name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="appointment_date">Appointment Date</label>
                        <input type="date" name="appointment_date" id="appointment_date" required 
                               min="<?php echo date('Y-m-d'); ?>" onchange="validateDateNotPast(this)">
                        <small>Select a date for your appointment</small>
                    </div>

                    <div class="form-group">
                        <label for="appointment_time">Preferred Time</label>
                        <input type="time" name="appointment_time" id="appointment_time" required>
                        <small>Choose your preferred time slot</small>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason for Visit</label>
                        <textarea name="reason" id="reason" placeholder="Describe the reason for your appointment..." rows="4" required minlength="10"></textarea>
                        <small>Help us understand your needs (minimum 10 characters)</small>
                    </div>

                    <div class="form-group">
                        <label for="notes">Additional Notes (Optional)</label>
                        <textarea name="notes" id="notes" placeholder="Any additional information..." rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-check-circle"></i> Book Appointment
                    </button>
                </form>
            </div>

            <div id="doctorDetailsContainer" style="display: none; grid-column: 1 / -1;">
                <div class="table-wrapper">
                    <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-user-md"></i> Doctor Information
                    </h2>

                    <div id="doctorInfo" style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;">Specialization</p>
                            <p style="font-weight: 600; font-size: 16px;" id="doctorSpecialization">-</p>
                        </div>

                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;">Years of Experience</p>
                            <p style="font-weight: 600; font-size: 16px;" id="doctorExperience">-</p>
                        </div>

                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;">Consultation Fee</p>
                            <p style="font-weight: 600; font-size: 16px; color: var(--primary-blue);" id="doctorFee">-</p>
                        </div>

                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;">Available Days</p>
                            <p style="font-weight: 600; font-size: 14px;">Monday - Friday, 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
    <script src="../../assets/js/script.js"></script>
    <script>
        function loadDoctorDetails(doctorId) {
            const container = document.getElementById('doctorDetailsContainer');
            const selectElement = document.getElementById('doctor_id');

            if (!doctorId || doctorId === '') {
                container.style.display = 'none';
                return;
            }

            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const specialization = selectedOption.dataset.specialization;
            const experience = selectedOption.dataset.experience;
            const fee = selectedOption.dataset.fee;

            document.getElementById('doctorSpecialization').textContent = specialization;
            document.getElementById('doctorExperience').textContent = experience + ' years';
            document.getElementById('doctorFee').textContent = 'RWF ' + fee;

            container.style.display = 'block';
        }

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const dateInput = document.getElementById('appointment_date');
            if (isDateInPast(dateInput.value)) {
                e.preventDefault();
                alert('Cannot book appointments in the past');
            }
        });
    </script>
</body>
</html>
