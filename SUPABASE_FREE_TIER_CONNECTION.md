# Supabase Free Tier Connection Guide

## Connection Pooling is Available on Free Tier

Your Supabase free tier **does support connection pooling** (Shared Pooler). The connection string format might be slightly different.

## Get the Exact Connection String

1. **In Supabase Dashboard:**
   - Go to **Settings** → **Database**
   - Click on **Connection string** tab (or look for connection details)
   - Find **Connection pooling** section
   - Select **Session mode**
   - Copy the **exact** connection string shown

2. **The connection string should look like:**
   ```
   postgresql://postgres:[YOUR-PASSWORD]@db.xxxxx.pooler.supabase.com:6543/postgres
   ```
   
   OR it might be:
   ```
   postgresql://postgres:[YOUR-PASSWORD]@db.xxxxx.supabase.co:6543/postgres
   ```

## Important Notes for Free Tier:

- **Shared Pooler** means you share resources with other free tier users
- **Pool Size:** 15 connections (as shown in your settings)
- **Max Client Connections:** 200
- The hostname format might vary - use **exactly** what Supabase shows

## If Pooler Hostname Doesn't Resolve:

### Option 1: Try Different DNS Server

Your current DNS might not resolve the pooler hostname. Try using Google's DNS:

1. **Temporarily change DNS** (for testing):
   - Windows: Network Settings → Change adapter options → Your connection → Properties → IPv4 → Use: `8.8.8.8` and `8.8.4.4`

2. **Or use nslookup with Google DNS:**
   ```bash
   nslookup db.hmevengvfponcwslnyye.pooler.supabase.com 8.8.8.8
   ```

### Option 2: Use Direct Connection String Format

Instead of individual DB_* variables, try using DATABASE_URL:

```env
DB_CONNECTION=pgsql
DATABASE_URL=postgresql://postgres:[YOUR-PASSWORD]@db.hmevengvfponcwslnyye.pooler.supabase.com:6543/postgres?sslmode=require
```

### Option 3: Check Supabase Dashboard Connection String

The exact format might be different. In Supabase Dashboard:
- Settings → Database
- Look for "Connection string" section
- Copy the **Session mode** connection string
- It might show a different hostname format

## Quick Test:

1. **Copy the exact connection string** from Supabase Dashboard
2. **Update your .env** with that exact format
3. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```
4. **Test connection:**
   ```bash
   php artisan tinker
   # Then: DB::connection()->getPdo();
   ```

## If Still Not Working:

The free tier shared pooler might have DNS propagation delays. Try:
- Wait a few minutes and try again
- Check if the hostname resolves: `nslookup db.hmevengvfponcwslnyye.pooler.supabase.com`
- Try from a different network (mobile hotspot)
- Contact Supabase support if the pooler hostname doesn't exist

## Alternative: Direct Connection (if pooler unavailable)

If the pooler doesn't work, you can try direct connection, but you'll need IPv6 support:

```env
DB_HOST=db.hmevengvfponcwslnyye.supabase.co
DB_PORT=5432
```

But this requires IPv6, which your network might not support.

