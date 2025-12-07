<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Clinic - Appointment Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-wrapper">
            <!-- Logo Section -->
            <div class="logo-section">
                <i class="fas fa-tooth"></i>
                <h1>DentalCare</h1>
                <p>Appointment Management System</p>
            </div>

            <!-- Login Form -->
            <form action="auth/login.php" method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    <span class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <span class="error-message"></span>
                </div>

                <div class="form-group checkbox">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <!-- Additional Links -->
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Create one</a></p>
                <p><a href="#forgot-password">Forgot your password?</a></p>
            </div>
        </div>

        <!-- Illustration Side (Desktop) -->
        <div class="login-illustration">
            <div class="illustration-content">
                <i class="fas fa-calendar-check"></i>
                <h2>Easy Appointment Booking</h2>
                <p>Book your dental appointments in seconds. View availability and get instant confirmations.</p>
                <ul class="features-list">
                    <li><i class="fas fa-check-circle"></i> Quick & Easy Booking</li>
                    <li><i class="fas fa-check-circle"></i> Expert Doctors</li>
                    <li><i class="fas fa-check-circle"></i> Real-time Notifications</li>
                    <li><i class="fas fa-check-circle"></i> Secure & Private</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
