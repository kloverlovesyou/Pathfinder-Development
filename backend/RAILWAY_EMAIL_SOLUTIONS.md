# Railway Email Connection Timeout - Solutions

## Problem:
- ✅ Verification tokens are being saved to database
- ❌ Emails not sending - "Connection timed out" to smtp.gmail.com:587
- This suggests Railway is blocking outbound SMTP connections

## Solution Options:

### Option 1: Use Log Driver (Quick Test)

Verify the email system works by logging emails instead of sending:

**In Railway Environment Variables:**
```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=judeandrei.f.pasicolan@gmail.com
MAIL_FROM_NAME="Pathfinder"
```

This will:
- Generate emails and log them to `storage/logs/laravel.log`
- Verify the email system works
- Let you see the verification links in logs

**To view logs on Railway:**
- Go to Railway Dashboard → Your Service → Logs
- Look for email content after registration

### Option 2: Use a Professional Email Service (Recommended for Production)

Railway often blocks direct SMTP. Use a service designed for this:

#### A. Mailgun (Free tier: 5,000 emails/month)

1. Sign up: https://www.mailgun.com
2. Get your API key and domain
3. Update Railway environment variables:
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-api-key
MAIL_FROM_ADDRESS=noreply@your-domain.mailgun.org
MAIL_FROM_NAME="Pathfinder"
```

#### B. SendGrid (Free tier: 100 emails/day)

1. Sign up: https://sendgrid.com
2. Create API key
3. Update Railway environment variables:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Pathfinder"
```

#### C. Postmark (Free tier: 100 emails/month)

1. Sign up: https://postmarkapp.com
2. Get API token
3. Update Railway environment variables:
```env
MAIL_MAILER=postmark
POSTMARK_TOKEN=your-postmark-token
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Pathfinder"
```

### Option 3: Try Gmail with Different Port/SSL

Sometimes port 465 with SSL works when 587 doesn't:

**In Railway Environment Variables:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=judeandrei.f.pasicolan@gmail.com
MAIL_PASSWORD=fwwmqqxsdnyqtuju
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=judeandrei.f.pasicolan@gmail.com
MAIL_FROM_NAME="Pathfinder"
```

### Option 4: Manual Verification (Temporary Workaround)

Since tokens are being saved, you can manually verify users:

1. Get verification link from registration response (it's included if email fails)
2. Or query database for `email_verification_token`
3. Visit: `https://pathfinder-development-production.up.railway.app/api/verify-email?token={token}&type=organization`

## Recommended: Use Log Driver First

**Step 1:** Set in Railway:
```env
MAIL_MAILER=log
```

**Step 2:** Register a new organization

**Step 3:** Check Railway logs - you'll see the email content with verification link

**Step 4:** Use that link to verify the email

**Step 5:** Once verified, switch to a professional email service (Mailgun/SendGrid) for production

## Why Railway Blocks SMTP:

Many cloud platforms (including Railway) block outbound SMTP ports (587, 465) to prevent spam. This is why professional email services (Mailgun, SendGrid) work better - they use HTTP APIs instead of SMTP.

## Quick Fix Right Now:

1. Set `MAIL_MAILER=log` in Railway
2. Register - check logs for verification link
3. Manually verify the email using the link from logs
4. Later, set up Mailgun or SendGrid for automatic emails

