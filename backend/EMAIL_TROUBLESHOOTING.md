# Email Not Sending - Troubleshooting Guide

## Your Configuration Looks Correct! ✅

Your `.env` settings are now correct:
- ✅ MAIL_PORT=587 (correct)
- ✅ MAIL_PASSWORD without spaces (correct)
- ✅ MAIL_FROM_ADDRESS matches Gmail (correct)

## If Emails Still Not Sending:

### Step 1: Clear Config Cache on Railway

Railway might be using cached config. After updating `.env`:

1. **Option A: Via Railway Console**
   - Go to Railway Dashboard → Your Service → Deployments
   - Open the latest deployment logs
   - Or use Railway's console/terminal if available
   - Run: `php artisan config:clear`

2. **Option B: Force Redeploy**
   - Make a small change to trigger redeploy
   - Or manually trigger redeploy in Railway

### Step 2: Test Email Configuration

Use the test endpoint to see what's happening:

```javascript
// In browser console (F12)
fetch('https://pathfinder-development-production.up.railway.app/api/test-email', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'judeandrei.f.pasicolan@gmail.com' })
})
.then(r => r.json())
.then(data => {
  console.log('Email Test Result:', data);
  alert(JSON.stringify(data, null, 2));
});
```

This will show:
- Your actual mail configuration
- Any errors
- Whether password/username are set

### Step 3: Check Registration Response

After registering, check the response. It now includes:
- `email_sent`: true/false
- `mail_config`: Your current configuration
- `email_error`: Specific error if failed
- `verification_url`: Manual link if email failed

### Step 4: Common Railway Issues

1. **Config Cache**: Railway might cache old config
   - Solution: Clear cache or redeploy

2. **Outbound SMTP Blocked**: Some hosting blocks port 587
   - Solution: Try port 465 with SSL:
     ```env
     MAIL_PORT=465
     MAIL_ENCRYPTION=ssl
     ```

3. **Gmail App Password Issues**:
   - Verify the App Password is still valid
   - Generate a new one if needed: https://myaccount.google.com/apppasswords
   - Make sure 2FA is enabled

4. **Silent Failures**: Mail might fail without throwing exception
   - Check Railway logs for email errors
   - Use `MAIL_MAILER=log` temporarily to see if emails are generated

### Step 5: Use Log Driver for Testing

To verify the email system works (without actually sending):

1. Temporarily set in Railway `.env`:
   ```env
   MAIL_MAILER=log
   ```

2. Register a new user

3. Check Railway logs - you should see the email content logged

4. If you see the email in logs, the system works - the issue is SMTP configuration

5. Switch back to `MAIL_MAILER=smtp` and fix SMTP settings

### Step 6: Check Railway Logs

Railway logs will show:
- "Verification email sent successfully" = ✅ Working
- "Failed to send verification email" = ❌ Check error details
- "Attempting to send verification email" = Shows mail config being used

## Quick Checklist:

- [ ] `.env` updated with correct values
- [ ] Railway redeployed after `.env` changes
- [ ] Config cache cleared (`php artisan config:clear`)
- [ ] Test endpoint shows correct mail_config
- [ ] Gmail App Password is valid (not expired)
- [ ] 2FA enabled on Gmail account
- [ ] Checked Railway logs for email errors
- [ ] Tried log driver to verify email generation works

## Still Not Working?

1. **Check the registration response** - it now shows detailed email status
2. **Use the test endpoint** - it shows your exact configuration
3. **Check Railway logs** - look for email-related errors
4. **Try log driver** - verify emails are being generated

The registration response will tell you exactly what's wrong!

