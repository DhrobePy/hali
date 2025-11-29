# Fajracct LMS - Deployment Checklist

## Pre-Deployment

- [ ] Review all configuration files
- [ ] Prepare database credentials
- [ ] Obtain SSL certificate for domain
- [ ] Set up email service (SMTP/SendGrid/Mailgun)
- [ ] Register for payment gateways (bKash, Nagad)
- [ ] Set up video hosting (Vimeo or Bunny.net)
- [ ] Prepare SMS gateway for OTP

## cPanel Deployment Steps

### 1. Database Setup
- [ ] Create MySQL database in cPanel
- [ ] Create database user with strong password
- [ ] Grant ALL PRIVILEGES to user
- [ ] Note credentials for configuration

### 2. File Upload
- [ ] Upload fajracct-lms-php.zip to cPanel
- [ ] Extract files to public_html or subdirectory
- [ ] Verify all files extracted correctly

### 3. Configuration
- [ ] Edit `config/database.php` with database credentials
- [ ] Edit `config/config.php` with site URL and email
- [ ] Set `SITE_URL` to your domain
- [ ] Set `SITE_EMAIL` to your email address

### 4. Database Import
- [ ] Access phpMyAdmin
- [ ] Select your database
- [ ] Import `config/schema.sql`
- [ ] Verify all tables created (17 tables total)

### 5. Permissions
- [ ] Set `uploads/` folder to 755 or 777
- [ ] Set `uploads/courses/` to 755 or 777
- [ ] Set `uploads/videos/` to 755 or 777
- [ ] Set `uploads/certificates/` to 755 or 777

### 6. Security Configuration
- [ ] Change admin password (default: admin123)
- [ ] Disable error display in `config/config.php`
- [ ] Enable HTTPS redirect in `.htaccess`
- [ ] Review security headers in `.htaccess`

### 7. API Integration
- [ ] Configure SMTP settings for email
- [ ] Add SMS gateway credentials
- [ ] Add bKash API credentials
- [ ] Add Nagad API credentials
- [ ] Add Vimeo or Bunny.net credentials
- [ ] Test email sending
- [ ] Test SMS sending
- [ ] Test payment gateway (sandbox first)

### 8. Testing
- [ ] Access homepage - verify design loads correctly
- [ ] Test user registration with OTP
- [ ] Test login functionality
- [ ] Test password reset
- [ ] Test course browsing
- [ ] Test enrollment process
- [ ] Test video playback
- [ ] Test quiz functionality
- [ ] Test certificate generation
- [ ] Test payment flow (sandbox)
- [ ] Test all user roles (Admin, Instructor, Student)

### 9. Content Setup
- [ ] Login as admin
- [ ] Create course categories
- [ ] Add first course
- [ ] Upload course thumbnail
- [ ] Add course modules and lessons
- [ ] Upload/link video content
- [ ] Create quizzes
- [ ] Publish course
- [ ] Test enrollment as student

### 10. Production Readiness
- [ ] Switch payment gateways to production mode
- [ ] Verify SSL certificate is active
- [ ] Test all forms with HTTPS
- [ ] Check all external links
- [ ] Verify email deliverability
- [ ] Test on mobile devices
- [ ] Test on different browsers
- [ ] Set up backup schedule
- [ ] Configure monitoring/alerts

## Post-Deployment

### Immediate Actions
- [ ] Change all default passwords
- [ ] Remove or secure test accounts
- [ ] Verify error logging is working
- [ ] Check file upload limits
- [ ] Test contact forms
- [ ] Verify analytics tracking

### Marketing Setup
- [ ] Add Google Analytics (optional)
- [ ] Set up Facebook Pixel (optional)
- [ ] Configure SEO meta tags
- [ ] Submit sitemap to Google
- [ ] Set up social media links

### Ongoing Maintenance
- [ ] Schedule regular database backups
- [ ] Monitor error logs
- [ ] Update PHP version as needed
- [ ] Review security patches
- [ ] Monitor disk space usage
- [ ] Review user feedback
- [ ] Update course content regularly

## Troubleshooting

### Common Issues

**Database Connection Error**
- Verify credentials in `config/database.php`
- Check database user has proper privileges
- Ensure MySQL service is running

**File Upload Fails**
- Check folder permissions (755 or 777)
- Verify PHP upload limits in `.htaccess`
- Check available disk space

**Email Not Sending**
- Verify SMTP credentials
- Check spam folder
- Test with different email provider
- Review error logs

**Payment Gateway Errors**
- Verify API credentials
- Check sandbox vs production mode
- Review gateway documentation
- Test with small amounts first

**Video Not Playing**
- Verify video URL is accessible
- Check video provider credentials
- Test video URL directly in browser
- Review browser console for errors

## Support Contacts

- **Technical Support**: support@fajracct.com
- **Emergency**: [Your emergency contact]
- **Hosting Provider**: [Your cPanel provider]
- **Payment Gateway Support**: [bKash/Nagad support]

## Backup Strategy

- **Daily**: Automated database backup
- **Weekly**: Full file system backup
- **Monthly**: Off-site backup copy
- **Before Updates**: Manual backup

## Security Checklist

- [ ] Strong passwords for all accounts
- [ ] HTTPS enabled site-wide
- [ ] Regular security updates
- [ ] Firewall configured
- [ ] Intrusion detection enabled
- [ ] Regular security audits
- [ ] User data encryption
- [ ] Secure API keys storage

---

**Deployment Date**: _______________
**Deployed By**: _______________
**Version**: 1.0.0
**Notes**: _______________________________________________
