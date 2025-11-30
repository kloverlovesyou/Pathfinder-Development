# 🚀 COPY & PASTE DEPLOYMENT GUIDE

Follow these steps in order. Just copy and paste!

## STEP 1: Generate Your APP_KEY

Open terminal in your project root and run:

```bash
cd backend
php artisan key:generate --show
```

**Copy the output** (it starts with `base64:`). You'll need it in Step 3.

---

## STEP 2: Commit Files to Your Repository

```bash
git add Dockerfile docker-entrypoint.sh render.yaml
git commit -m "Add Render deployment configuration"
git push origin primo
```

*(Replace `primo` with your branch name if different)*

---

## STEP 3: In Render Dashboard - Set Environment Variables

Go to your Render "New Web Service" page → Click **"Environment"** tab → Add these variables:

### Copy this entire block and paste into Render:

```
APP_NAME=Pathfinder Dev
APP_ENV=production
APP_KEY=PASTE_YOUR_KEY_FROM_STEP_1_HERE
APP_DEBUG=false
APP_URL=https://pathfinder-dev.onrender.com

DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
DB_SSLMODE=require

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-gmail-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME=Pathfinder Dev

SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database
LOG_LEVEL=error
```

**Replace these values:**
- `PASTE_YOUR_KEY_FROM_STEP_1_HERE` → Your APP_KEY from Step 1
- `db.xxxxx.supabase.co` → Your Supabase host
- `your_supabase_password` → Your Supabase password
- `your-email@gmail.com` → Your Gmail address
- `your-gmail-app-password` → Your Gmail App Password
- `pathfinder-dev.onrender.com` → Will be your service name (see Step 4)

---

## STEP 4: In Render Dashboard - Configure Service

In the "New Web Service" form:

- **Name**: `pathfinder-dev` (or any unique name you want)
- **Language**: `Docker` ✅
- **Branch**: `primo` (or your branch)
- **Region**: `Oregon (US West)` ✅
- **Root Directory**: ⬜ **Leave EMPTY**
- **Build Command**: ⬜ **Leave EMPTY**
- **Start Command**: ⬜ **Leave EMPTY**

**Important:** After you set the Name, update `APP_URL` in Step 3 to match:
- If name is `pathfinder-dev` → URL is `https://pathfinder-dev.onrender.com`
- If name is `my-pathfinder` → URL is `https://my-pathfinder.onrender.com`

---

## STEP 5: Click "Deploy Web Service"

Just click the button and wait 5-10 minutes! ☕

---

## STEP 6: After Deployment (When it's Live)

1. Go to Render Dashboard → Your Service → **Shell**
2. Run this command:
   ```bash
   php artisan migrate --force
   ```
3. Done! ✅

---

## That's It! 🎉

Your service should be live at: `https://[your-service-name].onrender.com`

---

## ⚠️ If Something Goes Wrong

1. Check **Logs** tab in Render
2. Verify all environment variables are set (Step 3)
3. Make sure APP_KEY is correct
4. Check database credentials

