# Fix Email Configuration on Railway

## Problem Found:

Your Railway environment is using:
- ❌ `MAIL_HOST=mailpit` (should be `smtp.gmail.com`)
- ❌ `MAIL_PORT=1025` (should be `587`)

This means Railway's environment variables are different from your local `.env` file.

## Solution: Update Railway Environment Variables

### Step 1: Go to Railway Dashboard

1. Open: https://railway.app
2. Select your project: `pathfinder-development-production`
3. Click on your **Laravel backend service**

### Step 2: Open Variables/Environment Tab

1. Look for **"Variables"** or **"Environment"** tab
2. Or go to **Settings** → **Variables**

### Step 3: Update These Variables

Find and update (or create if missing):

```
MAIL_MAILER = smtp
MAIL_HOST = smtp.gmail.com
MAIL_PORT = 587
MAIL_USERNAME = judeandrei.f.pasicolan@gmail.com
MAIL_PASSWORD = fwwmqqxsdnyqtuju
MAIL_ENCRYPTION = tls
MAIL_FROM_ADDRESS = judeandrei.f.pasicolan@gmail.com
MAIL_FROM_NAME = Pathfinder
```

**Important:**
- Remove any `MAIL_HOST=mailpit` variable
- Make sure `MAIL_PORT=587` (not 1025)
- Remove spaces from `MAIL_PASSWORD`

### Step 4: Save and Redeploy

1. **Save** all variable changes
2. Railway should **auto-redeploy** when you save
3. Wait for deployment to complete

### Step 5: Clear Config Cache (After Redeploy)

If Railway has a console/terminal:
```bash
php artisan config:clear
```

Or trigger another redeploy to ensure cache is cleared.

### Step 6: Test Again

After redeploy, test the email:

```javascript
fetch('https://pathfinder-development-production.up.railway.app/api/test-email', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'judeandrei.f.pasicolan@gmail.com' })
})
.then(r => r.json())
.then(data => {
  console.log('Mail Config:', data.mail_config);
  alert(JSON.stringify(data, null, 2));
});
```

**Check the `mail_config` in the response:**
- `host` should be `smtp.gmail.com` (not `mailpit`)
- `port` should be `587` (not `1025`)

## Why This Happened:

Railway uses its own environment variables, separate from your local `.env` file. When you update your local `.env`, those changes don't automatically sync to Railway. You must update them in Railway's dashboard.

## Quick Checklist:

- [ ] Go to Railway Dashboard → Your Service → Variables
- [ ] Update `MAIL_HOST` to `smtp.gmail.com` (remove `mailpit`)
- [ ] Update `MAIL_PORT` to `587` (not 1025)
- [ ] Verify `MAIL_PASSWORD` has no spaces
- [ ] Save variables (Railway will redeploy)
- [ ] Wait for deployment to complete
- [ ] Test email endpoint again
- [ ] Check `mail_config` in response matches your settings

