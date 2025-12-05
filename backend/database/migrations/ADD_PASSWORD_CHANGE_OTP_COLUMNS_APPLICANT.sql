-- SQL Script to Add Password Change OTP Columns for Applicant Table
-- Run these directly on your production database if you can't use migrations
-- This script is compatible with PostgreSQL

-- ============================================
-- For applicant table
-- ============================================
ALTER TABLE applicant 
ADD COLUMN IF NOT EXISTS password_change_otp VARCHAR(6) NULL,
ADD COLUMN IF NOT EXISTS password_change_otp_expires_at TIMESTAMP NULL;

-- ============================================
-- Verify the columns were added
-- ============================================
-- Check applicant table structure
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'applicant' 
AND column_name IN ('password_change_otp', 'password_change_otp_expires_at');

