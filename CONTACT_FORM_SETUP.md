# Contact Form Setup Guide

## Overview
This guide explains how to deploy and configure the NTS G.R.O.W contact form to receive emails at `info@ntsgrow.com`.

## Current Setup Status

### ✅ Already Configured
- **Contact Form**: Located in `contact.html` with proper form fields
- **PHP Handler**: `contact-handler.php` processes form submissions
- **JavaScript**: Handles form submission with AJAX and user feedback
- **Email Recipient**: Set to `info@ntsgrow.com`
- **Validation**: Both client-side and server-side validation implemented

## Requirements

### Server Requirements
1. **Web Server**: Apache or Nginx
2. **PHP**: Version 7.0 or higher
3. **PHP Mail Function**: Must be enabled on the server
4. **Domain**: Must be hosted on a live domain (not localhost)

### Email Requirements
1. **Valid Domain**: `ntsgrow.com` domain must be properly configured
2. **DNS Records**: MX records must be set up for email delivery
3. **Email Account**: `info@ntsgrow.com` must be a valid, active email account

## Deployment Steps

### Step 1: Choose a Web Hosting Provider
Select a hosting provider that supports PHP and email functionality:

**Recommended Hosting Options:**
- **Shared Hosting**: SiteGround, Bluehost, HostGator
- **VPS/Cloud**: DigitalOcean, Linode, Vultr
- **Managed WordPress**: WP Engine, Kinsta (if using WordPress)

### Step 2: Upload Files
Upload all website files to your hosting server via:
- **FTP/SFTP**: Using FileZilla, WinSCP, or similar
- **File Manager**: Through hosting control panel
- **Git**: Clone repository directly to server

**Required Files:**
```
/
├── index.html
├── contact.html
├── contact-handler.php
├── features.html
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/
│   └── fonts/
└── other HTML files
```

### Step 3: Configure Domain and DNS
1. **Point Domain**: Configure `ntsgrow.com` to point to your hosting server
2. **MX Records**: Set up mail exchange records for email delivery
3. **A Records**: Ensure domain resolves to correct IP address

### Step 4: Set Up Email Account
1. **Create Email**: Set up `info@ntsgrow.com` in your hosting email panel
2. **Test Email**: Send a test email to verify it's working
3. **Configure Client**: Set up email client (Gmail, Outlook, etc.) if needed

### Step 5: Test PHP Mail Function
Create a simple test file to verify PHP mail works:

```php
<?php
// test-mail.php
$to = "info@ntsgrow.com";
$subject = "Test Email";
$message = "This is a test email to verify PHP mail function.";
$headers = "From: noreply@ntsgrow.com";

if (mail($to, $subject, $message, $headers)) {
    echo "Test email sent successfully!";
} else {
    echo "Failed to send test email.";
}
?>
```

Upload this file and visit `yoursite.com/test-mail.php` to test.

## Configuration Options

### Email Settings (contact-handler.php)
Current configuration:
```php
// Recipient email
$to = 'info@ntsgrow.com';

// From email
$headers = "From: noreply@ntsgrow.com\r\n";

// Subject line
$subject = 'New Contact Form Submission - NTS G.R.O.W';
```

### Customization Options
You can modify these settings in `contact-handler.php`:

1. **Change Recipient**: Update `$to` variable
2. **Modify Subject**: Change `$subject` variable
3. **Update From Address**: Modify `$headers` variable
4. **Add CC/BCC**: Add additional header lines

## Troubleshooting

### Common Issues

#### 1. Emails Not Being Sent
**Symptoms**: Form submits but no emails received
**Solutions**:
- Verify PHP mail() function is enabled
- Check server mail logs
- Ensure domain has proper MX records
- Try using SMTP instead of mail() function

#### 2. Emails Going to Spam
**Symptoms**: Emails sent but appear in spam folder
**Solutions**:
- Set up SPF records in DNS
- Configure DKIM authentication
- Use authenticated SMTP service
- Ensure "From" email uses same domain

#### 3. Form Not Submitting
**Symptoms**: Form doesn't submit or shows errors
**Solutions**:
- Check browser console for JavaScript errors
- Verify `contact-handler.php` exists and is accessible
- Ensure all form fields have correct names
- Check server error logs

#### 4. 500 Internal Server Error
**Symptoms**: Server error when submitting form
**Solutions**:
- Check PHP error logs
- Verify PHP syntax in `contact-handler.php`
- Ensure proper file permissions (644 for files, 755 for directories)

### Advanced Configuration (SMTP)

For better email delivery, consider using SMTP instead of PHP mail():

```php
// Example SMTP configuration (requires PHPMailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; // or your SMTP server
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

## Testing the Contact Form

### Test Checklist
1. ✅ Fill out all required fields
2. ✅ Submit form and verify success message appears
3. ✅ Check `info@ntsgrow.com` for received email
4. ✅ Test with invalid email address (should show error)
5. ✅ Test with empty fields (should show validation errors)
6. ✅ Test form reset after successful submission

### Test Data
Use this sample data for testing:
```
Full Name: John Smith
Email: test@example.com
Company Name: Test Company Ltd
Phone: +1234567890
Message: This is a test message from the contact form.
```

## Security Considerations

### Current Security Features
- ✅ Input sanitization with `htmlspecialchars()`
- ✅ Email validation with `filter_var()`
- ✅ Required field validation
- ✅ POST method only
- ✅ CSRF protection via form origin

### Additional Security Recommendations
1. **Rate Limiting**: Implement to prevent spam
2. **Captcha**: Add reCAPTCHA for bot protection
3. **IP Blocking**: Block known spam IP addresses
4. **Input Length Limits**: Restrict message length
5. **File Upload Protection**: If adding file uploads

## Monitoring and Maintenance

### Regular Checks
1. **Monthly**: Test contact form functionality
2. **Quarterly**: Review and clear error logs
3. **Annually**: Update PHP version and dependencies

### Email Monitoring
1. Set up email monitoring alerts
2. Configure backup email addresses
3. Regularly check spam folders
4. Monitor email delivery rates

## Support

### Hosting Provider Support
Contact your hosting provider if:
- PHP mail() function is not working
- Email delivery issues persist
- Server configuration problems

### Email Service Support
Contact your email provider if:
- Emails not reaching inbox
- Email account access issues
- DNS/MX record problems

## Contact Form Flow

```
User fills form → JavaScript validation → Form submission → 
PHP validation → Email sent → Success/Error message → 
Email received at info@ntsgrow.com
```

---

**Note**: This contact form is ready to use once deployed to a live server with PHP support. The main requirement is ensuring your hosting environment supports PHP mail() function and that `info@ntsgrow.com` is properly configured to receive emails.
