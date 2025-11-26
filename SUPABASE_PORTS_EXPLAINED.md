# Supabase Database Ports Explained

## You Cannot Change the Port - It's Fixed by Connection Type

Supabase uses **fixed ports** based on the connection type you choose. You don't change the port in settings - you select the connection type, and the port is automatically determined.

## Ports by Connection Type:

### 1. **Direct Connection** (IPv6 only)
- **Port:** `5432`
- **Host:** `db.[PROJECT-REF].supabase.co`
- **Use when:** You have IPv6 support or purchased IPv4 add-on
- **Example:** `db.hmevengvfponcwslnyye.supabase.co:5432`

### 2. **Connection Pooler - Session Mode** (IPv4 compatible)
- **Port:** `6543`
- **Host:** `db.[PROJECT-REF].pooler.supabase.com`
- **Use when:** You need IPv4 compatibility (most common)
- **Example:** `db.hmevengvfponcwslnyye.pooler.supabase.com:6543`

### 3. **Connection Pooler - Transaction Mode** (IPv4 compatible)
- **Port:** `6543`
- **Host:** `db.[PROJECT-REF].pooler.supabase.com`
- **Use when:** Serverless functions or short-lived connections
- **Example:** `db.hmevengvfponcwslnyye.pooler.supabase.com:6543`

## How to "Change" the Port:

You don't change the port - you **change the connection type**:

1. **Go to Supabase Dashboard:**
   - Settings → Database
   - Connection pooling section

2. **Select the connection type:**
   - **Direct connection** → Port 5432
   - **Session pooler** → Port 6543
   - **Transaction pooler** → Port 6543

3. **Copy the connection string** - it will have the correct port already

## For Your Current Setup:

Since you're using port `6543`, you should be using the **Connection Pooler**.

Make sure your `.env` matches:
```env
DB_HOST=db.hmevengvfponcwslnyye.pooler.supabase.com
DB_PORT=6543
```

**Important:** The hostname must end with `.pooler.supabase.com` (not just `.supabase.co`) when using port 6543.

## Troubleshooting:

If port 6543 isn't working:
1. **Verify connection pooling is enabled** in Supabase Dashboard
2. **Check the exact hostname** - it must be `pooler.supabase.com`
3. **Use the connection string** directly from Supabase (it has the correct format)

## Quick Check:

In Supabase Dashboard → Settings → Database:
- Look for "Connection pooling" section
- Copy the **Session mode** connection string
- It should show: `db.xxxxx.pooler.supabase.com:6543`

That's the exact format you need in your `.env` file!

