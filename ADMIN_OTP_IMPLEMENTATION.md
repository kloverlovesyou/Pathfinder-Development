# Admin OTP Verification Implementation

## Overview
OTP (One-Time Password) verification has been successfully added to the admin login process. This adds an extra layer of security by requiring admins to verify their identity via email before completing login.

## Changes Made

### Backend Changes

1. **Database Migration** (`backend/database/migrations/ADD_ADMIN_LOGIN_OTP_COLUMNS.sql`)
   - Added `login_otp` (VARCHAR(6)) column to store the OTP code
   - Added `login_otp_expires_at` (TIMESTAMP) column to track OTP expiration

2. **Admin Model** (`backend/app/Models/Admin.php`)
   - Added `login_otp` and `login_otp_expires_at` to the `$fillable` array

3. **BrevoEmailService** (`backend/app/Services/BrevoEmailService.php`)
   - Added `sendLoginOTP()` method to send OTP emails to admins

4. **AdminController** (`backend/app/Http/Controllers/AdminController.php`)
   - Modified `login()` method to:
     - Verify email and password
     - Generate 6-digit OTP
     - Store OTP with 10-minute expiration
     - Send OTP via email
     - Return `otp_required: true` response
   - Added `verifyOTP()` method to:
     - Verify email, password, and OTP
     - Check OTP expiration
     - Clear OTP after successful verification
     - Generate authentication token

5. **Routes** (`backend/routes/api.php`)
   - Added `/admin/verify-otp` route for OTP verification

### Frontend Changes

1. **AdminLogin Component** (`frontend/src/components/Admin/AdminLogin.vue`)
   - Added OTP input field (shown when OTP is required)
   - Added state management for OTP flow
   - Modified login handler to support two-step process:
     - Step 1: Submit email/password → receive OTP
     - Step 2: Submit OTP → complete login
   - Added OTP formatting (6 digits, numbers only)
   - Added error handling for OTP verification

## Login Flow

1. Admin enters email and password
2. System validates credentials
3. System generates 6-digit OTP and sends it via email
4. Admin receives email with OTP code
5. Admin enters OTP code in the login form
6. System verifies OTP (checks validity and expiration)
7. On success, admin is logged in and redirected to dashboard

## Setup Instructions

### 1. Run Database Migration

Execute the SQL migration file on your database:

```sql
-- Run this SQL script on your database
ALTER TABLE admin 
ADD COLUMN IF NOT EXISTS login_otp VARCHAR(6) NULL,
ADD COLUMN IF NOT EXISTS login_otp_expires_at TIMESTAMP NULL;
```

Or if you're using Laravel migrations, you can run:
```bash
php artisan migrate
```

### 2. Verify Email Configuration

Ensure your Brevo email service is properly configured:
- `BREVO_API_KEY` is set in your `.env` file
- Email sending is working (test with `/api/test-brevo-api` endpoint)

### 3. Test the Flow

1. Navigate to admin login page
2. Enter admin email and password
3. Check email for OTP code
4. Enter OTP code in the form
5. Complete login

## Security Features

- **OTP Expiration**: OTP codes expire after 10 minutes
- **One-time Use**: OTP is cleared after successful verification
- **Email Verification**: OTP is sent to the registered admin email
- **Password Re-verification**: Password is verified again during OTP verification

## Error Handling

- Invalid credentials: Returns 401 with error message
- Invalid OTP: Returns 401 with specific error message
- Expired OTP: Returns 401 with expiration message
- Email sending failure: Logs error but doesn't reveal failure to user (security)

## Notes

- OTP codes are 6-digit numeric codes
- OTP expires after 10 minutes
- Each login attempt generates a new OTP
- Old OTPs are automatically invalidated when a new one is generated

