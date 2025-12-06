# Schedule Attendance Schema Documentation

## Database Relationships

This document outlines how the schedule-specific attendance tracking integrates with the existing database schema.

### Table Relationships Diagram

```
┌─────────────────┐
│   training      │
│─────────────────│
│ trainingID (PK) │
│ title           │
│ description     │
│ organizationID  │
└────────┬────────┘
         │ 1:N
         │
         ├─────────────────────────────────────┐
         │                                     │
         ▼                                     ▼
┌──────────────────────┐            ┌──────────────────────┐
│  trainingschedule    │            │   registration       │
│──────────────────────│            │──────────────────────│
│ trainingScheduleID   │            │ registrationID (PK)  │
│ trainingID (FK)      │            │ trainingID (FK)      │
│ schedule             │            │ applicantID (FK)     │
│ end_time             │            │ registrationStatus   │
│ mode                 │            │ certTrackingID       │
│ location             │            │ certGivenDate        │
│ trainingLink         │            │ certificatePath      │
│ qr_generated_at      │            │ checked_in_at        │
│ attendance_expires_at│            │ registeredDate       │
│ attendance_key       │            │ ongoingDate          │
└──────────┬───────────┘            │ completedDate        │
           │                        │ certifiedDate        │
           │ N:1                    └──────────┬───────────┘
           │                                   │
           │                                   │ 1:N
           │                                   │
           └───────────────────────────────────┘
                          │
                          │
                          ▼
              ┌───────────────────────────┐
              │   schedule_attendance     │ (NEW TABLE)
              │───────────────────────────│
              │ scheduleAttendanceID (PK) │
              │ registrationID (FK)       │
              │ trainingScheduleID (FK)   │
              │ attendedAt                │
              │ attendanceKey             │
              └───────────┬───────────────┘
                          │
                          │
                          ▼
              ┌───────────────────────────┐
              │   certifications          │
              │───────────────────────────│
              │ certificationID (PK)      │
              │ applicantID (FK)          │
              │ certificate_path          │
              │ certificationName         │
              └───────────────────────────┘
```

### Key Relationships

1. **Training → Training Schedules** (1:N)
   - One training can have multiple schedules (days)
   - `training.trainingID` → `trainingschedule.trainingID`

2. **Training → Registrations** (1:N)
   - One training can have multiple registrations (applicants)
   - `training.trainingID` → `registration.trainingID`

3. **Registration → Schedule Attendance** (1:N) ✨ NEW
   - One registration can have multiple schedule attendance records
   - `registration.registrationID` → `schedule_attendance.registrationID`
   - Each record represents attendance for a specific schedule

4. **Training Schedule → Schedule Attendance** (1:N) ✨ NEW
   - One training schedule can have multiple attendance records (from different registrants)
   - `trainingschedule.trainingScheduleID` → `schedule_attendance.trainingScheduleID`

5. **Registration → Certifications** (via applicantID)
   - `registration.applicantID` → `certifications.applicantID`
   - Certificates are issued per applicant, linked via applicantID

### Flow of Attendance Tracking

1. **Registration Phase:**
   ```
   Applicant registers for Training
   → Creates record in `registration` table
   → registrationStatus = "Registered"
   ```

2. **Attendance Recording (QR Scan):**
   ```
   Applicant scans QR code for specific Schedule
   → Creates record in `schedule_attendance` table
   → Links: registrationID + trainingScheduleID + attendedAt + attendanceKey
   → Updates `registration.registrationStatus` = "Attended" (if at least one schedule attended)
   ```

3. **Certificate Issuance:**
   ```
   Organization issues certificate
   → Checks if registrant attended at least one schedule (via schedule_attendance)
   → Updates `registration.certificatePath`, `certTrackingID`, `certGivenDate`
   → Creates record in `certifications` table
   ```

### New Table: `schedule_attendance`

| Column | Type | Description |
|--------|------|-------------|
| `scheduleAttendanceID` | int4 (PK) | Unique identifier |
| `registrationID` | int4 (FK) | Links to `registration.registrationID` |
| `trainingScheduleID` | int4 (FK) | Links to `trainingschedule.trainingScheduleID` |
| `attendedAt` | timestamp | When attendance was recorded |
| `attendanceKey` | varchar | The QR code key that was scanned |

**Constraints:**
- Unique constraint on `(registrationID, trainingScheduleID)` - prevents duplicate attendance per schedule
- Foreign key to `registration` with CASCADE delete
- Foreign key to `trainingschedule` with CASCADE delete
- Indexes on both foreign keys for performance

### Example Data Flow

**Scenario:** Training with 3 schedules, Applicant registers and attends 2 out of 3 schedules

1. **Registration Created:**
   ```sql
   registration table:
   - registrationID: 100
   - trainingID: 5
   - applicantID: 42
   - registrationStatus: "Registered"
   ```

2. **Attend Schedule 1 (Day 1):**
   ```sql
   schedule_attendance table:
   - scheduleAttendanceID: 1
   - registrationID: 100
   - trainingScheduleID: 36
   - attendedAt: 2025-12-03 10:00:00
   - attendanceKey: "ABC123XYZ456"
   
   registration table updated:
   - registrationStatus: "Attended"
   ```

3. **Attend Schedule 2 (Day 2):**
   ```sql
   schedule_attendance table:
   - scheduleAttendanceID: 2
   - registrationID: 100
   - trainingScheduleID: 37
   - attendedAt: 2025-12-04 14:00:00
   - attendanceKey: "DEF789GHI012"
   ```

4. **Frontend Display:**
   - Status: "Attended (Partial)" or "Attended (2/3)"
   - Schedule Attendance column shows:
     - ✓ Schedule 1 (Dec 3) - Attended
     - ✓ Schedule 2 (Dec 4) - Attended  
     - ○ Schedule 3 (Dec 5) - Not Attended

5. **Certificate Issuance:**
   - Organization can issue certificate (attended at least 1 schedule)
   - Updates `registration.certificatePath`, `certTrackingID`, `certGivenDate`
   - Creates `certifications` record

### Backward Compatibility

The system maintains backward compatibility:
- Existing `registration.registrationStatus` still works
- Overall status updates based on schedule attendance
- Single-schedule trainings still work (one schedule = one attendance record)
- Frontend gracefully handles trainings with or without schedule attendance data

