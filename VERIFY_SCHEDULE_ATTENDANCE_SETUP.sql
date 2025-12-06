-- Verification Script for schedule_attendance Table
-- Run this to verify your table is set up correctly

-- 1. Check if table exists and has correct structure
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_schema = 'public' 
  AND table_name = 'schedule_attendance'
ORDER BY ordinal_position;

-- 2. Check foreign key constraints
SELECT
    tc.constraint_name,
    tc.table_name,
    kcu.column_name,
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name
FROM information_schema.table_constraints AS tc
JOIN information_schema.key_column_usage AS kcu
    ON tc.constraint_name = kcu.constraint_name
    AND tc.table_schema = kcu.table_schema
JOIN information_schema.constraint_column_usage AS ccu
    ON ccu.constraint_name = tc.constraint_name
    AND ccu.table_schema = tc.table_schema
WHERE tc.constraint_type = 'FOREIGN KEY'
    AND tc.table_name = 'schedule_attendance';

-- 3. Check unique constraint
SELECT
    tc.constraint_name,
    tc.constraint_type,
    kcu.column_name
FROM information_schema.table_constraints AS tc
JOIN information_schema.key_column_usage AS kcu
    ON tc.constraint_name = kcu.constraint_name
    AND tc.table_schema = kcu.table_schema
WHERE tc.table_name = 'schedule_attendance'
    AND tc.constraint_type = 'UNIQUE';

-- 4. Check indexes
SELECT
    indexname,
    indexdef
FROM pg_indexes
WHERE tablename = 'schedule_attendance'
    AND schemaname = 'public';

-- 5. Test query: See attendance records (if any exist)
SELECT 
    sa."scheduleAttendanceID",
    sa."registrationID",
    sa."trainingScheduleID",
    sa."attendedAt",
    sa."attendanceKey",
    r."registrationStatus",
    ts."schedule" as "scheduleDate",
    ts."end_time" as "scheduleEndTime"
FROM "schedule_attendance" sa
LEFT JOIN "registration" r ON sa."registrationID" = r."registrationID"
LEFT JOIN "trainingschedule" ts ON sa."trainingScheduleID" = ts."trainingScheduleID"
ORDER BY sa."attendedAt" DESC
LIMIT 10;

