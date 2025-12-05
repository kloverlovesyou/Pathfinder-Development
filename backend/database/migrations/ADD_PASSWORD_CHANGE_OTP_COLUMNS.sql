-- SQL Script to Add Password Change OTP Columns
-- Run these directly on your production database if you can't use migrations
-- This script is compatible with PostgreSQL

-- ============================================
-- For organization table
-- ============================================
ALTER TABLE organization 
ADD COLUMN IF NOT EXISTS password_change_otp VARCHAR(6) NULL,
ADD COLUMN IF NOT EXISTS password_change_otp_expires_at TIMESTAMP NULL;

-- ============================================
-- Verify the columns were added
-- ============================================
-- Check organization table structure
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'organization' 
AND column_name IN ('password_change_otp', 'password_change_otp_expires_at');

