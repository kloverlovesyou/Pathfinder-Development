# Attendance Table Comparison

## Existing `attendance` Table vs New `schedule_attendance` Table

### Existing `attendance` Table Structure:
```
attendanceID (PK)
attendanceDateTime (timestamp)
attendanceStatus (varchar)
trainingID (FK) → Links to training (overall training)
registrationID (FK)
```

### New `schedule_attendance` Table Structure:
```
scheduleAttendanceID (PK)
registrationID (FK)
trainingScheduleID (FK) → Links to specific schedule/day ⭐ KEY DIFFERENCE
attendedAt (timestamp)
attendanceKey (varchar) - QR key
```

## Key Differences:

| Feature | `attendance` Table | `schedule_attendance` Table |
|---------|-------------------|----------------------------|
| **Granularity** | Training-level (one record per training) | Schedule-level (multiple records per training) |
| **Links to** | `trainingID` (entire training) | `trainingScheduleID` (specific schedule/day) |
| **Multi-day Support** | ❌ Cannot track which day/schedule | ✅ Tracks each schedule separately |
| **Status Tracking** | Has `attendanceStatus` column | No status column (uses registration status) |
| **QR Key Tracking** | ❌ No | ✅ Stores `attendanceKey` |
| **Purpose** | General attendance record | Day-by-day schedule attendance |

## The Problem:

**Your existing `attendance` table:**
- Links to `trainingID` → Cannot tell which specific schedule/day was attended
- For a 3-day training → Can only store 1 record (trained attended overall)
- Cannot track: "Attended Day 1, Day 2, but not Day 3"

**New `schedule_attendance` table:**
- Links to `trainingScheduleID` → Can track each schedule/day separately
- For a 3-day training → Can store 3 separate records (one per schedule)
- Can track: "Attended Day 1 (Schedule 36), Day 2 (Schedule 37), but not Day 3 (Schedule 38)"

## Recommendation:

### Option 1: Use Existing `attendance` Table (Modify It) ⚠️
**Pros:**
- No new table needed
- Consolidates data

**Cons:**
- Need to add `trainingScheduleID` column
- Need to modify existing data/structure
- May break existing functionality if `attendance` table is used elsewhere

### Option 2: Use New `schedule_attendance` Table (Recommended) ✅
**Pros:**
- Clean separation of concerns
- Doesn't affect existing `attendance` table
- Designed specifically for schedule-level tracking
- Already implemented and tested

**Cons:**
- Additional table to manage
- Need to maintain both tables if `attendance` is used elsewhere

### Option 3: Keep Both Tables
- `attendance` table → General/overall attendance records
- `schedule_attendance` table → Detailed schedule/day-level tracking

## Decision:

**For your requirement ("day by day status depends on the schedule"), you NEED schedule-level tracking.**

The existing `attendance` table **CANNOT** do this because:
- It links to `trainingID` (entire training)
- It cannot distinguish between different schedules/days

The new `schedule_attendance` table **CAN** do this because:
- It links to `trainingScheduleID` (specific schedule)
- Each schedule gets its own attendance record

