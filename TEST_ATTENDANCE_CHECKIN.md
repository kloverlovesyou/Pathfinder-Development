# Testing Attendance Check-In with Schedule Tracking

## ✅ Verification Checklist

After creating the `schedule_attendance` table, verify the following:

### 1. Backend API Endpoint Test

**Endpoint:** `POST /api/attendance/checkin`

**Test Payload:**
```json
{
  "trainingID": 1,
  "key": "ABC123XYZ456",  // The QR code attendance_key from trainingschedule table
  "firstName": "John",
  "lastName": "Doe",
  "emailAddress": "john.doe@example.com"
}
```

**Expected Behavior:**
1. ✅ Validates training exists
2. ✅ Validates schedule exists with matching attendance_key
3. ✅ Checks if QR code has expired (schedule end_time)
4. ✅ Verifies applicant is registered for the training
5. ✅ Verifies name matches registered applicant
6. ✅ Creates record in `schedule_attendance` table
7. ✅ Updates `registration.registrationStatus` to "Attended" (if at least one schedule attended)
8. ✅ Returns success message

**Test Cases:**
- ✅ First attendance scan → Creates new record in `schedule_attendance`
- ✅ Duplicate scan for same schedule → Returns "Attendance already recorded"
- ✅ Scan for different schedule → Creates another record
- ✅ Expired QR code → Returns "QR Code Expired"
- ✅ Not registered → Returns "Access Denied"
- ✅ Wrong name → Returns "Verification Failed"

### 2. Database Verification

**Check schedule_attendance table:**
```sql
SELECT * FROM "schedule_attendance" 
ORDER BY "attendedAt" DESC 
LIMIT 10;
```

**Expected columns:**
- `scheduleAttendanceID` - Auto-increment ID
- `registrationID` - Links to registration
- `trainingScheduleID` - Links to specific schedule
- `attendedAt` - Timestamp of attendance
- `attendanceKey` - The QR key that was scanned

**Check registration status update:**
```sql
SELECT 
    r."registrationID",
    r."registrationStatus",
    COUNT(sa."scheduleAttendanceID") as "attendedSchedules"
FROM "registration" r
LEFT JOIN "schedule_attendance" sa ON r."registrationID" = sa."registrationID"
WHERE r."trainingID" = 1  -- Replace with your test training ID
GROUP BY r."registrationID", r."registrationStatus";
```

### 3. Frontend Display Test

**In OrganizationTrainings.vue:**
1. ✅ Open a training with multiple schedules
2. ✅ View registrants list
3. ✅ Verify "Schedule Attendance" column shows:
   - Each schedule listed
   - ✓ for attended schedules
   - ○ for not attended schedules
4. ✅ Verify status shows attendance count (e.g., "Attended (2/3)")
5. ✅ Certificate button enabled if at least one schedule attended

### 4. QR Code Flow Test

1. ✅ Generate QR code for a specific schedule
2. ✅ QR code contains correct `trainingID` and `key` (attendance_key)
3. ✅ Scan QR code via attendance check-in page
4. ✅ Attendance recorded for that specific schedule
5. ✅ Subsequent scans show "already recorded" message

### 5. Multi-Schedule Scenario Test

**Training with 3 schedules:**
- Day 1 (Schedule ID: 36)
- Day 2 (Schedule ID: 37)  
- Day 3 (Schedule ID: 38)

**Test Steps:**
1. Registrant scans Day 1 QR → Attendance recorded for Schedule 36
2. Registrant scans Day 2 QR → Attendance recorded for Schedule 37
3. Registrant doesn't attend Day 3

**Expected Result:**
- Status: "Attended (Partial)" or "Attended (2/3)"
- Schedule Attendance column shows:
  - ✓ Day 1 - Attended
  - ✓ Day 2 - Attended
  - ○ Day 3 - Not Attended
- Certificate can be issued (attended at least 1 schedule)

### 6. Certificate Issuance Test

1. ✅ Registrant with at least 1 schedule attended → Can issue certificate
2. ✅ Registrant with 0 schedules attended → Cannot issue certificate
3. ✅ Bulk certificate issuance respects schedule attendance

## Common Issues to Watch For

1. **Foreign Key Errors:**
   - Ensure `registrationID` and `trainingScheduleID` exist
   - Check that foreign key constraints are properly set

2. **Duplicate Attendance:**
   - Unique constraint should prevent duplicate records
   - Returns "already recorded" message

3. **Schedule Not Found:**
   - Verify `attendance_key` matches between QR code and database
   - Check that schedule exists and belongs to the training

4. **Status Not Updating:**
   - Verify `registration.registrationStatus` updates after first attendance
   - Check that `recordStage()` method is called

## Quick Test Query

Run this to see attendance per schedule:
```sql
SELECT 
    sa."scheduleAttendanceID",
    sa."registrationID",
    sa."trainingScheduleID",
    sa."attendedAt",
    ts."schedule" as "scheduleDate",
    ts."end_time" as "scheduleEndTime",
    r."registrationStatus"
FROM "schedule_attendance" sa
JOIN "trainingschedule" ts ON sa."trainingScheduleID" = ts."trainingScheduleID"
JOIN "registration" r ON sa."registrationID" = r."registrationID"
ORDER BY sa."attendedAt" DESC;
```

