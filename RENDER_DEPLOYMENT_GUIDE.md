# Render Deployment Guide for Pathfinder

This guide will help you deploy your Pathfinder application to Render using Docker.

## Prerequisites

✅ You have:
- Created your own GitHub repository
- Pushed your code to the repository
- Created a Render account
- Connected your repository to Render

## Step 1: Configure Render Web Service

Based on the Render dashboard you're viewing, here's what to configure:

### Basic Settings

1. **Source Code**: Already connected (httprimo / Pathfinder)
2. **Service Type**: Web Service ✅
3. **Name**: ⚠️ **Use a different name!** (e.g., `pathfinder-dev`, `pathfinder-staging`, `pathfinder-personal`, `my-pathfinder`)
   - This ensures a unique URL and avoids conflicts with existing deployment
4. **Language**: Docker ✅
5. **Branch**: primo (or your main branch)
6. **Region**: Oregon (US West) ✅

### Important Settings to Configure

#### Root Directory (Optional)
- **Leave empty** if your Dockerfile is at the root
- OR set to the directory containing your Dockerfile

#### Build Command
- **Leave empty** - Docker will handle the build automatically

#### Start Command
- **Leave empty** - Docker will use the CMD from Dockerfile

#### Environment Variables

Click on **"Environment"** tab and add these variables:

### Required Environment Variables

```env
# Application
APP_NAME="Pathfinder (Your Deployment)"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-unique-service-name.onrender.com

# ⚠️ IMPORTANT: Use a unique service name to avoid conflicts!
# The URL will be: https://[your-service-name].onrender.com
# Example: https://pathfinder-dev.onrender.com or https://my-pathfinder.onrender.com

# Database (Supabase PostgreSQL)
# Option 1: Use same database (if you want to share data)
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
DB_SSLMODE=require

# Option 2: Use different database (recommended to avoid conflicts)
# Create a new Supabase project or use a different database
# DB_DATABASE=pathfinder_dev  # Different database name
# DB_PASSWORD=your_different_password

# OR use connection string (recommended)
DATABASE_URL=postgresql://postgres:[YOUR-PASSWORD]@db.xxxxxxxxxxxxx.supabase.co:5432/postgres?sslmode=require

# Email Configuration (Gmail)
# ⚠️ Consider using different email to distinguish from production
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Pathfinder (Dev)"  # Different name to identify emails

# OR use Brevo (Alternative)
# BREVO_API_KEY=your-brevo-api-key
# MAIL_FROM_ADDRESS=your-verified-email@example.com

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### How to Get Your APP_KEY

If you don't have an APP_KEY yet, you can generate one:

1. **Option 1**: Run locally:
   ```bash
   cd backend
   php artisan key:generate
   ```
   Copy the key from your local `.env` file

2. **Option 2**: After first deployment, use Render's Shell:
   ```bash
   php artisan key:generate --show
   ```
   Then add it to environment variables

## Step 2: Update Dockerfile for Render

Render uses the `PORT` environment variable. Update your root `Dockerfile` to use it:

The current Dockerfile exposes port 80, but Render will provide a `PORT` environment variable. We need to update it.

## Step 3: Database Setup

### Run Migrations

After your first deployment, you need to run database migrations:

1. Go to Render Dashboard → Your Service → **Shell**
2. Run:
   ```bash
   php artisan migrate --force
   ```

Or add this to your build command (not recommended for production):
```bash
composer install --optimize-autoloader --no-dev && php artisan migrate --force
```

### Create Database Tables

Make sure your Supabase database is set up and accessible from Render's IP addresses.

## Step 4: Deploy

1. Click **"Deploy Web Service"** button
2. Wait for the build to complete (usually 5-10 minutes)
3. Check the logs for any errors

## Step 5: Post-Deployment Checklist

After deployment:

- [ ] Check if the service is running (green status)
- [ ] Visit your service URL: `https://your-service-name.onrender.com`
- [ ] Run database migrations (via Shell)
- [ ] Test API endpoints
- [ ] Verify email configuration
- [ ] Check application logs for errors

## Step 6: Frontend Deployment (Optional)

If you want to deploy the frontend separately:

1. Create a new **Static Site** service in Render
2. Connect your repository
3. Set:
   - **Build Command**: `cd frontend && npm install && npm run build`
   - **Publish Directory**: `frontend/dist`
4. Update your frontend API URL to point to your backend service

## Troubleshooting

### Common Issues

1. **Build Fails**
   - Check Dockerfile syntax
   - Verify all dependencies are in composer.json
   - Check build logs for specific errors

2. **Service Won't Start**
   - Verify PORT environment variable is being used
   - Check Apache configuration
   - Check application logs

3. **Database Connection Errors**
   - Verify Supabase credentials
   - Check if Supabase allows connections from Render IPs
   - Verify SSL mode is set to `require`

4. **500 Internal Server Error**
   - Check if APP_KEY is set
   - Verify storage permissions
   - Check application logs

5. **Email Not Working**
   - Verify Gmail App Password (not regular password)
   - Check MAIL_PORT is 587 (not 1025)
   - Remove spaces from MAIL_PASSWORD
   - Consider using Brevo API instead

### Viewing Logs

1. Go to Render Dashboard → Your Service
2. Click on **"Logs"** tab
3. Check for errors and warnings

### Getting Shell Access

1. Go to Render Dashboard → Your Service
2. Click on **"Shell"** tab
3. Run commands like:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan migrate
   ```

## Environment Variables Reference

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_KEY` | Laravel encryption key | `base64:...` |
| `APP_URL` | Your service URL | `https://pathfinder.onrender.com` |
| `DB_HOST` | Supabase database host | `db.xxxxx.supabase.co` |
| `DB_PASSWORD` | Supabase database password | `your-password` |
| `MAIL_USERNAME` | Gmail address | `your-email@gmail.com` |
| `MAIL_PASSWORD` | Gmail App Password | `xxxx xxxx xxxx xxxx` |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_DEBUG` | Enable debug mode | `false` |
| `LOG_LEVEL` | Logging level | `error` |
| `SESSION_DRIVER` | Session storage | `database` |

## Next Steps

1. ✅ Configure all environment variables
2. ✅ Deploy the service
3. ✅ Run database migrations
4. ✅ Test the API
5. ✅ Deploy frontend (if needed)
6. ✅ Set up custom domain (optional)

## Support

If you encounter issues:
1. Check Render documentation: https://render.com/docs
2. Check application logs in Render dashboard
3. Verify all environment variables are set correctly
4. Ensure database is accessible from Render

