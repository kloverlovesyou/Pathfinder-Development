-- Create schedule_attendance table for tracking attendance per training schedule
-- This allows day-by-day attendance tracking for multi-day trainings

CREATE TABLE IF NOT EXISTS "schedule_attendance" (
    "scheduleAttendanceID" SERIAL PRIMARY KEY,
    "registrationID" INTEGER NOT NULL,
    "trainingScheduleID" INTEGER NOT NULL,
    "attendedAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "attendanceKey" VARCHAR(255) NULL,
    
    -- Foreign key to registration table
    CONSTRAINT "fk_schedule_attendance_registration" 
        FOREIGN KEY ("registrationID") 
        REFERENCES "registration"("registrationID") 
        ON DELETE CASCADE,
    
    -- Foreign key to trainingschedule table
    CONSTRAINT "fk_schedule_attendance_schedule" 
        FOREIGN KEY ("trainingScheduleID") 
        REFERENCES "trainingschedule"("trainingScheduleID") 
        ON DELETE CASCADE,
    
    -- Unique constraint: one attendance record per registration per schedule
    CONSTRAINT "unique_registration_schedule" 
        UNIQUE ("registrationID", "trainingScheduleID")
);

-- Create indexes for better query performance
CREATE INDEX IF NOT EXISTS "idx_schedule_attendance_registrationID" 
    ON "schedule_attendance"("registrationID");

CREATE INDEX IF NOT EXISTS "idx_schedule_attendance_trainingScheduleID" 
    ON "schedule_attendance"("trainingScheduleID");

-- Add comment to table
COMMENT ON TABLE "schedule_attendance" IS 'Tracks attendance for each training schedule day. Links registration to specific schedule instances.';

-- Add comments to columns
COMMENT ON COLUMN "schedule_attendance"."scheduleAttendanceID" IS 'Primary key';
COMMENT ON COLUMN "schedule_attendance"."registrationID" IS 'Foreign key to registration table';
COMMENT ON COLUMN "schedule_attendance"."trainingScheduleID" IS 'Foreign key to trainingschedule table';
COMMENT ON COLUMN "schedule_attendance"."attendedAt" IS 'Timestamp when attendance was recorded';
COMMENT ON COLUMN "schedule_attendance"."attendanceKey" IS 'The QR code key that was scanned for attendance';

