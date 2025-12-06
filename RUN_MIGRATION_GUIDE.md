# How to Run the Schedule Attendance Table Migration

## Option 1: Run SQL Script Directly (Recommended)

1. **Copy the SQL script:**
   - Open `CREATE_SCHEDULE_ATTENDANCE_TABLE.sql`
   - Copy all the SQL code

2. **Execute in your database:**
   - **If using Supabase:**
     - Go to SQL Editor
     - Paste the SQL script
     - Click "Run" or press Ctrl+Enter
   
   - **If using psql command line:**
     ```bash
     psql -h your-host -U your-user -d your-database -f CREATE_SCHEDULE_ATTENDANCE_TABLE.sql
     ```
   
   - **If using pgAdmin:**
     - Connect to your database
     - Open Query Tool
     - Paste the SQL script
     - Execute

## Option 2: Mark Migration as Run (After Creating Table Manually)

If you ran the SQL script directly, you need to tell Laravel that the migration has been run:

```bash
php artisan migrate:status
```

Then manually insert a record into the `migrations` table:

```sql
INSERT INTO "migrations" ("migration", "batch")
VALUES ('2025_12_01_100000_create_schedule_attendance_table', 
        (SELECT COALESCE(MAX("batch"), 0) + 1 FROM "migrations"));
```

## Verification

After running the SQL script, verify the table was created:

```sql
-- Check if table exists
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
  AND table_name = 'schedule_attendance';

-- Check table structure
\d "schedule_attendance"
```

## Troubleshooting

If you get errors:
- **"relation already exists"**: The table already exists, you can skip this step
- **"foreign key constraint"**: Make sure `registration` and `trainingschedule` tables exist
- **"permission denied"**: You need proper database permissions

