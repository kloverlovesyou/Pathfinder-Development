# Update .env with Pooler Connection

Based on your Supabase dashboard, update your `backend/.env` file with these values:

## Transaction Pooler Connection (Current):

```env
DB_CONNECTION=pgsql
DB_HOST=aws-1-ap-northeast-2.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.hmevengvfponcwslnyye
DB_PASSWORD=your_supabase_password
DB_SSLMODE=require
```

## Important Notes:

1. **Hostname:** `aws-1-ap-northeast-2.pooler.supabase.com` (not `db.xxxxx.pooler.supabase.com`)
2. **Username:** `postgres.hmevengvfponcwslnyye` (includes the project reference, not just `postgres`)
3. **Port:** `6543` (correct for pooler)
4. **IPv4 Compatible:** ✅ This will work on IPv4 networks!

## Optional: Try Session Pooler Instead

For Laravel applications, **Session pooler** is often better than Transaction pooler:
- Session pooler: Better for persistent connections (like Laravel)
- Transaction pooler: Better for serverless/short-lived connections

To get Session pooler connection string:
1. In the "Method" dropdown, change from "Transaction pooler" to **"Session pooler"** or **"Session mode"**
2. Copy that connection string
3. Use those values instead

But Transaction pooler should also work!

## After Updating .env:

1. Clear config cache:
   ```bash
   cd backend
   php artisan config:clear
   ```

2. Test connection:
   ```bash
   php artisan tinker
   # Then: DB::connection()->getPdo();
   ```

