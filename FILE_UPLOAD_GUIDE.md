# File Upload System Guide

## How Files Are Currently Handled

### 1. **File Storage Structure**

All uploaded files are stored in the `uploads/` folder with the following structure:

```
uploads/
├── user_1/
│   ├── file_6933dc8291c7e7.50933677_1765006466.jpg (Student ID Photo)
│   ├── file_6933dc82935eb1.52600392_1765006466.pdf (Grades Form 1)
│   ├── file_6933dc82939d91.20299065_1765006466.pdf (Form 137 Junior)
│   ├── file_6933dc8293f4c1.61354499_1765006466.pdf (Form 137 Senior)
│   ├── file_6933dc82943159.08474565_1765006466.pdf (Certificate of Enrollment)
│   └── file_6933dc8296eb84.04816107_1765006466.png (Signature)
├── user_2/
│   └── ...
└── user_N/
    └── ...
```

### 2. **File Types Stored**

The system stores the following files for each applicant:

1. **Student ID Photo** (`student_id_image`)
   - Stored in: `personal` table
   - Column: `student_id_image`
   - Format: JPG/PNG
   - Path format: `uploads/user_{userId}/file_{uniqueId}_{timestamp}.jpg`

2. **Education Documents** (stored in `education_attachments` table):
   - **Grades Form 1** (`grades_form_1`)
   - **Form 137 Junior** (`form_137_junior`)
   - **Form 137 Senior** (`form_137_senior`)
   - **Certificate of Enrollment** (`certificate_of_enrollment_path`)
   - Format: PDF
   - Path format: `uploads/user_{userId}/file_{uniqueId}_{timestamp}.pdf`

3. **Signature** (stored separately, not in database currently)
   - Format: PNG
   - Path format: `uploads/user_{userId}/file_{uniqueId}_{timestamp}.png`
   - Note: Currently saved but not linked to database table

### 3. **How Files Are Saved**

Files are processed in `backend/submit_application.php` using the `saveBase64File()` function:

```php
function saveBase64File($base64Data, $fileName, $userId) {
    // Creates: uploads/user_{userId}/ directory
    // Generates unique filename: file_{uniqid}_{timestamp}.{extension}
    // Decodes Base64 and saves file
    // Returns: relative path like "uploads/user_1/file_xxx.jpg"
}
```

**Process Flow:**
1. Frontend converts files to Base64 and stores in `localStorage`
2. On submit, Base64 data is sent to backend
3. Backend decodes Base64 and saves to `uploads/user_{userId}/` folder
4. Database stores only the relative file path (e.g., `uploads/user_1/file_xxx.jpg`)

### 4. **Database Storage**

The database stores **only file paths**, not the actual files:

- **`personal` table**: `student_id_image` column stores path
- **`education_attachments` table**: 
  - `grades_form_1` column
  - `form_137_junior` column
  - `form_137_senior` column
  - `certificate_of_enrollment_path` column

## How to Access Uploaded Files

### Method 1: Direct File System Access

Files are stored on the server at:
```
D:\xampp\htdocs\STUDENT_PORTAL\uploads\user_{userId}\
```

You can access them directly via file system or through your web server.

### Method 2: Via Web Browser (Recommended)

Use the provided `view_file.php` script (see below) to view/download files securely.

### Method 3: Query Database for File Paths

```sql
-- Get all files for a specific user
SELECT 
    u.id AS user_id,
    u.application_number,
    p.student_id_image,
    e.grades_form_1,
    e.form_137_junior,
    e.form_137_senior,
    e.certificate_of_enrollment_path
FROM users u
LEFT JOIN personal p ON u.id = p.user_id
LEFT JOIN education_attachments e ON u.id = e.student_id
WHERE u.id = 1;  -- Replace with actual user_id
```

### Method 4: List All Files for a User

```sql
-- Get user's application number and all file paths
SELECT 
    u.application_number,
    CONCAT('uploads/user_', u.id, '/') AS base_path,
    p.student_id_image AS id_photo,
    e.grades_form_1,
    e.form_137_junior,
    e.form_137_senior,
    e.certificate_of_enrollment_path
FROM users u
LEFT JOIN personal p ON u.id = p.user_id
LEFT JOIN education_attachments e ON u.id = e.student_id
WHERE u.application_number = '2026021546';  -- Replace with actual application number
```

## Security Considerations

1. **File Access Control**: The `view_file.php` script includes security checks
2. **Directory Permissions**: Ensure `uploads/` folder has proper permissions (755)
3. **File Validation**: Files are validated before saving (size, format, AI validation for ID photos)
4. **Unique Filenames**: Prevents file overwrites and naming conflicts

## File Naming Convention

Files are saved with unique names:
- Format: `file_{uniqid}_{timestamp}.{extension}`
- Example: `file_6933dc8291c7e7.50933677_1765006466.jpg`
- This ensures no conflicts even if multiple users upload simultaneously

## Notes

- Files are stored permanently on the server
- Database only stores paths, not file contents
- Each user has their own subdirectory for organization
- Files are not deleted automatically (you may want to implement cleanup for old/rejected applications)

