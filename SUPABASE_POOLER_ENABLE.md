# Enabling Supabase Connection Pooling

## Issue
The pooler hostname `db.hmevengvfponcwslnyye.pooler.supabase.com` is not resolving, which means connection pooling might not be enabled for your project.

## Steps to Enable Connection Pooling

1. **Go to Supabase Dashboard:**
   - Navigate to your project
   - Go to **Settings** → **Database**

2. **Enable Connection Pooling:**
   - Look for **Connection pooling** section
   - If you see "Enable connection pooling" or similar, click it
   - Wait for it to activate (may take a few minutes)

3. **Get the Pooler Connection String:**
   - Once enabled, you should see connection strings for:
     - **Session mode** (recommended for Laravel)
     - **Transaction mode** (for serverless)
   - Copy the **Session mode** connection string

4. **Verify the Hostname:**
   - The pooler hostname should be: `db.[PROJECT-REF].pooler.supabase.com`
   - Port should be: `6543`
   - If the format is different, use exactly what Supabase shows

## Alternative: Use Direct Connection with IPv4 Add-on

If connection pooling is not available or you prefer direct connection:

1. **Purchase IPv4 Add-on** (if available in your plan)
   - Go to Supabase Dashboard → Settings → Database
   - Look for "IPv4 add-on" option
   - This will make the direct connection IPv4 compatible

2. **Then use direct connection:**
   ```env
   DB_HOST=db.hmevengvfponcwslnyye.supabase.co
   DB_PORT=5432
   ```

## Temporary Workaround: Test from Different Network

If you need to test immediately:

1. **Try from mobile hotspot** (different network)
2. **Use a VPN** that supports IPv6
3. **Check if your ISP supports IPv6** - you might need to enable it

## Verify Pooler Status

In Supabase Dashboard:
- Settings → Database → Connection pooling
- Should show "Active" or "Enabled"
- Should display connection strings with `pooler.supabase.com`

## Next Steps

1. Enable connection pooling in Supabase (if not already enabled)
2. Wait a few minutes for it to activate
3. Copy the exact connection string from Supabase
4. Update your `.env` with the exact hostname shown
5. Test the connection again

If pooling is not available in your Supabase plan, you may need to:
- Upgrade your Supabase plan, OR
- Purchase the IPv4 add-on, OR
- Use a different network/VPN that supports IPv6

