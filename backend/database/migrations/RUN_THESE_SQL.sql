-- SQL Script to Add Email Verification Columns
-- Run these directly on your production database if you can't use migrations

-- ============================================
-- For applicant table
-- ============================================
ALTER TABLE `applicant` 
ADD COLUMN `email_verification_token` VARCHAR(64) NULL AFTER `emailAddress`,
ADD COLUMN `email_verified_at` TIMESTAMP NULL AFTER `email_verification_token`;

-- ============================================
-- For organization table
-- ============================================
ALTER TABLE `organization` 
ADD COLUMN `email_verification_token` VARCHAR(64) NULL AFTER `emailAddress`,
ADD COLUMN `email_verified_at` TIMESTAMP NULL AFTER `email_verification_token`;

-- ============================================
-- Verify the columns were added
-- ============================================
-- Check applicant table structure
DESCRIBE `applicant`;

-- Check organization table structure
DESCRIBE `organization`;

