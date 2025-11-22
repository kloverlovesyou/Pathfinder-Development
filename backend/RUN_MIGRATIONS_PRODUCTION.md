# Running Migrations on Production Database

## Option 1: Run Migrations via Railway (Recommended)

### If Railway has a console/terminal:

1. Go to your Railway project dashboard
2. Open the service/container that runs your Laravel backend
3. Open the terminal/console
4. Run:
   ```bash
   php artisan migrate --force
   ```
   The `--force` flag is required in production.

### If Railway runs migrations automatically:

Check your Railway configuration. Some setups run migrations automatically on deploy. If not, you may need to add it to your build/deploy script.

## Option 2: Run Migrations Locally (Pointing to Production DB)

1. Make sure your local `.env` points to the production database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=your-production-db-host
   DB_PORT=3306
   DB_DATABASE=your-production-db-name
   DB_USERNAME=your-production-db-user
   DB_PASSWORD=your-production-db-password
   ```

2. Run migrations:
   ```bash
   php artisan migrate --force
   ```

**⚠️ WARNING:** Be very careful! Make sure you're connected to the right database!

## Option 3: Run SQL Directly (If migrations don't work)

If you can't run migrations via artisan, you can run these SQL commands directly on your database:

### For `applicant` table:

```sql
ALTER TABLE `applicant` 
ADD COLUMN `email_verification_token` VARCHAR(64) NULL AFTER `emailAddress`,
ADD COLUMN `email_verified_at` TIMESTAMP NULL AFTER `email_verification_token`;
```

### For `organization` table:

```sql
ALTER TABLE `organization` 
ADD COLUMN `email_verification_token` VARCHAR(64) NULL AFTER `emailAddress`,
ADD COLUMN `email_verified_at` TIMESTAMP NULL AFTER `email_verification_token`;
```

## Verify the Columns Were Added

After running migrations or SQL, verify the columns exist:

```sql
-- Check applicant table
DESCRIBE `applicant`;

-- Check organization table
DESCRIBE `organization`;
```

You should see:
- `email_verification_token` (VARCHAR(64), nullable)
- `email_verified_at` (TIMESTAMP, nullable)

## After Running Migrations

1. Clear config cache (if on Railway):
   ```bash
   php artisan config:clear
   ```

2. Test registration - emails should now work!

