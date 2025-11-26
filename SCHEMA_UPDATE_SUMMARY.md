# Database Schema Update Summary

This document summarizes all updates made to align the codebase with the new ERD schema.

## Updated Models

### 1. Career ✅
**Changes:**
- `qualificationStandard` → `qualificationStandards` (plural) - matches ERD
- Updated PHPDoc to reflect `qualificationStandards`
- Maintained backward compatibility with `qualificationStandard` in fillable array
- Updated CareerController to support both field names

### 2. Organization ✅
**Changes:**
- Added `RegistrationRequirements` field (pdf) in PHPDoc
- Added `RegistrationStatus` field (Registered, In Review, Verified/Declined)
- Updated field names to PascalCase: `Logo_directory`, `Name`, `Location`, `WebsiteURL`, `EmailAddress`, `Password`
- Added `phoneNumber` to PHPDoc (already in fillable)
- Maintained backward compatibility

### 3. Training ✅
**Changes:**
- Updated field names: `Title`, `Description`
- Maintained backward compatibility

### 4. TrainingSchedule ✅
**Changes:**
- Added ERD fields: `Date`, `StartTime`, `EndTime` with proper casts
- `Mode` (Onsite/Online)
- `Location` (if Onsite)
- `TrainingLink` (if Online)
- Added comprehensive PHPDoc documentation
- Kept existing implementation fields for backward compatibility

### 5. ApplicationHistory ✅
**Changes:**
- Fixed primary key typo: `appplicationHistoryID` → `applicationHistoryID`
- Updated field names to PascalCase: `HistoryDate`, `InterviewSchedule`, `InterviewMode`, `InterviewLocation`, `InterviewLink`
- Added both PascalCase and camelCase versions to fillable array for backward compatibility
- Updated casts to support both naming conventions

### 6. Application ✅
**Changes:**
- Updated field name: `Requirements` (pdf)
- Maintained `requirement_directory` for backward compatibility
- Added `history()` relationship to ApplicationHistory

### 7. Registration ✅
**Changes:**
- Updated field names to PascalCase: `RegistrationDate`, `RegistrationStatus`, `CertTrackingID`, `CertGivenDate`
- Added `Certificate` field (png)
- `RegistrationStatus` values: Registered/Cancelled/Did not Attend/Attended
- Maintained backward compatibility

### 8. Applicant ✅
**Changes:**
- Updated field names to PascalCase: `DisplayPicture_directory`, `FirstName`, `MiddleName`, `LastName`, `Address`, `EmailAddress`, `PhoneNumber`, `Password`
- Fixed PHPDoc: `resumes` → `resume` (one-to-one relationship)
- Maintained backward compatibility

### 9. Admin ✅
**Changes:**
- Updated field names to PascalCase: `AdminID`, `Name`, `Location`, `WebsiteURL`, `EmailAddress`, `Password`
- Maintained backward compatibility

### 10. Certification ✅
**Changes:**
- Updated PHPDoc to include `Certificate` field (png) and `IsSelected` boolean
- Added `Certificate` to fillable array (with backward compatibility for `certificate` and `certificate_path`)
- Added `IsSelected` to casts as boolean

### 11. Tag ✅
**Changes:**
- Added `trainings()` relationship for many-to-many with Training via `training_tag` junction table
- Already had `careers()` relationship

## Updated Controllers

### CareerController ✅
- Updated to support both `qualificationStandards` (ERD) and `qualificationStandard` (backward compatibility)
- Validation accepts both field names
- Response includes both field names for compatibility

## Key Principles Applied

1. **ERD Compliance**: Updated all field names to match ERD exactly where specified
2. **Backward Compatibility**: Maintained support for existing camelCase/snake_case field names in fillable arrays
3. **Documentation**: Updated PHPDoc comments to reflect new schema accurately
4. **Fillable Fields**: Added both new (ERD) and old field names to `$fillable` arrays
5. **Casts**: Updated casts to support both naming conventions where applicable
6. **Relationships**: Verified and added all relationships to match ERD structure

## Models Verified (No Changes Needed)

- Resume - Matches ERD (summary, professionalLink)
- Education - Matches ERD structure (educationLevel, strand, program, major, institutionName, institutionAddress, graduationYear)
- Experience - Matches ERD structure (jobTitle, companyName, companyAddress, startYear, endYear)
- Skill - Matches ERD (skillName)
- ApplicationStatus - Matches ERD (statusName)
- OrganizationsChoice - Matches ERD junction table structure

## Relationships Verified ✅

All relationships match ERD:
- Admin → Organization (one-to-many) ✅
- Organization → Training (one-to-many) ✅
- Organization → Career (one-to-many) ✅
- Training → TrainingSchedule (one-to-many) ✅
- Training ↔ Tag (many-to-many via training_tag) ✅
- Career ↔ Tag (many-to-many via career_tag) ✅
- Organization ↔ Career ↔ Training (many-to-many-to-many via OrganizationsChoice) ✅
- Applicant → Registration (one-to-many) ✅
- Training → Registration (one-to-many) ✅
- Applicant → Resume (one-to-one) ✅
- Resume → Certifications (one-to-many) ✅
- Applicant → Certifications (one-to-many) ✅
- Resume → Education (one-to-many) ✅
- Resume → Experience (one-to-many) ✅
- Resume → Skills (one-to-many) ✅
- Applicant → Application (one-to-many) ✅
- Career → Application (one-to-many) ✅
- Application → ApplicationHistory (one-to-many) ✅
- ApplicationStatus → ApplicationHistory (one-to-many) ✅

## Next Steps

1. ✅ Update models to match ERD field names
2. ✅ Update controllers to handle both old and new field names
3. ⏳ Update frontend components to use new field names with backward compatibility
4. ⏳ Test all CRUD operations with new schema
5. ⏳ Gradually phase out backward compatibility after migration

