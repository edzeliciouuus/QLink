# QLink Setup Guide

## Prerequisites
- XAMPP installed and running
- PHP 8.0+ 
- MySQL 5.7+ or MariaDB 10.2+

## Quick Setup

### 1. Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Wait for both services to show "Running" status

### 2. Import Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `qlink`
3. Import the file: `database/qlink.sql`
4. Verify tables are created:
   - `users`
   - `departments` 
   - `queues`
   - `dept_now_serving`
   - `notifications`
   - `queue_history`
   - `system_settings`
   - `activity_log`

### 3. Test System
1. Visit: http://localhost/QLink/test_system.php
2. Check all components show ✓ (green checkmarks)
3. If database connection fails, ensure MySQL is running

### 4. Test Registration
1. Visit: http://localhost/QLink/register.php
2. Fill out the registration form
3. Submit and verify no errors occur
4. Check database for new user

### 5. Test Login
1. Visit: http://localhost/QLink/login.php
2. Use credentials from step 4
3. Verify successful login and redirect

## Default Users (after import)

### Admin User
- Email: admin@qlink.com
- Password: admin123
- Role: admin

### Staff User  
- Email: staff@qlink.com
- Password: staff123
- Role: staff

### Student User
- Email: student@qlink.com
- Password: student123
- Role: student

## Troubleshooting

### "Not Found" Errors
- Ensure Apache is running
- Check file paths are correct
- Verify .htaccess files exist (if using URL rewriting)

### Database Connection Errors
- Ensure MySQL is running
- Check database credentials in `includes/config.php`
- Verify database `qlink` exists

### CSRF Token Errors
- Check session configuration
- Ensure `includes/csrf.php` is loaded
- Verify browser accepts cookies

### Permission Errors
- Check file permissions (should be readable by web server)
- Ensure `api/` directory is accessible

## File Structure
```
QLink/
├── api/
│   └── auth/
│       ├── login.php
│       ├── register.php
│       └── logout.php
├── includes/
│   ├── config.php
│   ├── Database.php
│   ├── Auth.php
│   └── csrf.php
├── assets/
│   ├── css/app.css
│   └── js/app.js
├── database/
│   └── qlink.sql
├── staff/
│   └── index.php
├── admin/
│   └── index.php
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── test_system.php
└── SETUP.md
```

## Next Steps
After successful setup:
1. Test all user roles (student, staff, admin)
2. Create additional departments
3. Test queue functionality
4. Configure SMS notifications (Semaphore API)
5. Customize UI and branding

## Support
If you encounter issues:
1. Check the test_system.php output
2. Review Apache and MySQL error logs
3. Verify all required files exist
4. Check browser console for JavaScript errors
