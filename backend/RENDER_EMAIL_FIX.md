# Fix Email Verification on Render

## Problem:
- ✅ Verification tokens are being saved to database
- ❌ Emails not sending - Gmail SMTP is blocked on Render
- Render blocks outbound SMTP ports (587, 465) like Railway does

## Solution: Use Brevo API (Recommended)

Your app already has Brevo API integration built-in! You just need to add the API key.

### Step 1: Get Your Brevo API Key

1. **Sign up/Login** to Brevo: https://www.brevo.com
2. **Go to API Keys**: 
   - Click on your profile → **SMTP & API**
   - Or go directly to: https://app.brevo.com/settings/keys/api
3. **Create/Get API Key**:
   - Scroll to **"API Keys"** section (NOT SMTP section)
   - Click **"Generate a new API key"** or copy existing one
   - Copy the API key (it looks like: `xkeysib-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`)

### Step 2: Add to Render Environment Variables

1. Go to **Render Dashboard**: https://dashboard.render.com
2. Select your **backend service**
3. Go to **Environment** → **Environment Variables**
4. Add this variable:
   ```
   BREVO_API_KEY=your-brevo-api-key-here
   ```
   (Replace `your-brevo-api-key-here` with the actual key from Step 1)

5. **Save** - Render will auto-redeploy (takes 2-5 minutes)

### Step 3: Optional - Keep Gmail SMTP as Fallback

You can keep your Gmail SMTP settings, but the app will use Brevo API first:

```env
# Brevo API (Primary - works on Render)
BREVO_API_KEY=xkeysib-your-key-here

# Gmail SMTP (Fallback - may not work on Render)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=judeandrei.f.pasicolan@gmail.com
MAIL_PASSWORD=fwwmqqxsdnyqtuju
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=judeandrei.f.pasicolan@gmail.com
MAIL_FROM_NAME="Pathfinder"
```

The app will:
1. ✅ Try Brevo API first (if `BREVO_API_KEY` is set)
2. ⚠️ Fall back to Gmail SMTP if Brevo fails (may not work on Render)

### Step 4: Test Email

After redeploy, test the email endpoint:

```javascript
fetch('https://your-backend.onrender.com/api/test-email', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'judeandrei.f.pasicolan@gmail.com' })
})
.then(r => r.json())
.then(data => {
  console.log('Email Test Result:', data);
  console.log('Method used:', data.method); // Should show "brevo_api"
  console.log('Config:', data.mail_config);
});
```

**Check the response:**
- `method` should be `"brevo_api"` (not `"smtp"`)
- `mail_config.brevo_api_key_set` should be `true`
- `status` should be `"success"`

## Why Gmail SMTP Doesn't Work on Render

Render (like Railway) blocks outbound SMTP connections on ports 587 and 465 to prevent spam. This is why:
- ✅ Brevo API works (uses HTTPS port 443)
- ❌ Gmail SMTP fails (needs port 587/465)

## Alternative: Use Brevo SMTP Instead of Gmail

If you prefer SMTP, you can switch to Brevo SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-email@example.com
MAIL_PASSWORD=your-brevo-smtp-key  # Get from Brevo dashboard (different from API key)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-brevo-email@example.com
MAIL_FROM_NAME="Pathfinder"
```

But **Brevo API is still recommended** because:
- ✅ Works even if SMTP ports are blocked
- ✅ Faster (no SMTP handshake)
- ✅ Better error messages
- ✅ More reliable

## Quick Checklist

- [ ] Signed up for Brevo account (free)
- [ ] Got API key from https://app.brevo.com/settings/keys/api
- [ ] Added `BREVO_API_KEY` to Render environment variables
- [ ] Saved and Render redeployed
- [ ] Tested with `/api/test-email` endpoint
- [ ] Verified `method` shows `"brevo_api"` in response
- [ ] Tested user registration - email should arrive

## Troubleshooting

### Issue: "BREVO_API_KEY is not configured"
- **Solution**: Make sure you added `BREVO_API_KEY` in Render Dashboard → Environment → Environment Variables (not just local .env)

### Issue: "Brevo API authentication failed"
- **Solution**: Double-check your API key is correct (starts with `xkeysib-`)
- **Solution**: Make sure you copied the API key, not the SMTP key

### Issue: Still seeing SMTP errors in logs
- **Solution**: The app tries Brevo API first, then SMTP. Check logs to see if Brevo API is working
- **Solution**: Look for "Verification email sent via Brevo API" in logs (should see this if working)

## Need Help?

Check Render logs:
1. Go to Render Dashboard → Your Service → Logs
2. Look for messages like:
   - ✅ "Verification email sent via Brevo API" - Working!
   - ❌ "SMTP failed for verification email" - SMTP blocked (expected, Brevo should handle it)
   - ❌ "Brevo API key is missing" - `BREVO_API_KEY` not set




