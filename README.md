# Fajracct Secured LMS

A modern, secure, subscription-based Learning Management System built with PHP 8+ and MySQL, featuring the distinctive "Fajracct Style" design with glassmorphism effects and parallax scrolling.

## Features

### Core Features
- ✅ **Modern Authentication System** - Secure registration/login with OTP verification
- ✅ **Role-Based Access Control** - Admin, Instructor, and Student roles
- ✅ **Course Management** - Complete course creation and management system
- ✅ **Video Delivery** - Secure video hosting with Vimeo/Bunny.net integration
- ✅ **Progress Tracking** - Detailed learning analytics and completion tracking
- ✅ **Quiz System** - Interactive quizzes and assessments
- ✅ **Subscription Plans** - Flexible monthly, annual, and per-course pricing
- ✅ **Payment Integration** - bKash and Nagad payment gateways
- ✅ **Certificates** - Auto-generated certificates upon course completion
- ✅ **Reviews & Ratings** - Course feedback system

### Design Features
- 🎨 **Fajracct Style** - Professional, minimal design with generous white space
- ✨ **Glassmorphism** - Modern glass-effect UI elements
- 📜 **Parallax Scrolling** - Smooth multi-layered scrolling effects
- 📱 **Fully Responsive** - Optimized for all devices
- ⚡ **Fast & Lightweight** - No heavy frameworks, pure PHP

## Technology Stack

- **Backend**: PHP 8+
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Icons**: Lucide Icons
- **Fonts**: Google Fonts (Poppins, Inter)

## Installation Instructions

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache/Nginx)
- cPanel access (for cPanel deployment)

### Local Development Setup

1. **Clone or extract the project**
   ```bash
   cd /path/to/your/webroot
   ```

2. **Configure Database**
   - Edit `config/database.php`
   - Update database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'fajracct_lms');
     define('DB_USER', 'your_db_user');
     define('DB_PASS', 'your_db_password');
     ```

3. **Create Database**
   ```sql
   CREATE DATABASE fajracct_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Import Database Schema**
   ```bash
   mysql -u your_db_user -p fajracct_lms < config/schema.sql
   ```

5. **Configure Site Settings**
   - Edit `config/config.php`
   - Update `SITE_URL` and `SITE_EMAIL`:
     ```php
     define('SITE_URL', 'http://localhost/fajracct-lms');
     define('SITE_EMAIL', 'noreply@yourdomain.com');
     ```

6. **Set Permissions**
   ```bash
   chmod 755 -R .
   chmod 777 -R uploads/
   ```

7. **Access the Application**
   - Open browser: `http://localhost/fajracct-lms`
   - Default admin login:
     - Email: `admin@fajracct.com`
     - Password: `admin123`
   - **IMPORTANT**: Change admin password immediately after first login!

### cPanel Deployment

1. **Create MySQL Database**
   - Log into cPanel
   - Go to "MySQL Databases"
   - Create a new database (e.g., `username_fajracct`)
   - Create a database user with strong password
   - Add user to database with ALL PRIVILEGES

2. **Upload Files**
   - Compress the entire project folder to `fajracct-lms.zip`
   - In cPanel File Manager, navigate to `public_html`
   - Upload `fajracct-lms.zip`
   - Extract the archive
   - Move all files from `fajracct-lms` folder to `public_html` (or subdirectory)

3. **Configure Database**
   - Edit `config/database.php` using File Manager editor
   - Update with your cPanel database credentials:
     ```php
     define('DB_HOST', 'localhost');  // Usually localhost
     define('DB_NAME', 'username_fajracct');
     define('DB_USER', 'username_dbuser');
     define('DB_PASS', 'your_strong_password');
     ```

4. **Import Database**
   - Go to cPanel phpMyAdmin
   - Select your database
   - Click "Import" tab
   - Choose `config/schema.sql`
   - Click "Go"

5. **Configure Site Settings**
   - Edit `config/config.php`
   - Update `SITE_URL`:
     ```php
     define('SITE_URL', 'https://yourdomain.com');
     define('SITE_EMAIL', 'noreply@yourdomain.com');
     ```

6. **Set Permissions**
   - In File Manager, select `uploads` folder
   - Click "Permissions"
   - Set to `777` or `755` (depending on server configuration)

7. **Configure .htaccess** (if needed)
   - Create `.htaccess` in root directory:
     ```apache
     # Enable Rewrite Engine
     RewriteEngine On
     
     # Force HTTPS
     RewriteCond %{HTTPS} off
     RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
     
     # Security Headers
     <IfModule mod_headers.c>
         Header set X-Content-Type-Options "nosniff"
         Header set X-Frame-Options "SAMEORIGIN"
         Header set X-XSS-Protection "1; mode=block"
     </IfModule>
     
     # Prevent directory listing
     Options -Indexes
     ```

8. **Test Installation**
   - Visit your domain
   - Login with admin credentials
   - Change admin password immediately

## Configuration

### Email Configuration
Edit `config/config.php` to configure email settings:

```php
// For production, use SMTP or email service
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
```

### SMS Configuration (for OTP)
Integrate with Bangladeshi SMS gateway:

```php
define('SMS_API_URL', 'https://api.smsgateway.com/send');
define('SMS_API_KEY', 'your-api-key');
```

### Payment Gateway Configuration

#### bKash
```php
define('BKASH_APP_KEY', 'your-app-key');
define('BKASH_APP_SECRET', 'your-app-secret');
define('BKASH_USERNAME', 'your-username');
define('BKASH_PASSWORD', 'your-password');
define('BKASH_BASE_URL', 'https://checkout.pay.bka.sh'); // Production URL
```

#### Nagad
```php
define('NAGAD_MERCHANT_ID', 'your-merchant-id');
define('NAGAD_MERCHANT_NUMBER', 'your-merchant-number');
define('NAGAD_PUBLIC_KEY', 'your-public-key');
define('NAGAD_PRIVATE_KEY', 'your-private-key');
define('NAGAD_BASE_URL', 'https://api.mynagad.com'); // Production URL
```

### Video Hosting Configuration

#### Vimeo
```php
define('VIMEO_ACCESS_TOKEN', 'your-access-token');
define('VIMEO_CLIENT_ID', 'your-client-id');
define('VIMEO_CLIENT_SECRET', 'your-client-secret');
```

#### Bunny.net
```php
define('BUNNY_API_KEY', 'your-api-key');
define('BUNNY_LIBRARY_ID', 'your-library-id');
define('BUNNY_STREAM_URL', 'your-stream-url');
```

## Project Structure

```
fajracct-lms-php/
├── admin/              # Admin dashboard and management
├── api/                # API endpoints
│   ├── auth/          # Authentication APIs
│   ├── courses/       # Course management APIs
│   └── payments/      # Payment processing APIs
├── assets/            # Static assets
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   └── images/       # Images and icons
├── components/        # Reusable PHP components
├── config/           # Configuration files
│   ├── config.php    # Main configuration
│   ├── database.php  # Database connection
│   └── schema.sql    # Database schema
├── includes/         # Helper classes
│   ├── Auth.php      # Authentication helper
│   └── Course.php    # Course management helper
├── instructor/       # Instructor dashboard
├── student/          # Student dashboard
├── uploads/          # User uploads
│   ├── courses/      # Course materials
│   ├── videos/       # Video files
│   └── certificates/ # Generated certificates
├── index.php         # Landing page
├── login.php         # Login page
├── register.php      # Registration page
└── README.md         # This file
```

## Security Features

- ✅ **PDO Prepared Statements** - SQL injection prevention
- ✅ **Password Hashing** - Bcrypt with cost factor 12
- ✅ **CSRF Protection** - Token-based CSRF prevention
- ✅ **XSS Prevention** - Input sanitization and output escaping
- ✅ **Session Security** - Secure session handling
- ✅ **OTP Verification** - Email and SMS verification
- ✅ **Role-Based Access** - Granular permission system

## Default Credentials

**Admin Account**:
- Email: `admin@fajracct.com`
- Password: `admin123`

**⚠️ IMPORTANT**: Change the admin password immediately after installation!

## Support & Documentation

### Common Issues

**Database Connection Error**:
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check database user has proper privileges

**File Upload Issues**:
- Verify `uploads/` directory has write permissions (777 or 755)
- Check PHP `upload_max_filesize` and `post_max_size` settings

**Email Not Sending**:
- Configure SMTP settings in `config/config.php`
- For development, check error logs for email content
- For production, use transactional email service (SendGrid, Mailgun)

### Development Notes

- Error reporting is enabled by default in `config/config.php`
- For production, set `error_reporting(0)` and `ini_set('display_errors', 0)`
- All passwords are hashed using Bcrypt
- OTP codes expire after 10 minutes
- Session lifetime is 7 days by default

## Roadmap

- [ ] WhatsApp Business API integration
- [ ] Live class scheduling
- [ ] Discussion forums
- [ ] Mobile app (React Native)
- [ ] Advanced analytics dashboard
- [ ] Bulk course import/export
- [ ] Multi-language support

## License

Proprietary - All rights reserved

## Credits

- **Design**: Fajracct Style
- **Icons**: Lucide Icons
- **Fonts**: Google Fonts (Poppins, Inter)

---

**Built with ❤️ for Fajracct**

For support, contact: support@fajracct.com
