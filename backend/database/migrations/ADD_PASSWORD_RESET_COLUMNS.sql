-- SQL Script to Add Password Reset Columns
-- Run these directly on your production database if you can't use migrations
-- This script is compatible with PostgreSQL

-- ============================================
-- For applicant table
-- ============================================
ALTER TABLE applicant 
ADD COLUMN IF NOT EXISTS password_reset_token VARCHAR(64) NULL,
ADD COLUMN IF NOT EXISTS password_reset_expires_at TIMESTAMP NULL;

-- ============================================
-- For organization table
-- ============================================
ALTER TABLE organization 
ADD COLUMN IF NOT EXISTS password_reset_token VARCHAR(64) NULL,
ADD COLUMN IF NOT EXISTS password_reset_expires_at TIMESTAMP NULL;

-- ============================================
-- Verify the columns were added
-- ============================================
-- Check applicant table structure
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'applicant' 
AND column_name IN ('password_reset_token', 'password_reset_expires_at');

-- Check organization table structure
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'organization' 
AND column_name IN ('password_reset_token', 'password_reset_expires_at');

