# Quick Email Configuration Check

## Your Current Configuration Issues:

Based on what you shared earlier, you had:
```env
MAIL_PORT=1025  ❌ WRONG - Should be 587
MAIL_PASSWORD=fwwm qqxs dnyq tuju  ❌ Has spaces
```

## ✅ Correct Configuration:

Update your Railway `.env` file with:

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

## Steps to Fix:

1. **Go to Railway Dashboard** → Your Project → Variables/Environment
2. **Update these variables:**
   - `MAIL_PORT` = `587` (not 1025)
   - `MAIL_PASSWORD` = `fwwmqqxsdnyqtuju` (remove all spaces)
   - `MAIL_FROM_ADDRESS` = `judeandrei.f.pasicolan@gmail.com`
3. **Redeploy** your application (Railway should auto-redeploy when env vars change)
4. **Test** by registering a new organization

## After Registration, Check the Response:

The registration response now includes:
- `email_sent`: `true` or `false`
- `mail_config`: Shows your current mail configuration
- `email_error`: Error message if sending failed
- `verification_url`: Manual verification link if email failed

## Quick Test:

After fixing `.env`, test with:
```javascript
fetch('https://pathfinder-development-production.up.railway.app/api/test-email', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'judeandrei.f.pasicolan@gmail.com' })
})
.then(r => r.json())
.then(console.log);
```

## Common Issues:

1. **Port 1025** → Change to **587**
2. **Password with spaces** → Remove all spaces
3. **Wrong FROM address** → Use your Gmail address
4. **App Password expired** → Generate new one at https://myaccount.google.com/apppasswords

