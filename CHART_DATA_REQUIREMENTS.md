# Chart Data Requirements

This document outlines the specific data requirements for each chart on the Organization Homepage.

## Overview

All charts will render even with zero data, but they need specific data sources to display meaningful information. The charts calculate data from:
- **Trainings**: Organization's training records
- **Careers**: Organization's career postings
- **Registrations**: People who registered for the organization's trainings
- **Applications**: People who applied to the organization's careers

---

## Chart 1: General Overview (Line Chart)

**Type**: Line chart showing trends over time  
**Minimum Data Required**: 
- **At least 1 registration OR 1 application** in the current year

**Data Sources**:
- `registrationsData` - Array of registration objects (from database)
- `applicationsData` - Array of application objects (from database)

**Required Fields**:
- **Registrations**: Each registration needs:
  - `registrationDate` OR `dateRegistered` (date string)
  
- **Applications**: Each application needs:
  - `dateSubmitted` (date string)

**Time Period**: Full year - 12 months (January to December of current year)

**What Shows**:
- **Blue line**: Number of training registrants per month
- **Gray line**: Number of career applicants per month

**Example Minimum Data**:
```json
// At least one registration with a date in the last 6 months:
{
  "registrationDate": "2024-12-15",  // or "dateRegistered"
  "trainingID": 1
}

// OR at least one application with a date in the last 6 months:
{
  "dateSubmitted": "2024-12-10",
  "careerID": 1
}
```

---

## Chart 2: Training Summary (Pie Chart)

**Type**: Pie chart showing distribution of training modes  
**Minimum Data Required**: 
- **At least 1 training** in the organization's training list

**Data Sources**:
- `trainingsData` - Array of training objects (from database)

**Required Fields**:
- Each training needs:
  - `mode` OR `Mode` (string: "onsite"/"on-site"/"on site" or "online")

**What Shows**:
- **On-site**: Count of trainings with mode = "onsite", "on-site", or "on site"
- **Online**: Count of trainings with mode = "online"

**Note**: Hybrid trainings are no longer included in this chart.

**Example Minimum Data**:
```json
// At least one training:
{
  "trainingID": 1,
  "mode": "onsite"  // or "Mode": "online" or "hybrid"
}
```

**Note**: If all trainings have the same mode, the pie chart will show one large slice.

---

## Chart 3: Applicant Engagement (Bar Chart)

**Type**: Bar chart showing engagement across different categories  
**Minimum Data Required**: 
- **At least 1 career OR 1 training** in the organization

**Data Sources**:
- `careersData` - Array of career objects (from database)
- `totalTrainings` - Count of trainings (calculated from trainingsData)

**Required Fields**:
- **Careers**: All careers are counted (no specific fields needed)
- **Trainings**: Counted from `trainingsData.length`

**What Shows**:
- **Jobs**: Total count of all careers
- **Trainings**: Total count of trainings

**Example Minimum Data**:
```json
// At least one career:
{
  "careerID": 1,
  "position": "Software Engineer"
}

// OR at least one training (counted automatically):
{
  "trainingID": 1,
  "title": "Any Training"
}
```

**Note**: Internships have been removed. This chart will show data even if you only have careers OR only trainings.

---

## Chart 4: Career Insights (Doughnut Chart)

**Type**: Doughnut chart showing application status distribution  
**Minimum Data Required**: 
- **At least 1 application** to any of the organization's careers

**Data Sources**:
- `applicationsData` - Array of application objects (from database)

**Required Fields**:
- Each application needs:
  - `applciationStatus` OR `applicationStatus` OR `status` (string)

**Status Values Recognized**:
- **Submitted**: "submitted" or empty/null
- **In Review**: "in review" or "pending"
- **For Interview**: "for interview"
- **Accepted**: "accepted"
- **Rejected**: "rejected"

**What Shows**:
- **Submitted**: Applications with status = "submitted" or no status
- **In Review**: Applications with status = "in review" or "pending"
- **For Interview**: Applications with status = "for interview"
- **Accepted**: Applications with status = "accepted"
- **Rejected**: Applications with status = "rejected"

**Example Minimum Data**:
```json
// At least one application:
{
  "applicationID": 1,
  "careerID": 1,
  "status": "pending"  // or "applciationStatus" or "applicationStatus"
}
```

---

## Summary Table

| Chart | Minimum Requirement | Data Source | Key Fields Needed |
|-------|-------------------|-------------|-------------------|
| **Chart 1: General Overview** | 1 registration OR 1 application in current year | `registrationsData`, `applicationsData` (from database) | `registrationDate`/`dateRegistered`, `dateSubmitted` |
| **Chart 2: Training Summary** | 1 training | `trainingsData` (from database) | `mode` or `Mode` ("onsite"/"on-site" or "online") |
| **Chart 3: Applicant Engagement** | 1 career OR 1 training | `careersData`, `trainingsData` (from database) | Any career or training (internships removed) |
| **Chart 4: Career Insights** | 1 application | `applicationsData` (from database) | `status`/`applciationStatus`/`applicationStatus` (5 statuses) |

---

## Data Flow

**All charts now depend entirely on database data - no fallback to dashboard endpoint.**

1. **Fetch Training Stats** → Gets `trainingsData` from database (needed for Charts 2 & 3)
2. **Fetch Career Stats** → Gets `careersData` from database (needed for Chart 3)
3. **Fetch Registrations** → Gets `registrationsData` from database (needed for Chart 1)
   - Loops through each training and calls `/trainings/{trainingID}/registrants`
4. **Fetch Applications** → Gets `applicationsData` from database (needed for Charts 1 & 4)
   - Loops through each career and calls `/careers/{careerID}/applicants`
5. **Calculate Chart Data** → Processes all database data and populates charts
6. **Render Charts** → Displays charts with calculated data from database

---

## Troubleshooting

### Charts showing all zeros:
- **Chart 1**: Check if you have registrations/applications with valid dates in the current year (January to December)
- **Chart 2**: Check if trainings have `mode` or `Mode` field set to "onsite"/"on-site" or "online" (hybrid is excluded)
- **Chart 3**: Check if you have at least one career or training (internships are no longer shown separately)
- **Chart 4**: Check if you have applications with status fields matching: Submitted, In Review, For Interview, Accepted, or Rejected

### Charts not rendering:
- Check browser console for errors
- Verify API endpoints are accessible
- Ensure authentication token is valid
- Check network tab for failed API requests

### Data appears incorrect:
- Verify date formats are correct (ISO format preferred)
- Check field names match expected values (case-sensitive for some fields)
- Ensure status values match recognized values exactly

---

## Testing Checklist

To test if charts will display data:

- [ ] Create at least 1 training with a `mode` field
- [ ] Create at least 1 career posting
- [ ] Have at least 1 person register for a training (within last 6 months)
- [ ] Have at least 1 person apply to a career
- [ ] Ensure registration has `registrationDate` or `dateRegistered`
- [ ] Ensure application has `dateSubmitted`
- [ ] Ensure application has a status field (`status`, `applicationStatus`, or `applciationStatus`)

