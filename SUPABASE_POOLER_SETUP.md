# Using Supabase Session Pooler (IPv4 Compatible)

Your Supabase database is IPv6-only, but your network/PHP is using IPv4. You need to use the **Session Pooler** connection instead.

## Steps to Get Pooler Connection:

1. **In Supabase Dashboard:**
   - Go to **Settings** → **Database**
   - Find **Connection pooling** section
   - Select **Session mode** (not Transaction mode)
   - Copy the connection string

2. **The pooler connection will look like:**
   ```
   postgresql://postgres:[YOUR-PASSWORD]@db.hmevengvfponcwslnyye.pooler.supabase.com:6543/postgres
   ```
   
   **Note the differences:**
   - Host: `pooler.supabase.com` (not just `supabase.co`)
   - Port: `6543` (not `5432`)

## Update Your .env File:

Replace your current database configuration with:

```env
DB_CONNECTION=pgsql
DB_HOST=db.hmevengvfponcwslnyye.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
DB_SSLMODE=require
```

**OR** use the connection string format:

```env
DB_CONNECTION=pgsql
DATABASE_URL=postgresql://postgres:[YOUR-PASSWORD]@db.hmevengvfponcwslnyye.pooler.supabase.com:6543/postgres?sslmode=require
```

## Important Notes:

- **Port 6543** is for Session Pooler (not 5432)
- **Session Pooler** is ideal for most applications
- **Transaction Pooler** (port 6543) is for serverless functions
- Both poolers are **IPv4 compatible

## After Updating:

1. Clear Laravel config cache:
   ```bash
   cd backend
   php artisan config:clear
   ```

2. Test the connection:
   ```bash
   php artisan tinker
   # Then: DB::connection()->getPdo();
   ```

## Alternative: IPv4 Add-on

If you prefer direct connection, you can purchase the IPv4 add-on from Supabase, but the Session Pooler is free and works perfectly for your use case.

