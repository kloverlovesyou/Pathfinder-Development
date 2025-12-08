-- SQL Script to Add Admin Login OTP Columns
-- Run these directly on your production database if you can't use migrations
-- This script is compatible with PostgreSQL

-- ============================================
-- For admin table
-- ============================================
ALTER TABLE admin 
ADD COLUMN IF NOT EXISTS login_otp VARCHAR(6) NULL,
ADD COLUMN IF NOT EXISTS login_otp_expires_at TIMESTAMP NULL;

-- ============================================
-- Verify the columns were added
-- ============================================
-- Check admin table structure
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'admin' 
AND column_name IN ('login_otp', 'login_otp_expires_at');

