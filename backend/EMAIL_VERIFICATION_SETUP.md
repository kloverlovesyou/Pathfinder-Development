# Email Verification Setup Guide

## Quick Diagnosis

If emails are not being sent, follow these steps:

### 1. Check Email Configuration

The most common issue is missing or incorrect email configuration in your `.env` file.

### 2. For Testing (Development)

Use the **log driver** to test email functionality without actually sending emails. Emails will be logged to `storage/logs/laravel.log`.

Add to your `.env` file:
```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@pathfinder.com
MAIL_FROM_NAME="Pathfinder"
```

Then check `storage/logs/laravel.log` after registration to see the email content.

### 3. For Production (SMTP)

Configure SMTP settings in your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@pathfinder.com
MAIL_FROM_NAME="Pathfinder"
```

**For Gmail:**
- You need to use an **App Password**, not your regular password
- Enable 2-factor authentication first
- Generate an app password: https://myaccount.google.com/apppasswords

### 4. Test Email Configuration

Use the test endpoint to verify your email setup:

```bash
POST /api/test-email
{
  "email": "your-test-email@example.com"
}
```

This will:
- Attempt to send a test email
- Show your current mail configuration
- Display any errors if sending fails

### 5. Check Logs

Check `storage/logs/laravel.log` for detailed error messages:

```bash
tail -f storage/logs/laravel.log
```

Look for:
- "Verification email sent successfully" - Email was sent
- "Failed to send verification email" - Email failed (check error details)

### 6. Common Issues

#### Issue: "Connection timeout"
- **Solution**: Check your SMTP host and port settings
- **Solution**: Ensure firewall allows outbound connections on port 587/465

#### Issue: "Authentication failed"
- **Solution**: Verify MAIL_USERNAME and MAIL_PASSWORD are correct
- **Solution**: For Gmail, use App Password instead of regular password

#### Issue: "No emails in inbox"
- **Solution**: Check spam/junk folder
- **Solution**: Verify MAIL_FROM_ADDRESS is valid
- **Solution**: Check if emails are being logged (if using log driver)

#### Issue: "Mail driver not configured"
- **Solution**: Ensure MAIL_MAILER is set in .env file
- **Solution**: Run `php artisan config:clear` after changing .env

### 7. Debug Mode

When `APP_DEBUG=true` in your `.env`, the registration response will include:
- `email_sent`: Boolean indicating if email was sent
- `email_error`: Error message if sending failed
- `debug_message`: Helpful suggestions

### 8. Verify Email Endpoint

The verification link format is:
```
GET /api/verify-email?token={verification_token}&type={applicant|organization}
```

Make sure your frontend URL matches your backend URL when generating verification links.

### 9. Resend Verification Email

If you didn't receive the email, you can resend it:

```bash
POST /api/resend-verification
{
  "emailAddress": "user@example.com",
  "type": "applicant"  // or "organization"
}
```

## Quick Setup Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Configure MAIL_MAILER in .env (use 'log' for testing)
- [ ] Set MAIL_FROM_ADDRESS and MAIL_FROM_NAME
- [ ] If using SMTP, configure MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Test with `/api/test-email` endpoint
- [ ] Register a new user and check logs/email

## Need Help?

1. Check `storage/logs/laravel.log` for detailed errors
2. Use the test endpoint: `POST /api/test-email`
3. Verify your .env configuration matches the examples above
4. For Gmail, ensure you're using an App Password

