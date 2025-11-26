# How to Get Supabase Pooler Connection String

## Steps:

1. **In the Supabase Dashboard** (where you see "Connect to your project"):
   - You should see a button: **"Pooler settings"** (next to "IPv4 add-on")
   - **Click on "Pooler settings"**

2. **This will show you the Connection Pooler options:**
   - You'll see **Session mode** and **Transaction mode**
   - Select **Session mode** (recommended for Laravel)

3. **Copy the connection string:**
   - It should look like: `postgresql://postgres:[YOUR-PASSWORD]@db.xxxxx.pooler.supabase.com:6543/postgres`
   - Or it might show individual parameters

4. **Update your `.env` file** with the pooler connection details:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=db.hmevengvfponcwslnyye.pooler.supabase.com
   DB_PORT=6543
   DB_DATABASE=postgres
   DB_USERNAME=postgres
   DB_PASSWORD=your_password
   DB_SSLMODE=require
   ```

## Alternative: Use Connection String Directly

If Supabase provides a full connection string, you can use it directly:

```env
DB_CONNECTION=pgsql
DATABASE_URL=postgresql://postgres:[YOUR-PASSWORD]@db.hmevengvfponcwslnyye.pooler.supabase.com:6543/postgres?sslmode=require
```

## After Getting the Pooler Connection:

1. Update your `.env` file
2. Clear Laravel config: `php artisan config:clear`
3. Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

The pooler connection will work on IPv4 networks!

