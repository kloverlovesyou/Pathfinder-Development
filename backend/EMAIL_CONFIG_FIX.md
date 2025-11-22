# Email Configuration Fix

## Issues Found:

1. **MAIL_PORT=1025** - This is incorrect for Gmail. Gmail uses:
   - Port **587** for TLS (recommended)
   - Port **465** for SSL

2. **MAIL_PASSWORD with spaces** - Gmail App Passwords should be used without spaces

## Correct Configuration:

Update your `.env` file with these settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=judeandrei.f.pasicolan@gmail.com
MAIL_PASSWORD=fwwmqqxsdnyqtuju
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=judeandrei.f.pasicolan@gmail.com
MAIL_FROM_NAME="Pathfinder"
```

## Important Notes:

1. **Remove spaces from App Password**: `fwwm qqxs dnyq tuju` → `fwwmqqxsdnyqtuju`

2. **Port 587** is for TLS encryption (which matches your MAIL_ENCRYPTION=tls)

3. **MAIL_FROM_ADDRESS** should match your Gmail address or be a verified sender address

4. **After updating .env**, run:
   ```bash
   php artisan config:clear
   ```

5. **Test the configuration**:
   ```bash
   POST /api/test-email
   {
     "email": "your-test-email@example.com"
   }
   ```

## If Still Not Working:

1. **Verify App Password**: Make sure you're using a Gmail App Password (not your regular password)
   - Go to: https://myaccount.google.com/apppasswords
   - Generate a new App Password if needed
   - Copy it WITHOUT spaces

2. **Check Gmail Settings**:
   - Enable "Less secure app access" (if still available)
   - Or use 2-Factor Authentication + App Password (recommended)

3. **Try Port 465 with SSL**:
   ```env
   MAIL_PORT=465
   MAIL_ENCRYPTION=ssl
   ```

4. **Check Firewall**: Ensure port 587 or 465 is not blocked

5. **Check Logs**: Look at `storage/logs/laravel.log` for detailed error messages

