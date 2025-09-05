# QLink - Smart Queuing System

A modern, mobile-first queuing system built for schools using PHP 8+, MySQL, Bootstrap 5, and vanilla JavaScript.

## 🚀 Features

- **Smart Queue Management**: Join queues, track position, and get real-time updates
- **Role-Based Access**: Students, Staff, and Admin roles with appropriate permissions
- **Real-Time Notifications**: In-app and SMS notifications when you're next in line
- **Mobile-First Design**: Responsive interface that works on all devices
- **Department Management**: Multiple departments with independent queues
- **Analytics Dashboard**: Track wait times, peak hours, and completion rates
- **Security Features**: CSRF protection, prepared statements, and session hardening

## 🛠️ Tech Stack

- **Backend**: PHP 8+ with OOP principles
- **Database**: MySQL with InnoDB engine
- **Frontend**: Bootstrap 5 + Vanilla JavaScript
- **SMS Service**: Semaphore API integration
- **Security**: CSRF tokens, password hashing, prepared statements
- **Server**: XAMPP compatible

## 📁 Project Structure

```
QLink/
├── index.php                 # Landing page
├── login.php                 # User authentication
├── register.php              # User registration
├── dashboard.php             # Student dashboard
├── staff/                    # Staff management console
│   └── index.php
├── admin/                    # Admin panel
│   └── index.php
├── api/                      # API endpoints
│   ├── auth/                 # Authentication APIs
│   ├── queues/               # Queue management APIs
│   ├── notifications/        # Notification APIs
│   └── analytics/            # Analytics APIs
├── includes/                 # Core classes and functions
│   ├── config.php            # Configuration settings
│   ├── Database.php          # Database connection class
│   ├── Auth.php              # Authentication class
│   └── csrf.php              # CSRF protection
├── assets/                   # Frontend assets
│   ├── css/app.css           # Custom styles
│   └── js/app.js             # JavaScript utilities
└── database/                 # Database files
    └── qlink.sql             # Complete database schema
```

## 🚀 Installation

### Prerequisites

- XAMPP (Apache + MySQL + PHP 8+)
- MySQL 5.7+ or MariaDB 10.2+
- PHP 8.0+ with PDO and cURL extensions

### Step 1: Setup Database

1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a new database named `qlink`
4. Import the `database/qlink.sql` file

### Step 2: Configure Application

1. Copy the project files to your XAMPP htdocs folder
2. Edit `includes/config.php`:
   ```php
   // Update database credentials if needed
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'qlink');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   
   // Add your Semaphore SMS API key
   define('SEMAPHORE_API_KEY', 'your_api_key_here');
   ```

### Step 3: Access the Application

- **Landing Page**: `http://localhost/QLink/`
- **Login**: `http://localhost/QLink/login.php`
- **Student Dashboard**: `http://localhost/QLink/dashboard.php`
- **Staff Console**: `http://localhost/QLink/staff/`
- **Admin Panel**: `http://localhost/QLink/admin/`

## 👥 Default Users

After importing the database, you can login with these accounts:

### Admin User
- **Email**: `admin@qlink.edu.ph`
- **Password**: `admin123`
- **Role**: Full system access

### Staff Users
- **Email**: `john.smith@qlink.edu.ph`
- **Password**: `staff123`
- **Role**: Queue management

### Student Users
- **Email**: `alice.brown@student.qlink.edu.ph`
- **Password**: `student123`
- **Role**: Join queues and track status

## 🔧 Configuration

### SMS Notifications

1. Sign up for a Semaphore account: https://semaphore.co/
2. Get your API key
3. Update `includes/config.php`:
   ```php
   define('SEMAPHORE_API_KEY', 'your_actual_api_key');
   ```

### Database Settings

Update database connection in `includes/config.php`:
```php
define('DB_HOST', 'your_db_host');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### Security Settings

Configure security parameters in `includes/config.php`:
```php
define('SESSION_LIFETIME', 3600);        // Session timeout (seconds)
define('CSRF_TOKEN_LIFETIME', 1800);     // CSRF token timeout (seconds)
define('PASSWORD_MIN_LENGTH', 8);        // Minimum password length
```

## 📱 Usage

### For Students

1. **Register/Login**: Create an account or sign in
2. **Join Queue**: Select a department and join the queue
3. **Track Status**: Monitor your position and estimated wait time
4. **Get Notified**: Receive notifications when you're next in line
5. **Cancel Queue**: Leave the queue if needed

### For Staff

1. **Login**: Access staff console with staff credentials
2. **Select Department**: Choose the department to manage
3. **Call Next**: Call the next customer in line
4. **Manage Customers**: Mark customers as done or skip them
5. **Monitor Queue**: View waiting customers and current status

### For Admins

1. **Dashboard**: View system overview and statistics
2. **Department Management**: Add, edit, and manage departments
3. **User Management**: Manage staff and student accounts
4. **Analytics**: View detailed reports and statistics
5. **System Settings**: Configure system parameters

## 🔒 Security Features

- **CSRF Protection**: All forms protected against CSRF attacks
- **SQL Injection Prevention**: Prepared statements throughout
- **Password Security**: Bcrypt hashing for passwords
- **Session Security**: Secure session handling with regeneration
- **Input Validation**: Server-side validation and sanitization
- **Role-Based Access**: Strict permission checking

## 📊 Database Schema

### Core Tables

- **users**: User accounts and authentication
- **departments**: Department information
- **queues**: Queue entries and status
- **dept_now_serving**: Current serving status per department
- **notifications**: In-app and SMS notifications
- **queue_history**: Archived queue data
- **activity_log**: System activity tracking

### Key Features

- **Automatic Ticket Numbers**: Daily reset per department
- **Queue Status Tracking**: Waiting → Serving → Done
- **Notification System**: Next-10 alerts and status updates
- **History Archiving**: Automatic cleanup and reporting

## 🚧 Next Steps

### Phase 1: Core APIs (Current)
- [x] Authentication system
- [x] User management
- [x] Basic queue operations
- [x] Staff console
- [x] Admin dashboard

### Phase 2: Queue Management APIs
- [ ] Join queue endpoint
- [ ] Queue status endpoint
- [ ] Cancel queue endpoint
- [ ] Staff queue management endpoints

### Phase 3: Notification System
- [ ] Next-10 notification triggers
- [ ] SMS integration
- [ ] In-app notification system

### Phase 4: Analytics & Reporting
- [ ] Wait time analytics
- [ ] Peak hour analysis
- [ ] Completion rate reports
- [ ] Export functionality

### Phase 5: Advanced Features
- [ ] QR code tickets
- [ ] Missed turn handling
- [ ] Department-specific settings
- [ ] Mobile app integration

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP MySQL service is running
   - Verify database credentials in `config.php`
   - Ensure database `qlink` exists

2. **CSRF Token Errors**
   - Check session configuration
   - Verify `config.php` settings
   - Clear browser cookies

3. **SMS Notifications Not Working**
   - Verify Semaphore API key
   - Check API endpoint configuration
   - Review error logs

### Debug Mode

Enable debug mode in `includes/config.php`:
```php
define('DEBUG_MODE', true);
```

This will show detailed error messages and log database queries.

## 📝 API Documentation

### Authentication Endpoints

- `POST /api/auth/login.php` - User login
- `POST /api/auth/register.php` - User registration
- `POST /api/auth/logout.php` - User logout

### Queue Endpoints

- `POST /api/queues/join.php` - Join a queue
- `GET /api/queues/status.php` - Get queue status
- `POST /api/queues/cancel.php` - Cancel queue position

### Staff Endpoints

- `POST /api/queues/call-next.php` - Call next customer
- `POST /api/queues/skip.php` - Skip customer
- `POST /api/queues/done.php` - Mark customer as done

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:

1. Check the troubleshooting section above
2. Review the error logs
3. Check the database for data integrity
4. Verify all configuration settings

## 🔄 Updates

### Version 1.0.0 (Current)
- Initial release with core functionality
- Basic queue management
- User authentication system
- Staff and admin interfaces

### Planned Updates
- Enhanced analytics dashboard
- Mobile app companion
- Advanced notification system
- Multi-language support

---

**QLink** - Making queuing smart and simple for schools everywhere! 🎓✨
