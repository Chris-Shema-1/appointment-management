<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DentalCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="register-container">
        <div class="register-wrapper">
            <!-- Logo Section -->
            <div class="logo-section small">
                <i class="fas fa-tooth"></i>
                <h1>DentalCare</h1>
                <p>Create Your Account</p>
            </div>

            <!-- User Type Selection -->
            <div class="user-type-selection">
                <label class="user-type-option">
                    <input type="radio" name="user_type" value="patient" checked>
                    <span><i class="fas fa-user"></i> Patient</span>
                </label>
                <label class="user-type-option">
                    <input type="radio" name="user_type" value="doctor">
                    <span><i class="fas fa-stethoscope"></i> Doctor</span>
                </label>
            </div>

            <!-- Registration Form -->
            <form action="auth/register.php" method="POST" class="register-form" id="registerForm">
                <input type="hidden" name="user_type" id="formUserType" value="patient">

                <!-- Common Fields -->
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                    <small>Password must be at least 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>

                <!-- Doctor-Specific Fields (Hidden by default) -->
                <div id="doctorFields" class="doctor-fields" style="display: none;">
                    <hr class="form-divider">
                    <p class="form-section-title">Professional Information</p>

                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <select id="specialization" name="specialization">
                            <option value="">Select specialization</option>
                            <option value="General Dentistry">General Dentistry</option>
                            <option value="Orthodontics">Orthodontics</option>
                            <option value="Periodontics">Periodontics</option>
                            <option value="Endodontics">Endodontics</option>
                            <option value="Prosthodontics">Prosthodontics</option>
                            <option value="Oral Surgery">Oral Surgery</option>
                            <option value="Pediatric Dentistry">Pediatric Dentistry</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="qualification">Qualification</label>
                        <input type="text" id="qualification" name="qualification" placeholder="e.g., BDS, MDS">
                    </div>

                    <div class="form-group">
                        <label for="experience_years">Years of Experience</label>
                        <input type="number" id="experience_years" name="experience_years" placeholder="Enter years of experience" min="0" max="70">
                    </div>

                    <div class="form-group">
                        <label for="consultation_fee">Consultation Fee ($)</label>
                        <input type="number" id="consultation_fee" name="consultation_fee" placeholder="Enter consultation fee" min="0" step="0.01">
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="form-group checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#terms">Terms & Conditions</a> and <a href="#privacy">Privacy Policy</a></label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <!-- Login Link -->
            <div class="auth-links">
                <p>Already have an account? <a href="index.php">Sign in</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
