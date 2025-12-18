# 🦷 DentalCare Appointment Management System

## 📋 Project Overview

**DentalCare** is a professional web-based appointment management system designed specifically for dental clinics to digitize and streamline the appointment booking process. The system enables seamless communication and scheduling between patients and dental practitioners, eliminating manual paper-based scheduling and reducing administrative overhead.

### Problem Statement
Dental clinics traditionally manage appointments using paper agendas and manual scheduling, leading to:
- Scheduling conflicts and double bookings
- Missed appointments due to poor communication
- Inefficient patient tracking
- High administrative workload
- Limited accessibility for patients

**DentalCare** solves these challenges by providing a centralized, digital appointment management platform accessible 24/7 from any device.

---

## 🛠️ Technology Stack

| Category | Technology |
|----------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) |
| **Backend** | PHP 8.1+ |
| **Database** | MySQL 8.0 |
| **Server** | Apache 2.4 / Docker |
| **Version Control** | Git & GitHub |
| **Containerization** | Docker & Docker Compose |

---

## ✨ Features

### 👥 Patient Features
- ✅ User registration and authentication
- ✅ Browse available doctors and specializations
- ✅ Request appointments with detailed reason/symptoms
- ✅ View complete appointment history
- ✅ Cancel pending or confirmed appointments
- ✅ Real-time in-app notifications
- ✅ View doctor profiles (specialization, experience, consultation fees)
- ✅ Responsive dashboard

### 👨‍⚕️ Doctor Features
- ✅ User registration and professional profile setup
- ✅ View incoming appointment requests
- ✅ Approve or reject appointment requests
- ✅ Manage working hours and availability schedule
- ✅ View daily/weekly calendar with appointments
- ✅ Mark appointments as completed
- ✅ Access patient details and medical history notes
- ✅ Receive appointment notifications

### 🔐 Security Features
- ✅ Password hashing using PHP's `password_hash()`
- ✅ SQL injection prevention via prepared statements
- ✅ XSS protection with `htmlspecialchars()`
- ✅ Session-based authentication
- ✅ Role-based access control (Patient/Doctor)

---

## 📁 Project Structure

```
appointment-management/
│
├── 📄 index.php                    # Application entry point
├── 📄 README.md                    # This file
├── 📄 docker-compose.yml           # Docker containerization config
├── 📄 Dockerfile                   # PHP/Apache Docker image definition
│
├── 📁 auth/
│   ├── login.php                   # User login handler
│   ├── logout.php                  # Session termination
│   └── register.php                # User registration handler
│
├── 📁 config/
│   ├── database.php                # Database connection (env-based)
│   └── session.php                 # Session management & auth checks
│
├── 📁 includes/
│   ├── header.php                  # Navbar and user menu
│   ├── footer.php                  # Page footer
│   └── functions.php               # Utility functions (sanitize, format, etc.)
│
├── 📁 pages/
│   ├── 📁 patient/
│   │   ├── dashboard.php           # Patient home/dashboard
│   │   ├── book-appointment.php    # Appointment booking form
│   │   ├── my-appointments.php     # Appointment history & status
│   │   └── notifications.php       # Notification center
│   │
│   └── 📁 doctor/
│       ├── dashboard.php           # Doctor home/analytics
│       ├── appointments.php        # View appointment requests
│       ├── schedule.php            # Manage working hours
│       └── notifications.php       # Notification center
│
├── 📁 actions/
│   ├── book_appointment.php        # Process appointment booking
│   ├── cancel_appointment.php      # Process appointment cancellation
│   └── get_doctors.php             # API endpoint for doctor list
│
├── 📁 assets/
│   ├── 📁 css/
│   │   └── style.css               # Global styles & responsive design
│   ├── 📁 js/
│   │   └── script.js               # Client-side interactions
│   └── 📁 images/                  # Logo and UI assets
│
├── 📁 scripts/
│   ├── setup_database.sql          # Database schema & initialization
│   └── test-db.php                 # DB connection validator
│
└── 📁 database/
    └── db.sql                      # Additional SQL scripts
```

---

## 🚀 Installation & Setup

### Option 1: Local Setup (XAMPP/Windows)

#### Prerequisites
- XAMPP (or similar PHP/MySQL stack)
- PHP 8.0+
- MySQL 8.0+
- Git

#### Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Chris-Shema-1/appointment-management.git
   cd appointment-management
   ```

2. **Extract to XAMPP htdocs**
   ```bash
   xcopy . "C:\xampp\htdocs\appointment-management" /E /I
   ```

3. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create new database: `appointment_system`
   - Import SQL file:
     - Click `appointment_system` → **Import** tab
     - Choose `scripts/setup_database.sql`
     - Click **Import**

4. **Start Services**
   - Start Apache and MySQL from XAMPP Control Panel
   - Open browser: `http://localhost/appointment-management`

5. **Test Connection**
   ```bash
   php scripts/test-db.php
   ```
   Expected output: "Connected to DB" or connection details.

---

### Option 2: Docker Setup (Recommended for Deployment)

#### Prerequisites
- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose (usually included with Docker Desktop)

#### Quick Start

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Chris-Shema-1/appointment-management.git
   cd appointment-management
   ```

2. **Build and Start Containers**
   ```bash
   docker-compose down -v  # (Optional) Reset database volume
   docker-compose up -d --build
   ```

3. **Verify Services**
   ```bash
   docker-compose ps
   ```
   All services should show status "Up".

4. **Test Database Connection**
   ```bash
   docker-compose exec app php scripts/test-db.php
   ```

5. **Access the Application**
   - **Main App:** http://localhost:8080
   - **phpMyAdmin:** http://localhost:8081 (Server: `db`, User: `root`, Password: `root_password`)

#### Docker Services

| Service | Port | Container | Purpose |
|---------|------|-----------|---------|
| **app** | 8080 | `appointment_app` | PHP/Apache web application |
| **db** | 3307 | `appointment_db` | MySQL 8.0 database |
| **phpmyadmin** | 8081 | `appointment_phpmyadmin` | Database UI management |

#### Common Docker Commands

```bash
# View logs
docker-compose logs -f app
docker-compose logs -f db

# Access container shell
docker-compose exec app bash
docker-compose exec db bash

# Stop services
docker-compose stop

# Restart services
docker-compose restart

# Full reset (deletes DB data)
docker-compose down -v
```

---

## 🗄️ Database Setup

### Schema Overview

The database includes the following tables:

| Table | Purpose |
|-------|---------|
| **users** | Stores patient and doctor profiles (email, password, name, phone) |
| **doctors** | Doctor-specific info (specialization, qualification, experience, fees) |
| **appointments** | Appointment records with status tracking |
| **doctor_schedule** | Doctor availability (working hours per day) |
| **notifications** | User notifications (read/unread status) |

### Initialize Database

**Automatically (Docker):**
- Database is initialized on first `docker-compose up` by importing `scripts/setup_database.sql`

**Manually (XAMPP):**
- Import `scripts/setup_database.sql` via phpMyAdmin or MySQL CLI:
  ```bash
  mysql -u root -p appointment_system < scripts/setup_database.sql
  ```

---

## 👤 Test Credentials

Default test users are pre-loaded in the database schema:

### Patient Account
- **Email:** `john.doe@example.com`
- **Password:** `password123`
- **Role:** Patient

### Doctor Account
- **Email:** `dr.sarah@clinic.com`
- **Password:** `password123`
- **Role:** Doctor

---

## 🔧 Configuration

### Environment Variables (Docker)

The application reads database configuration from environment variables. Edit `docker-compose.yml`:

```yaml
environment:
  - DB_HOST=db              # Database service name
  - DB_USER=app_user        # MySQL user
  - DB_PASS=app_password    # MySQL password
  - DB_NAME=appointment_system
  - DB_PORT=3306
```

### Local Configuration (XAMPP)

Database credentials in `config/database.php` use defaults:
```php
$db_host = '127.0.0.1';           # Force TCP (not socket)
$db_user = 'root';
$db_pass = '';
$db_name = 'appointment_system';
$db_port = 3306;
```

---

## 🧪 Usage

### User Registration

1. Navigate to registration page
2. Fill in: Name, Email, Phone, Password
3. Select role: **Patient** or **Doctor**
4. Submit form
5. Redirect to login

### Patient Workflow

1. **Login** → Dashboard
2. **Book Appointment:**
   - Select doctor from list (view specialization, experience, fees)
   - Choose date and time
   - Enter reason for visit
   - Confirm booking
3. **View Appointments:**
   - Filter by status (Pending, Confirmed, Completed, Cancelled)
   - Cancel pending/confirmed appointments
4. **Notifications:**
   - Receive updates on appointment status
   - View notification history

### Doctor Workflow

1. **Login** → Dashboard (view stats)
2. **View Appointments:**
   - See pending requests
   - Approve/reject with notes
   - View confirmed & completed appointments
3. **Manage Schedule:**
   - Set working hours
   - Update availability
4. **Notifications:**
   - Receive new appointment requests
   - Patient cancellations

---

## 🐛 Troubleshooting

### Docker Issues

**"Connection refused" error**
- Ensure DB service is running: `docker-compose ps`
- Check DB logs: `docker-compose logs db`
- Verify environment variables in `docker-compose.yml`

**"Table doesn't exist" error**
- Database not initialized. Run:
  ```bash
  docker-compose down -v
  docker-compose up -d --build
  ```

**"No such file or directory" when connecting**
- App is using Unix socket instead of TCP
- Ensure `DB_HOST` is set to service name (`db`) not `localhost`

### Local (XAMPP) Issues

**MySQL connection failed**
- Ensure MySQL is running (XAMPP Control Panel)
- Check credentials in `config/database.php`
- Verify port is 3306 (not in use)

**"Table doesn't exist"**
- Import SQL schema via phpMyAdmin or:
  ```bash
  mysql -u root appointment_system < scripts/setup_database.sql
  ```

**Session/Login issues**
- Clear browser cookies
- Check `config/session.php` for require_patient()/require_doctor() functions
- Verify `$_SESSION` is enabled in PHP

---

## 🔒 Security Considerations

### Implemented
✅ Password hashing with `password_hash()` and `password_verify()`
✅ SQL injection prevention via prepared statements (`mysqli->prepare()`)
✅ XSS protection via `htmlspecialchars()`
✅ Session-based authentication with role checks
✅ CSRF tokens (basic implementation)

### Recommended for Production
- Use HTTPS (SSL/TLS certificate)
- Implement rate limiting on login
- Add email verification for registration
- Use environment variable files (.env) instead of hardcoded credentials
- Implement audit logging
- Add two-factor authentication (2FA)
- Use Docker secrets for sensitive data
- Regular security audits and updates

---

## 📊 Key Improvements Made

### Bug Fixes
✅ Fixed "mysqli object is already closed" error by relocating `closeConnection()` after all includes
✅ Normalized database host handling to prevent Unix socket usage in Docker
✅ Improved error messages for DB connection debugging

### Docker Compatibility
✅ Made `config/database.php` read from environment variables
✅ Added fallback to TCP connection (127.0.0.1) to avoid socket errors
✅ Updated `docker-compose.yml` with proper service networking
✅ Added DB health check to prevent premature connections

### Developer Experience
✅ Added `scripts/test-db.php` for quick connection validation
✅ Comprehensive Docker setup with phpMyAdmin
✅ Clear documentation for local and containerized environments

---

## 🎯 Future Enhancements

- [ ] Email notifications (appointment confirmations, reminders)
- [ ] SMS reminders for upcoming appointments
- [ ] Appointment rescheduling functionality
- [ ] Doctor ratings and reviews system
- [ ] Advanced filtering and search
- [ ] Appointment cancellation fees
- [ ] Document/prescription upload
- [ ] REST API for mobile app integration
- [ ] Real-time notifications with WebSockets
- [ ] Payment gateway integration

---

## 📝 Assignment Checklist

- ✅ Functional appointment booking system
- ✅ User authentication (patients and doctors)
- ✅ Database schema with relationships
- ✅ Role-based access control
- ✅ Appointment status management
- ✅ Notification system
- ✅ Responsive design
- ✅ Security best practices
- ✅ Docker containerization
- ✅ Comprehensive documentation

---

## 📚 References

- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL 8.0 Documentation](https://dev.mysql.com/doc/)
- [Docker Documentation](https://docs.docker.com/)
- [OWASP Top 10 - Security Risks](https://owasp.org/www-project-top-ten/)

---

## 👨‍💼 Author

**Shema Christian**

- GitHub: [@Chris-Shema-1](https://github.com/Chris-Shema-1)
- Email: (your email here)

---

## 📄 License

This project is created for **educational purposes** as a final examination project. 

**Academic Institution:** [Your School/University Name]  
**Course:** [Course Name]  
**Academic Year:** 2024-2025

---

## 🙏 Acknowledgments

- Inspired by real-world clinic management systems
- Thanks to open-source community for tools and libraries
- Special thanks to instructors and peers for feedback

---

**Last Updated:** December 2025  
**Status:** Complete & Production-Ready