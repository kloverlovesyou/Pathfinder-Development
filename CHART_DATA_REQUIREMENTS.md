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
- **At least 1 registration OR 1 application** in the last 6 months

**Data Sources**:
- `registrationsData` - Array of registration objects
- `applicationsData` - Array of application objects

**Required Fields**:
- **Registrations**: Each registration needs:
  - `registrationDate` OR `dateRegistered` (date string)
  
- **Applications**: Each application needs:
  - `dateSubmitted` (date string)

**Time Period**: Last 6 months (Jan, Feb, Mar, Apr, May, Jun from current date)

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
- `trainingsData` - Array of training objects

**Required Fields**:
- Each training needs:
  - `mode` OR `Mode` (string: "onsite", "online", or "hybrid")

**What Shows**:
- **Onsite**: Count of trainings with mode = "onsite"
- **Online**: Count of trainings with mode = "online"  
- **Hybrid**: Count of trainings with mode = "hybrid"

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
- `careersData` - Array of career objects
- `totalTrainings` - Count of trainings (calculated from trainingsData)

**Required Fields**:
- **Careers**: Each career needs:
  - `position` (string) OR `type` (string)
  
- **Trainings**: Counted from `trainingsData.length`

**What Shows**:
- **Internships**: Careers where position contains "intern" OR type = "internship"
- **Jobs**: Careers that are NOT internships
- **Trainings**: Total count of trainings

**Example Minimum Data**:
```json
// At least one career:
{
  "careerID": 1,
  "position": "Software Engineer Intern"  // or "type": "internship"
}

// OR at least one training (counted automatically):
{
  "trainingID": 1,
  "title": "Any Training"
}
```

**Note**: This chart will show data even if you only have careers OR only trainings.

---

## Chart 4: Career Insights (Doughnut Chart)

**Type**: Doughnut chart showing application status distribution  
**Minimum Data Required**: 
- **At least 1 application** to any of the organization's careers

**Data Sources**:
- `applicationsData` - Array of application objects

**Required Fields**:
- Each application needs:
  - `applciationStatus` OR `applicationStatus` OR `status` (string)

**Status Values Recognized**:
- **Accepted**: "accepted" or "for interview"
- **Pending**: "pending", "submitted", "in review", or empty/null
- **Rejected**: "rejected"

**What Shows**:
- **Accepted**: Applications with status = "accepted" or "for interview"
- **Pending**: Applications with status = "pending", "submitted", "in review", or no status
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
| **Chart 1: General Overview** | 1 registration OR 1 application in last 6 months | `registrationsData`, `applicationsData` | `registrationDate`/`dateRegistered`, `dateSubmitted` |
| **Chart 2: Training Summary** | 1 training | `trainingsData` | `mode` or `Mode` |
| **Chart 3: Applicant Engagement** | 1 career OR 1 training | `careersData`, `trainingsData` | `position`/`type`, training count |
| **Chart 4: Career Insights** | 1 application | `applicationsData` | `status`/`applciationStatus`/`applicationStatus` |

---

## Data Flow

1. **Fetch Training Stats** → Gets `trainingsData` (needed for Charts 2 & 3)
2. **Fetch Career Stats** → Gets `careersData` (needed for Chart 3)
3. **Fetch Registrations** → Gets `registrationsData` (needed for Chart 1)
   - Loops through each training and calls `/trainings/{trainingID}/registrants`
4. **Fetch Applications** → Gets `applicationsData` (needed for Charts 1 & 4)
   - Loops through each career and calls `/careers/{careerID}/applicants`
5. **Calculate Chart Data** → Processes all data and populates charts
6. **Render Charts** → Displays charts with calculated data

---

## Troubleshooting

### Charts showing all zeros:
- **Chart 1**: Check if you have registrations/applications with valid dates in the last 6 months
- **Chart 2**: Check if trainings have `mode` or `Mode` field set
- **Chart 3**: Check if you have at least one career or training
- **Chart 4**: Check if you have applications with status fields

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

