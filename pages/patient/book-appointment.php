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
                               min="<?php echo date('Y-m-d'); ?>" onchange="loadAvailableSlots()">
                        <small>Select a date for your appointment</small>
                    </div>

                    <div class="form-group">
                        <label for="appointment_time">Preferred Time</label>
                        <select name="appointment_time" id="appointment_time" required onchange="updateTimeDisplay()">
                            <option value="">Choose a time slot...</option>
                        </select>
                        <small id="timeSlotMessage" style="color: #666; margin-top: 5px; display: block;"></small>
                        <small>Each appointment is 30 minutes long. ⏱️</small>
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

                    <div id="doctorInfo" style="display: flex; flex-direction: column; gap: 20px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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
                                <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;">Current Appointments</p>
                                <p style="font-weight: 600; font-size: 16px;" id="doctorAppointments">-</p>
                            </div>
                        </div>

                        <hr style="margin: 10px 0; border: none; border-top: 1px solid #e0e0e0;">

                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 10px;">
                                <i class="fas fa-calendar-check"></i> Working Schedule
                            </p>
                            <div id="scheduleInfo" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                                <p style="color: #999; font-size: 13px;">Loading schedule...</p>
                            </div>
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
            
            // Load doctor's schedule and availability info
            loadDoctorSchedule(doctorId);
            
            // Clear time slots when doctor changes
            const timeSelect = document.getElementById('appointment_time');
            timeSelect.innerHTML = '<option value="">Choose a time slot...</option>';
        }

        /**
         * Load doctor's schedule and availability information
         */
        function loadDoctorSchedule(doctorId) {
            const scheduleDiv = document.getElementById('scheduleInfo');
            scheduleDiv.innerHTML = '<p style="color: #999; font-size: 13px;">Loading schedule...</p>';
            
            fetch(`../../actions/get_doctors.php?action=get_doctor_schedule&doctor_id=${doctorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        scheduleDiv.innerHTML = '<p style="color: #d9534f; font-size: 13px;">❌ ' + data.error + '</p>';
                        return;
                    }
                    
                    if (!data.schedule || data.schedule.length === 0) {
                        scheduleDiv.innerHTML = '<p style="color: #999; font-size: 13px;">No schedule available</p>';
                        return;
                    }
                    
                    // Display schedule
                    let html = '';
                    data.schedule.forEach(slot => {
                        html += `<div style="padding: 8px 12px; background: #f0f8ff; border-radius: 4px; font-size: 13px;">
                                    <strong>${slot.day}</strong><br>
                                    <small>${slot.start_time} - ${slot.end_time}</small>
                                 </div>`;
                    });
                    scheduleDiv.innerHTML = html;
                    
                    // Update appointments count
                    const appoLabel = document.getElementById('doctorAppointments');
                    if (data.confirmed_appointments > 0) {
                        appoLabel.textContent = data.confirmed_appointments + ' confirmed, ' + data.pending_appointments + ' pending';
                        appoLabel.style.color = '#5cb85c';
                    } else {
                        appoLabel.textContent = 'No appointments yet';
                        appoLabel.style.color = '#999';
                    }
                })
                .catch(error => {
                    scheduleDiv.innerHTML = '<p style="color: #d9534f; font-size: 13px;">❌ Failed to load schedule</p>';
                    console.error('Error loading schedule:', error);
                });
        }

        /**
         * Load available time slots for the selected doctor and date
         */
        function loadAvailableSlots() {
            const doctorId = document.getElementById('doctor_id').value;
            const date = document.getElementById('appointment_date').value;
            const timeSelect = document.getElementById('appointment_time');
            const messageDiv = document.getElementById('timeSlotMessage');
            
            // Reset
            timeSelect.innerHTML = '<option value="">Choose a time slot...</option>';
            messageDiv.textContent = '';
            
            // Validate inputs
            if (!doctorId) {
                messageDiv.textContent = '❌ Please select a doctor first';
                messageDiv.style.color = '#d9534f';
                return;
            }
            
            if (!date) {
                messageDiv.textContent = '❌ Please select a date first';
                messageDiv.style.color = '#d9534f';
                return;
            }
            
            // Show loading state
            timeSelect.innerHTML = '<option value="">Loading available slots...</option>';
            timeSelect.disabled = true;
            
            // Fetch available slots via AJAX
            fetch(`../../actions/get_doctors.php?action=get_available_slots&doctor_id=${doctorId}&date=${date}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    timeSelect.disabled = false;
                    
                    if (data.error) {
                        messageDiv.textContent = '❌ ' + data.error;
                        messageDiv.style.color = '#d9534f';
                        timeSelect.innerHTML = '<option value="">Error loading slots</option>';
                        return;
                    }
                    
                    if (!data.slots || data.slots.length === 0) {
                        messageDiv.textContent = '❌ No available slots on this date. Please choose a different date.';
                        messageDiv.style.color = '#d9534f';
                        timeSelect.innerHTML = '<option value="">No slots available</option>';
                        return;
                    }
                    
                    // Populate time slots
                    timeSelect.innerHTML = '<option value="">Choose a time slot...</option>';
                    data.slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot;
                        option.textContent = formatTimeDisplay(slot) + ' - ' + formatTimeDisplay(addMinutes(slot, 30)); // Show end time too
                        timeSelect.appendChild(option);
                    });
                    
                    messageDiv.innerHTML = '✅ <strong>' + data.slots.length + ' available slot(s) found</strong><br><small style="font-size: 12px; color: #666;">Each slot is 30 minutes</small>';
                    messageDiv.style.color = '#5cb85c';
                })
                .catch(error => {
                    timeSelect.disabled = false;
                    messageDiv.textContent = '❌ Failed to load time slots. Please check your internet connection and try again.';
                    messageDiv.style.color = '#d9534f';
                    timeSelect.innerHTML = '<option value="">Error loading slots</option>';
                    console.error('Error:', error);
                });
        }
        
        /**
         * Format time string to 12-hour format with AM/PM
         */
        function formatTimeDisplay(timeStr) {
            const [hours, mins] = timeStr.split(':').map(Number);
            const period = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return displayHours + ':' + String(mins).padStart(2, '0') + ' ' + period;
        }
        
        /**
         * Helper: Add minutes to a time string (HH:MM)
         */
        function addMinutes(timeStr, minutes) {
            const [hours, mins] = timeStr.split(':').map(Number);
            const date = new Date(0, 0, 0, hours, mins + minutes);
            return date.getHours().toString().padStart(2, '0') + ':' + 
                   date.getMinutes().toString().padStart(2, '0');
        }
        
        /**
         * Update display when time is selected
         */
        function updateTimeDisplay() {
            const timeSelect = document.getElementById('appointment_time');
            const messageDiv = document.getElementById('timeSlotMessage');
            
            if (timeSelect.value) {
                const endTime = addMinutes(timeSelect.value, 30);
                messageDiv.innerHTML = '⏱️ <strong>Duration: ' + formatTimeDisplay(timeSelect.value) + ' - ' + formatTimeDisplay(endTime) + '</strong><br><small style="font-size: 12px; color: #666;">30 minutes</small>';
                messageDiv.style.color = '#0275d8';
            }
        }

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const timeInput = document.getElementById('appointment_time');
            const dateInput = document.getElementById('appointment_date');
            
            if (!timeInput.value) {
                e.preventDefault();
                alert('⏱️ Please select a time slot from the available options');
                return;
            }
            
            if (!dateInput.value) {
                e.preventDefault();
                alert('📅 Please select a date');
                return;
            }
        });
    </script>
</body>
</html>
