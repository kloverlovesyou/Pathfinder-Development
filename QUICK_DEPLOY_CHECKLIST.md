# Quick Render Deployment Checklist

## Before You Click "Deploy Web Service"

### ✅ 1. Environment Variables Setup

Go to Render Dashboard → Your Service → **Environment** tab and add:

#### Critical Variables (Required)
```
APP_KEY=base64:YOUR_KEY_HERE
APP_URL=https://your-unique-service-name.onrender.com
APP_NAME="Pathfinder (Your Deployment)"
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_password
DB_SSLMODE=require
```

⚠️ **IMPORTANT**: 
- Use a **unique service name** in Render (e.g., `pathfinder-dev`, `my-pathfinder`)
- The `APP_URL` should match your service name: `https://[your-service-name].onrender.com`
- Consider using a different database or different credentials to avoid conflicts

#### Email Variables (Required)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Pathfinder (Dev)"  # Different name to identify emails
```

#### Optional but Recommended
```
APP_ENV=production
APP_DEBUG=false
SESSION_DRIVER=database
CACHE_DRIVER=database
LOG_LEVEL=error
```

### ✅ 2. Get Your APP_KEY

**Before deploying**, generate your APP_KEY:

**Option A: From your local project**
```bash
cd backend
php artisan key:generate
# Copy the APP_KEY from your .env file
```

**Option B: After first deploy (via Render Shell)**
1. Deploy first (it will fail, that's okay)
2. Go to Shell in Render
3. Run: `php artisan key:generate --show`
4. Copy the key and add it to Environment Variables
5. Redeploy

### ✅ 3. Verify Dockerfile Location

- ✅ Dockerfile is at the **root** of your repository
- ✅ `docker-entrypoint.sh` is at the **root** of your repository
- ✅ Your repository structure matches what Dockerfile expects

### ✅ 4. Render Dashboard Settings

In the "New Web Service" form:

- **Source Code**: ✅ Connected (httprimo / Pathfinder)
- **Service Type**: ✅ Web Service
- **Name**: ⚠️ **Use a UNIQUE name!** (e.g., `pathfinder-dev`, `pathfinder-staging`, `my-pathfinder`)
  - This creates a unique URL and avoids conflicts
- **Language**: ✅ Docker
- **Branch**: ✅ primo (or your main branch)
- **Region**: ✅ Oregon (US West)
- **Root Directory**: ⬜ **Leave EMPTY** (Dockerfile is at root)
- **Build Command**: ⬜ **Leave EMPTY** (Docker handles it)
- **Start Command**: ⬜ **Leave EMPTY** (Dockerfile CMD handles it)

### ✅ 5. Click "Deploy Web Service"

After clicking deploy:
1. ⏳ Wait 5-10 minutes for build
2. 👀 Watch the logs for errors
3. ✅ Check if service shows "Live" status

### ✅ 6. Post-Deployment Steps

#### Run Database Migrations

1. Go to Render Dashboard → Your Service → **Shell**
2. Run:
   ```bash
   php artisan migrate --force
   ```

#### Verify Service is Working

1. Visit: `https://your-service-name.onrender.com`
2. Check API endpoint: `https://your-service-name.onrender.com/api/health` (if you have one)
3. Check logs for any errors

#### Test Email (Optional)

If you have a test endpoint:
```bash
curl -X POST https://your-service-name.onrender.com/api/test-email \
  -H "Content-Type: application/json" \
  -d '{"email":"your-email@gmail.com"}'
```

## Common First-Time Issues

### ❌ Build Fails
- Check Dockerfile syntax
- Verify all files are committed to your repo
- Check build logs for specific error

### ❌ Service Won't Start
- Verify APP_KEY is set
- Check PORT is being used (should be automatic)
- Check application logs

### ❌ 500 Error After Deploy
- APP_KEY missing or invalid
- Database connection failed
- Check logs: Render Dashboard → Logs

### ❌ Database Connection Error
- Verify Supabase credentials
- Check DB_SSLMODE=require is set
- Verify Supabase allows external connections

## Need Help?

1. Check `RENDER_DEPLOYMENT_GUIDE.md` for detailed instructions
2. Check Render logs for specific errors
3. Verify all environment variables are set correctly

