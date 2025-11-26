# How to Get the Pooler Connection String

The Database Settings page shows that connection pooling is active, but you need to get the actual connection string from a different place.

## Steps to Get Pooler Connection String:

1. **Go back to the "Connect to your project" modal:**
   - This is usually accessible from:
     - Project Settings → Database → Connection string
     - Or from the main dashboard

2. **In the "Connect to your project" modal:**
   - Look for the **"Method"** dropdown (currently shows "Direct connection")
   - **Change it to "Connection pooling"** or **"Session pooler"**
   - This will show you the pooler connection string

3. **The connection string should show:**
   - Hostname with `.pooler.supabase.com`
   - Port `6543`
   - Full connection string like: `postgresql://postgres:[PASSWORD]@db.xxxxx.pooler.supabase.com:6543/postgres`

## Alternative: Check Connection String Tab

If you're in the "Connect to your project" modal:
1. Make sure you're on the **"Connection String"** tab
2. Change the **"Method"** dropdown from "Direct connection" to **"Connection pooling"** or **"Session mode"**
3. The connection string below will update to show the pooler details

## What You Should See:

When you select "Connection pooling" or "Session pooler" as the method, you should see:
- A different hostname (ending with `.pooler.supabase.com`)
- Port `6543` (instead of `5432`)
- A note that it's IPv4 compatible

## Quick Navigation:

From Database Settings page:
- Look for a link/button that says "Connection string" or "Connect to your project"
- Or go to: Project Settings → Database → Connection string tab
- Change the Method dropdown to see pooler connection

