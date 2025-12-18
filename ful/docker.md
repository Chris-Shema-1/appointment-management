# 🐳 Docker Setup Guide
## Appointment Management System

This guide explains how to run the Appointment Management System using Docker.

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **Docker Desktop** (Windows/Mac) or **Docker Engine** (Linux)
  - Download: https://www.docker.com/products/docker-desktop
  - Verify installation: `docker --version`
  
- **Docker Compose** (usually included with Docker Desktop)
  - Verify installation: `docker-compose --version`

---

## 🚀 Quick Start

### 1. Clone or Navigate to Project Directory

```bash
cd appointment-system
```

### 2. Build and Start Containers

```bash
docker-compose up -d --build
```

**What this does:**
- Builds the PHP/Apache application image
- Downloads MySQL 8.0 image
- Downloads phpMyAdmin image
- Creates a network for the containers
- Starts all services in detached mode (-d)
- Automatically imports the database schema

### 3. Access the Application

Once containers are running, access the application:

- **Main Application:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081
  - Server: `db`
  - Username: `root`
  - Password: `root_password`

---

## 🔧 Docker Services

### Service Details

| Service | Port | Container Name | Purpose |
|---------|------|----------------|---------|
| app | 8080 | appointment_app | PHP/Apache web application |
| db | 3307 | appointment_db | MySQL 8.0 database |
| phpmyadmin | 8081 | appointment_phpmyadmin | Database management UI |

### Service Architecture

```
┌─────────────────────────────────────────────┐
│  Docker Host (Your Computer)                │
│                                             │
│  ┌──────────────────────────────────────┐  │
│  │  appointment_network (Bridge)         │  │
│  │                                       │  │
│  │  ┌────────────┐  ┌────────────┐     │  │
│  │  │    app     │  │     db     │     │  │
│  │  │  (PHP 8.2) │──│  (MySQL)   │     │  │
│  │  │   :8080    │  │   :3307    │     │  │
│  │  └────────────┘  └────────────┘     │  │
│  │         │              │             │  │
│  │         └──────┬───────┘             │  │
│  │                │                     │  │
│  │         ┌──────────────┐            │  │
│  │         │ phpmyadmin   │            │  │
│  │         │    :8081     │            │  │
│  │         └──────────────┘            │  │
│  └──────────────────────────────────────┘  │
└─────────────────────────────────────────────┘
```

---

## 📝 Common Commands

### Start Services
```bash
# Start all services
docker-compose up -d

# Start with rebuild (after code changes)
docker-compose up -d --build

# View logs
docker-compose logs -f

# View logs for specific service
docker-compose logs -f app
```

### Stop Services
```bash
# Stop all services
docker-compose stop

# Stop and remove containers
docker-compose down

# Stop and remove containers + volumes (WARNING: deletes database data)
docker-compose down -v
```

### Check Status
```bash
# List running containers
docker-compose ps

# Check container health
docker ps
```

### Execute Commands in Container
```bash
# Access app container shell
docker exec -it appointment_app bash

# Access database container shell
docker exec -it appointment_db bash

# Run MySQL commands directly
docker exec -it appointment_db mysql -u root -p appointment_system
```

---

## 🗄️ Database Management

### Initial Database Setup

The database is automatically initialized when you first run `docker-compose up`. The SQL file at `database/appointment_system.sql` is imported automatically.

### Access Database via phpMyAdmin

1. Go to http://localhost:8081
2. Login with:
   - Server: `db`
   - Username: `root`
   - Password: `root_password`
3. Select `appointment_system` database

### Access Database via Command Line

```bash
# Connect to MySQL
docker exec -it appointment_db mysql -u root -p

# Enter password: root_password

# Use the database
USE appointment_system;

# Run queries
SELECT * FROM users;
```

### Backup Database

```bash
# Export database to SQL file
docker exec appointment_db mysqldump -u root -proot_password appointment_system > backup.sql

# Import database from SQL file
docker exec -i appointment_db mysql -u root -proot_password appointment_system < backup.sql
```

---

## 🔧 Configuration

### Environment Variables

Database configuration is set in `docker-compose.yml`:

```yaml
environment:
  MYSQL_DATABASE: appointment_system
  MYSQL_ROOT_PASSWORD: root_password
  MYSQL_USER: app_user
  MYSQL_PASSWORD: app_password
```

**To change database credentials:**
1. Edit `docker-compose.yml`
2. Update the environment variables
3. Rebuild containers: `docker-compose up -d --build`

### Port Configuration

**To change application ports:**

Edit `docker-compose.yml`:

```yaml
services:
  app:
    ports:
      - "8080:80"  # Change 8080 to your desired port
  
  db:
    ports:
      - "3307:3306"  # Change 3307 to your desired port
  
  phpmyadmin:
    ports:
      - "8081:80"  # Change 8081 to your desired port
```

Then restart: `docker-compose down && docker-compose up -d`

---

## 🐛 Troubleshooting

### Container Won't Start

**Check logs:**
```bash
docker-compose logs app
docker-compose logs db
```

**Common issues:**
- Port already in use: Change ports in docker-compose.yml
- Database not ready: Wait a few seconds and try again
- Permission issues: Run `docker-compose down -v` and restart

### Database Connection Failed

**Check if database is healthy:**
```bash
docker-compose ps
```

Look for "healthy" status on the db service.

**Test connection:**
```bash
docker exec -it appointment_db mysql -u root -proot_password -e "SHOW DATABASES;"
```

### Application Shows Errors

**Check PHP errors:**
```bash
docker-compose logs app
```

**Restart application:**
```bash
docker-compose restart app
```

### Reset Everything

**WARNING: This deletes all data!**

```bash
# Stop and remove everything
docker-compose down -v

# Remove images
docker rmi appointment-system-app

# Start fresh
docker-compose up -d --build
```

---

## 📦 Data Persistence

### Volumes

Data is persisted using Docker volumes:

- **db_data**: Stores MySQL database files
- **Application files**: Mounted from host directory (live updates)

**View volumes:**
```bash
docker volume ls
```

**Inspect volume:**
```bash
docker volume inspect appointment-system_db_data
```

---

## 🚀 Production Deployment

### Security Considerations

**For production deployment:**

1. **Change default passwords** in `docker-compose.yml`
2. **Use environment files** (.env) instead of hardcoded values
3. **Remove phpMyAdmin** service (or restrict access)
4. **Enable HTTPS** with reverse proxy (nginx/traefik)
5. **Set proper file permissions**
6. **Use Docker secrets** for sensitive data

### Example Production docker-compose.yml

```yaml
version: '3.8'

services:
  db:
    image: mysql:8.0
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD_FILE: /run/secrets/db_root_password
      MYSQL_DATABASE: appointment_system
      MYSQL_USER_FILE: /run/secrets/db_user
      MYSQL_PASSWORD_FILE: /run/secrets/db_password
    secrets:
      - db_root_password
      - db_user
      - db_password
    volumes:
      - db_data:/var/lib/mysql

  app:
    build: .
    restart: always
    ports:
      - "80:80"
    depends_on:
      - db

secrets:
  db_root_password:
    file: ./secrets/db_root_password.txt
  db_user:
    file: ./secrets/db_user.txt
  db_password:
    file: ./secrets/db_password.txt

volumes:
  db_data:
```

---

## 📚 Additional Resources

- Docker Documentation: https://docs.docker.com/
- Docker Compose Documentation: https://docs.docker.com/compose/
- MySQL Docker Image: https://hub.docker.com/_/mysql
- PHP Docker Image: https://hub.docker.com/_/php

---

## ✅ Testing the Setup

### Test Checklist

After running `docker-compose up -d`, verify:

- [ ] All containers running: `docker-compose ps`
- [ ] Application accessible: http://localhost:8080
- [ ] phpMyAdmin accessible: http://localhost:8081
- [ ] Database has tables: Check via phpMyAdmin
- [ ] Can register new user
- [ ] Can login successfully
- [ ] Application functions normally

### Default Test Credentials

**Patients:**
- Email: `john.doe@example.com`
- Password: `password123`

**Doctors:**
- Email: `dr.brown@clinic.com`
- Password: `password123`

---

## 🎓 Assignment Notes

This Docker setup fulfills the Dockerization requirement (Requirement #8) of the project assignment:

✅ Application is containerized
✅ Database is containerized
✅ Services are orchestrated with Docker Compose
✅ Documentation provided
✅ Easy deployment with single command
✅ Portable across different environments

---

## 🆘 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review Docker logs: `docker-compose logs`
3. Verify all prerequisites are installed
4. Ensure ports 8080, 8081, and 3307 are available

---

**Last Updated:** December 2024  
**Docker Version:** 24.0+  
**Docker Compose Version:** 2.0+