# Environment Variables - Avoiding Conflicts

This guide helps you set up different environment variables to avoid conflicts with your existing deployment.

## 🎯 Key Differences to Set

### 1. Service Name & URL

**In Render Dashboard:**
- **Service Name**: Use a unique name (e.g., `pathfinder-dev`, `pathfinder-staging`, `my-pathfinder`)
- **URL**: Will automatically be `https://[your-service-name].onrender.com`

**Environment Variable:**
```env
APP_URL=https://your-unique-service-name.onrender.com
APP_NAME="Pathfinder (Your Deployment)"
```

**Examples:**
- Production: `https://pathfinder.onrender.com`
- Your deployment: `https://pathfinder-dev.onrender.com` or `https://my-pathfinder.onrender.com`

### 2. Database Configuration

You have two options:

#### Option A: Use Same Database (Share Data)
```env
# Use the same Supabase database
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=same_password
DB_SSLMODE=require
```

**Pros:**
- Share data between deployments
- No need for separate database

**Cons:**
- Changes affect both deployments
- Potential conflicts if both write to same tables

#### Option B: Use Different Database (Recommended)
```env
# Use a different Supabase project or database
DB_CONNECTION=pgsql
DB_HOST=db.yyyyy.supabase.co  # Different Supabase project
DB_PORT=5432
DB_DATABASE=pathfinder_dev    # Different database name
DB_USERNAME=postgres
DB_PASSWORD=different_password
DB_SSLMODE=require
```

**Pros:**
- Complete isolation
- No conflicts
- Safe to test changes

**Cons:**
- Need separate Supabase project/database
- Data not shared

### 3. Email Configuration

#### Option A: Same Email Account
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=same-email@gmail.com
MAIL_PASSWORD=same-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=same-email@gmail.com
MAIL_FROM_NAME="Pathfinder (Dev)"  # Different name to identify
```

#### Option B: Different Email Account (Recommended)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-personal-email@gmail.com
MAIL_PASSWORD=your-personal-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-personal-email@gmail.com
MAIL_FROM_NAME="Pathfinder (Your Deployment)"
```

**Why different email?**
- Easy to identify which deployment sent the email
- No confusion in email logs
- Can use different email service if needed

### 4. Application Key

**MUST be different!**

```env
APP_KEY=base64:YOUR_UNIQUE_KEY_HERE
```

Generate a new key:
```bash
cd backend
php artisan key:generate --show
```

**Why different?**
- Each deployment needs its own encryption key
- Security best practice
- Prevents conflicts

### 5. Session & Cache

If using database sessions, consider:

```env
SESSION_DRIVER=database
CACHE_DRIVER=database
```

With same database: Sessions might conflict (users logged into one deployment might affect the other)

With different database: No conflicts

## 📋 Complete Environment Variables Template

### For Development/Personal Deployment

```env
# Application Identity
APP_NAME="Pathfinder (Dev)"
APP_ENV=production
APP_KEY=base64:YOUR_UNIQUE_KEY_HERE
APP_DEBUG=false
APP_URL=https://pathfinder-dev.onrender.com

# Database (Use different if possible)
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_password
DB_SSLMODE=require

# Email (Use different to identify)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Pathfinder (Dev)"

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

## 🔍 Quick Comparison Table

| Variable | Production | Your Deployment | Notes |
|----------|-----------|-----------------|-------|
| `APP_URL` | `https://pathfinder.onrender.com` | `https://pathfinder-dev.onrender.com` | Must be different |
| `APP_NAME` | `Pathfinder` | `Pathfinder (Dev)` | Different for identification |
| `APP_KEY` | `base64:xxx...` | `base64:yyy...` | Must be different |
| `DB_HOST` | `db.xxxxx.supabase.co` | `db.xxxxx.supabase.co` or different | Can be same or different |
| `DB_DATABASE` | `postgres` | `postgres` or `pathfinder_dev` | Can be same or different |
| `MAIL_FROM_ADDRESS` | `prod@example.com` | `dev@example.com` | Different to identify emails |
| `MAIL_FROM_NAME` | `Pathfinder` | `Pathfinder (Dev)` | Different for identification |

## ✅ Checklist

Before deploying, ensure:

- [ ] Service name is unique in Render
- [ ] `APP_URL` matches your service name
- [ ] `APP_KEY` is generated and unique
- [ ] `APP_NAME` is different for identification
- [ ] Database configuration is set (same or different)
- [ ] Email configuration is set (same or different)
- [ ] `MAIL_FROM_NAME` is different to identify emails

## 🎯 Recommended Setup

For maximum isolation and no conflicts:

1. ✅ **Unique service name**: `pathfinder-dev` or `my-pathfinder`
2. ✅ **Different database**: Create new Supabase project
3. ✅ **Different email**: Use your personal email
4. ✅ **Different APP_KEY**: Generate new one
5. ✅ **Different APP_NAME**: Add identifier like "(Dev)"

This ensures complete separation from production!

