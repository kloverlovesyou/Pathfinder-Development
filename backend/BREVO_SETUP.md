# Brevo Email Setup Guide

## Step 1: Get Your Brevo Credentials

1. **Sign up/Login**: Go to https://www.brevo.com
2. **Go to SMTP & API**: 
   - Click on your profile → **SMTP & API**
   - Or go to: https://app.brevo.com/settings/keys/api
3. **Get SMTP Key**:
   - Under **SMTP** section, click **"Show"** next to your SMTP key
   - Copy the SMTP key (it looks like: `xsmtpib-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`)
4. **Note your email**: Your Brevo account email (the one you used to sign up)

## Step 2: Update Railway Environment Variables

Go to Railway Dashboard → Your Service → Variables, and set:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-email@example.com
MAIL_PASSWORD=your-smtp-key-from-brevo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-brevo-email@example.com
MAIL_FROM_NAME="Pathfinder"
```

**Important:**
- `MAIL_USERNAME` = Your Brevo account email address
- `MAIL_PASSWORD` = Your Brevo SMTP key (the long string starting with `xsmtpib-`)
- `MAIL_FROM_ADDRESS` = Can be your Brevo email or any verified sender email in Brevo

## Step 3: Verify Sender Email (Optional but Recommended)

1. In Brevo dashboard, go to **Settings** → **Senders**
2. Add and verify your sender email address
3. Use that verified email as `MAIL_FROM_ADDRESS`

## Step 4: Save and Redeploy

1. **Save** all variables in Railway
2. Railway will **auto-redeploy**
3. Wait for deployment to complete

## Step 5: Test Email

After redeploy, test the email:

```javascript
fetch('https://pathfinder-development-production.up.railway.app/api/test-email', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'your-test-email@example.com' })
})
.then(r => r.json())
.then(data => {
  console.log('Email Test Result:', data);
  alert(JSON.stringify(data, null, 2));
});
```

## Brevo Free Tier Limits

- **300 emails/day** (free tier)
- Perfect for development and small production use
- No credit card required

## Alternative: Use Port 465 with SSL

If port 587 doesn't work, try:

```env
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

## Troubleshooting

### Issue: "Authentication failed"
- **Solution**: Double-check your SMTP key (not your account password)
- Make sure there are no extra spaces in `MAIL_PASSWORD`

### Issue: "Connection timeout"
- **Solution**: Try port 465 with SSL instead of 587 with TLS

### Issue: Emails going to spam
- **Solution**: Verify your sender email in Brevo dashboard
- Use a custom domain if possible

## Quick Checklist

- [ ] Signed up for Brevo account
- [ ] Got SMTP key from Brevo dashboard
- [ ] Updated Railway environment variables
- [ ] Set `MAIL_HOST=smtp-relay.brevo.com`
- [ ] Set `MAIL_PORT=587` (or 465)
- [ ] Set `MAIL_USERNAME` to your Brevo email
- [ ] Set `MAIL_PASSWORD` to your SMTP key
- [ ] Saved variables (Railway redeployed)
- [ ] Tested with `/api/test-email` endpoint
- [ ] Registered a new user to test verification email

