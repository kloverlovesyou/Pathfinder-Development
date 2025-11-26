# Supabase Database Setup Guide

This guide will help you switch from Railway (MySQL) to Supabase (PostgreSQL).

## Prerequisites

1. **Install PostgreSQL PHP Extension** (if not already installed):
   - On Windows: Enable `php_pdo_pgsql` and `php_pgsql` in your `php.ini`
   - On Linux: `sudo apt-get install php-pgsql` or `sudo yum install php-pgsql`
   - On macOS: `brew install php-pgsql`

2. **Get Supabase Connection Details**:
   - Go to your Supabase project dashboard
   - Navigate to Settings → Database
   - Copy the connection string or individual details

## Step 1: Update .env File

Update your `backend/.env` file with Supabase credentials:

```env
# Change from mysql to pgsql
DB_CONNECTION=pgsql

# Supabase Database Connection Details
# You can find these in Supabase Dashboard → Settings → Database
DB_HOST=db.xxxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password

# SSL Mode (required for Supabase)
DB_SSLMODE=require
```

**OR** use the connection string directly:

```env
DB_CONNECTION=pgsql
DATABASE_URL=postgresql://postgres:[YOUR-PASSWORD]@db.xxxxxxxxxxxxx.supabase.co:5432/postgres?sslmode=require
```

## Step 2: Verify PostgreSQL Extension

Check if PostgreSQL extension is installed:

```bash
cd backend
php -m | grep pgsql
```

You should see:
- `pdo_pgsql`
- `pgsql`

If not installed, install it based on your system (see Prerequisites).

## Step 3: Test Connection

Test the database connection:

```bash
cd backend
php artisan tinker
```

Then run:
```php
DB::connection()->getPdo();
```

If successful, you'll see the PDO object. If there's an error, check your credentials.

## Step 4: Important Notes

### MySQL vs PostgreSQL Differences

1. **Auto Increment**: 
   - MySQL: `AUTO_INCREMENT`
   - PostgreSQL: `SERIAL` or `BIGSERIAL`
   - The migration we ran earlier used MySQL syntax. You may need to update it for PostgreSQL.

2. **Date/Time Functions**:
   - MySQL: `DATE()`, `TIME()` - These work in PostgreSQL too
   - PostgreSQL: `DATE()`, `TIME()` - Same syntax

3. **Table/Column Names**:
   - MySQL: Case-insensitive by default
   - PostgreSQL: Case-sensitive (uses lowercase unless quoted)
   - Your current code should work, but watch for any issues

4. **Backticks**:
   - MySQL: Uses backticks for identifiers
   - PostgreSQL: Uses double quotes
   - Laravel's query builder handles this automatically

## Step 5: Update Auto-Increment Migration (if needed)

If you haven't run the auto-increment migration yet, you may need to update it for PostgreSQL:

```php
// For PostgreSQL, use SERIAL instead
DB::statement('ALTER TABLE trainingschedule ALTER COLUMN "trainingScheduleID" TYPE SERIAL');
```

Or use Laravel's schema builder:
```php
Schema::table('trainingschedule', function (Blueprint $table) {
    $table->id('trainingScheduleID')->change(); // This won't work directly
    // Better to use raw SQL for PostgreSQL
});
```

## Step 6: Run Migrations

After switching, you may need to run migrations:

```bash
cd backend
php artisan migrate
```

**Note**: If you already have data in Railway, you'll need to export it and import to Supabase, or start fresh.

## Troubleshooting

### Error: "could not find driver"
- Install PostgreSQL PHP extension (see Prerequisites)

### Error: "SSL connection required"
- Make sure `DB_SSLMODE=require` is set in `.env`

### Error: "password authentication failed"
- Double-check your Supabase password
- Make sure you're using the correct database name (usually `postgres`)

### Error: "relation does not exist"
- PostgreSQL is case-sensitive with table names
- Make sure table names match exactly (usually lowercase in PostgreSQL)

## Getting Supabase Connection String

1. Go to Supabase Dashboard
2. Select your project
3. Go to **Settings** → **Database**
4. Find **Connection string** section
5. Copy the **URI** or **Connection pooling** string
6. Replace `[YOUR-PASSWORD]` with your actual database password

Example format:
```
postgresql://postgres:[YOUR-PASSWORD]@db.xxxxx.supabase.co:5432/postgres
```

## Next Steps

After switching:
1. Test creating a training with multiple schedules
2. Verify all CRUD operations work
3. Check if any MySQL-specific queries need updating
4. Monitor for any case-sensitivity issues with table/column names

