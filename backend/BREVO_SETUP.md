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

## Step 2: Update Render Environment Variables

Go to Render Dashboard → Your Service → Environment, and set:

```env
# Option 1: SMTP Configuration (Fallback)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-email@example.com
MAIL_PASSWORD=your-smtp-key-from-brevo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-brevo-email@example.com
MAIL_FROM_NAME="Pathfinder"

# Option 2: Brevo API (Recommended - Works better on Render)
BREVO_API_KEY=your-brevo-api-key-here
```

**Important:**
- For **SMTP**: `MAIL_USERNAME` = Your Brevo account email address, `MAIL_PASSWORD` = Your Brevo SMTP key (the long string starting with `xsmtpib-`)
- For **API** (Recommended): Get your API key from https://app.brevo.com/settings/keys/api (under "API Keys" section)
- `MAIL_FROM_ADDRESS` = Can be your Brevo email or any verified sender email in Brevo
- The app will automatically use the Brevo API if `BREVO_API_KEY` is set, otherwise it will fall back to SMTP

## Step 3: Verify Sender Email (Optional but Recommended)

1. In Brevo dashboard, go to **Settings** → **Senders**
2. Add and verify your sender email address
3. Use that verified email as `MAIL_FROM_ADDRESS`

## Step 4: Save and Redeploy

1. **Save** all variables in Render Dashboard
2. Render will **auto-redeploy** when environment variables change
3. Wait for deployment to complete (usually 2-5 minutes)

## Step 5: Test Email

After redeploy, test the email:

**Replace `https://your-backend.onrender.com` with your actual Render backend URL:**

```javascript
fetch('https://your-backend.onrender.com/api/test-email', {
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

**To find your Render backend URL:**
1. Go to Render Dashboard → Your Backend Service
2. The URL will be shown at the top (e.g., `https://pathfinder-backend-xxxx.onrender.com`)

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
- [ ] Got API key from Brevo dashboard (recommended) OR SMTP key
- [ ] Updated Render environment variables
- [ ] Set `BREVO_API_KEY` (recommended) OR configured SMTP settings
- [ ] If using SMTP: Set `MAIL_HOST=smtp-relay.brevo.com`
- [ ] If using SMTP: Set `MAIL_PORT=587` (or 465)
- [ ] If using SMTP: Set `MAIL_USERNAME` to your Brevo email
- [ ] If using SMTP: Set `MAIL_PASSWORD` to your SMTP key
- [ ] Saved variables in Render Dashboard
- [ ] Render redeployed successfully
- [ ] Tested with `/api/test-email` endpoint
- [ ] Registered a new user to test verification email

