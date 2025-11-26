# Supabase Connection Troubleshooting

## Current Issue: DNS Resolution Error

Error: `could not translate host name "db.hmevengvfponcwslnyye.supabase.co" to address`

## Solutions to Try:

### Option 1: Use Connection Pooling URL (Recommended)

Supabase provides connection pooling URLs that might work better. In your Supabase dashboard:

1. Go to **Settings** → **Database**
2. Look for **Connection pooling** section
3. Use the **Session mode** or **Transaction mode** connection string
4. It usually looks like: `db.xxxxx.pooler.supabase.com` instead of `db.xxxxx.supabase.co`

Update your `.env`:
```env
DB_HOST=db.hmevengvfponcwslnyye.pooler.supabase.com
DB_PORT=6543  # Pooler uses port 6543, not 5432
```

### Option 2: Use Direct Connection String

Instead of individual DB_* variables, use DATABASE_URL:

```env
DB_CONNECTION=pgsql
DATABASE_URL=postgresql://postgres:[YOUR-PASSWORD]@db.hmevengvfponcwslnyye.supabase.co:5432/postgres?sslmode=require
```

### Option 3: Check Network/Firewall

The DNS resolves to IPv6. Try:

1. **Check if you can reach Supabase from your network**
   - Some networks block database connections
   - Try from a different network (mobile hotspot)

2. **Use IPv4 explicitly** (if Supabase provides it)
   - Check Supabase dashboard for direct IP

3. **Check Windows Firewall**
   - Make sure port 5432 (or 6543 for pooler) is not blocked

### Option 4: Verify Supabase Project Status

1. Check if your Supabase project is active
2. Verify the database password is correct
3. Check if there are any IP restrictions in Supabase settings

### Option 5: Test Connection from Command Line

Install PostgreSQL client and test directly:

```bash
# If you have psql installed
psql "postgresql://postgres:[PASSWORD]@db.hmevengvfponcwslnyye.supabase.co:5432/postgres?sslmode=require"
```

### Option 6: Use Supabase's Direct Connection (Non-Pooled)

In Supabase dashboard:
- Settings → Database → Connection string
- Use **Direct connection** (not pooled)
- Copy the full connection string

## Quick Test Commands

```bash
# Test DNS resolution
nslookup db.hmevengvfponcwslnyye.supabase.co

# Test if port is reachable (if you have telnet/nc)
telnet db.hmevengvfponcwslnyye.supabase.co 5432

# Test from PHP
cd backend
php artisan tinker
# Then: DB::connection()->getPdo();
```

## Common Issues

1. **IPv6 vs IPv4**: PHP might not support IPv6 properly
2. **SSL/TLS**: Make sure `sslmode=require` is set
3. **Password encoding**: Special characters in password might need URL encoding
4. **Connection timeout**: Add timeout settings if needed

## Next Steps

1. Try the connection pooling URL first (port 6543)
2. If that doesn't work, verify your Supabase project is active
3. Check if you can access Supabase dashboard (confirms project is up)
4. Try connecting from a different network to rule out firewall issues

