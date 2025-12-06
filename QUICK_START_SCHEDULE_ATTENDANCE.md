# Quick Start: Schedule Attendance Tracking

## ✅ Setup Complete!

Your `schedule_attendance` table is created and the code is ready to use it.

## How It Works Now:

### 1. **QR Code Scanning**
When a registrant scans a QR code:
- ✅ Attendance is recorded in `schedule_attendance` table
- ✅ Links to specific `trainingScheduleID` (the day/schedule)
- ✅ Prevents duplicate attendance for same schedule
- ✅ Updates overall `registration.registrationStatus` to "Attended"

### 2. **Viewing Attendance**
In OrganizationTrainings.vue:
- ✅ See which specific schedules each registrant attended
- ✅ Status shows count: "Attended (2/3)"
- ✅ Schedule Attendance column shows ✓ or ○ for each schedule

### 3. **Certificate Issuance**
- ✅ Can issue certificates if registrant attended at least 1 schedule
- ✅ Shows partial attendance (e.g., 2 out of 3 schedules)

## Test It:

1. **Generate QR for a schedule:**
   - Open a training with multiple schedules
   - Click on a schedule card to generate QR

2. **Scan QR code:**
   - Use the attendance check-in page
   - Enter name and email matching registration
   - Submit attendance

3. **Verify in database:**
   ```sql
   SELECT * FROM "schedule_attendance" ORDER BY "attendedAt" DESC;
   ```

4. **Check in frontend:**
   - Open training details modal
   - View registrants table
   - See "Schedule Attendance" column with per-schedule status

## Example Flow:

**Training with 3 schedules:**
- Day 1: Dec 3, 2025 (Schedule ID: 36)
- Day 2: Dec 4, 2025 (Schedule ID: 37)
- Day 3: Dec 5, 2025 (Schedule ID: 38)

**Registrant attends Day 1 and Day 2:**

1. Scan Day 1 QR → Creates record:
   ```
   schedule_attendance:
   - registrationID: 100
   - trainingScheduleID: 36
   - attendedAt: 2025-12-03 10:00:00
   ```

2. Scan Day 2 QR → Creates record:
   ```
   schedule_attendance:
   - registrationID: 100
   - trainingScheduleID: 37
   - attendedAt: 2025-12-04 14:00:00
   ```

3. Frontend shows:
   - Status: "Attended (2/3)"
   - Schedule Attendance:
     - ✓ Dec 3 - Attended
     - ✓ Dec 4 - Attended
     - ○ Dec 5 - Not Attended

## Troubleshooting:

**If attendance not recording:**
1. Check `schedule_attendance` table exists
2. Verify foreign keys are set up correctly
3. Check that `trainingScheduleID` exists in `trainingschedule` table
4. Verify QR code `attendance_key` matches schedule

**If status not updating:**
1. Check `registration.registrationStatus` after scanning
2. Verify at least one schedule attendance record exists
3. Check backend logs for errors

## Next Steps:

Everything is ready! Just:
1. ✅ Table is created
2. ✅ Code is updated
3. ✅ Frontend is configured

**Start using it:**
- Generate QR codes for schedules
- Scan them via attendance check-in
- View schedule-specific attendance in registrants list

