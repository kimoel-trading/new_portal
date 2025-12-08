<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connection.php';

// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

set_exception_handler(function ($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $exception->getMessage()
    ]);
    exit();
});

// Get JSON input
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Helper function to save base64 file
    function saveBase64File($base64Data, $fileName, $applicationNumber, $documentType) {
        if (!$base64Data) return null;
        
        // Remove data URL prefix if present
        $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
        $base64Data = preg_replace('/^data:application\/pdf;base64,/', '', $base64Data);
        
        // Create directory structure: uploads/applicant_{applicationNumber}/{documentType}/
        $uploadDir = __DIR__ . '/../uploads/applicant_' . $applicationNumber . '/' . $documentType . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (!$extension) {
            // Determine extension from base64 data
            $extension = 'jpg'; // default
        }
        
        $uniqueFileName = 'file_' . uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $uniqueFileName;
        $relativePath = 'uploads/applicant_' . $applicationNumber . '/' . $documentType . '/' . $uniqueFileName;
        
        file_put_contents($filePath, base64_decode($base64Data));
        return $relativePath;
    }

    // Generate unique application number
    function generateApplicationNumber($conn) {
        $prefix = '2026';
        do {
            $suffix = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $appNo = $prefix . $suffix;
            $stmt = $conn->prepare("SELECT id FROM users WHERE application_number = ? LIMIT 1");
            $stmt->bind_param('s', $appNo);
            $stmt->execute();
            $res = $stmt->get_result();
            $exists = $res && $res->num_rows > 0;
            $stmt->close();
        } while ($exists);
        return $appNo;
    }

    // Helper function to bind parameters properly
    // CRITICAL: bind_param requires ALL parameters to be passed by reference
    // This method creates variables in the calling scope using a closure
    function bindParams($stmt, $types, $params) {
        // Create a temporary array to hold the values
        $temp = [];
        foreach ($params as $i => $value) {
            $temp[$i] = $value;
        }
        
        // Build arguments array with references
        $args = [$types];
        foreach ($temp as $key => $value) {
            $args[] = &$temp[$key];
        }
        
        // Use ReflectionClass to invoke bind_param with proper reference handling
        $reflection = new ReflectionClass('mysqli_stmt');
        $method = $reflection->getMethod('bind_param');
        
        // This should work - invokeArgs handles references correctly
        try {
            return $method->invokeArgs($stmt, $args);
        } catch (ReflectionException $e) {
            error_log("Reflection error: " . $e->getMessage());
            // Fallback: try call_user_func_array
            return call_user_func_array([$stmt, 'bind_param'], $args);
        }
    }

    // Generate PIN
    $pin = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    
    // 1. Create user record
    $appNo = generateApplicationNumber($conn);
    $stmt = $conn->prepare("INSERT INTO users (application_number, pin, role) VALUES (?, ?, 'student')");
    $stmt->bind_param('ss', $appNo, $pin);
    $stmt->execute();
    $userId = $conn->insert_id;
    $stmt->close();

    // 2. Prepare personal data
    $personal = $data['personal'] ?? [];
    $education = $data['education'] ?? [];
    $confirmation = $data['confirmation'] ?? [];
    $aap = $data['aap'] ?? [];
    $programs = $data['programs'] ?? [];
    
    // Save student ID image
    $picturePath = null;
    if (!empty($personal['student_id_image'])) {
        $picturePath = saveBase64File($personal['student_id_image'], 'student_id.jpg', $appNo, 'id_pic');
    }

    // Map disability value
    $disability = $personal['disability'] ?? 'None';
    if ($disability === '') $disability = 'None';

    // 3. Insert into applications table
    // Note: Excluding 'id' column as it's auto-increment
    // Check if table has 'user_id' column by querying structure
    $tableInfo = $conn->query("DESCRIBE applications");
    $tableColumns = [];
    $hasUserId = false;
    if ($tableInfo) {
        while ($row = $tableInfo->fetch_assoc()) {
            $tableColumns[] = $row['Field'];
            if ($row['Field'] === 'user_id') {
                $hasUserId = true;
            }
        }
    }
    
    // Build INSERT statement based on actual table structure
    // Note: Excluding 'id' (auto-increment) and 'submission_timestamp' (has default)
    if ($hasUserId) {
        // Table has user_id column - 74 columns total (excluding id and submission_timestamp)
        $insertSql = "INSERT INTO applications (
            user_id, application_no, application_status, last_name, first_name, middle_name, name_extension,
            birthdate, sex, nationality, height, address_region, address_province, address_city,
            address_barangay, address_street, email, mobile_no, telephone, emergency_contact_name,
            emergency_contact_address, emergency_contact_phone, emergency_contact_relationship,
            first_member_to_apply, recipient_of_4ps, member_of_indigenous_group, member_of_lgbtqia,
            internally_displaced_person, disability, solo_parent, father_name, mother_name,
            mother_maiden_name, father_age, mother_age, father_occupation, mother_occupation,
            father_contact_no, mother_contact_no, family_income_range, siblings_info, picture_path,
            shs_school_name, shs_email, school_type, shs_track, shs_strand, shs_completion_year,
            category_of_applicant, jhs_completion_year, specialization, jhs_math_grade, jhs_science_grade,
            jhs_english_grade, shs_sem1_math_subj, shs_sem1_math_grade, shs_sem1_science_subj, shs_sem1_science_grade,
            shs_sem1_english_subj, shs_sem1_english_grade, shs_sem2_math_subj, shs_sem2_math_grade,
            shs_sem2_science_subj, shs_sem2_science_grade, shs_sem2_english_subj, shs_sem2_english_grade,
            choice1_program, choice1_campus, choice2_program, choice2_campus, choice3_program, choice3_campus,
            evaluator_id, reviewer_comments
        ) VALUES (" . str_repeat("?, ", 73) . "?)";
        $expectedPlaceholders = 74;
    } else {
        // Table does NOT have user_id column - 73 columns total (excluding id and submission_timestamp)
        $insertSql = "INSERT INTO applications (
            application_no, application_status, last_name, first_name, middle_name, name_extension,
            birthdate, sex, nationality, height, address_region, address_province, address_city,
            address_barangay, address_street, email, mobile_no, telephone, emergency_contact_name,
            emergency_contact_address, emergency_contact_phone, emergency_contact_relationship,
            first_member_to_apply, recipient_of_4ps, member_of_indigenous_group, member_of_lgbtqia,
            internally_displaced_person, disability, solo_parent, father_name, mother_name,
            mother_maiden_name, father_age, mother_age, father_occupation, mother_occupation,
            father_contact_no, mother_contact_no, family_income_range, siblings_info, picture_path,
            shs_school_name, shs_email, school_type, shs_track, shs_strand, shs_completion_year,
            category_of_applicant, jhs_completion_year, specialization, jhs_math_grade, jhs_science_grade,
            jhs_english_grade, shs_sem1_math_subj, shs_sem1_math_grade, shs_sem1_science_subj, shs_sem1_science_grade,
            shs_sem1_english_subj, shs_sem1_english_grade, shs_sem2_math_subj, shs_sem2_math_grade,
            shs_sem2_science_subj, shs_sem2_science_grade, shs_sem2_english_subj, shs_sem2_english_grade,
            choice1_program, choice1_campus, choice2_program, choice2_campus, choice3_program, choice3_campus,
            evaluator_id, reviewer_comments
        ) VALUES (" . str_repeat("?, ", 72) . "?)";
        $expectedPlaceholders = 73;
    }
    
    $applicationStatus = 'For Evaluation';
    $lastName = $personal['last_name'] ?? '';
    $firstName = $personal['first_name'] ?? '';
    $middleName = $personal['middle_name'] ?? '';
    $nameExtension = $personal['name_extension'] ?? null;
    $birthdate = !empty($personal['birthdate']) ? $personal['birthdate'] : null;
    $sex = $personal['sex'] ?? '';
    $nationality = $personal['nationality'] ?? null;
    $height = !empty($personal['height']) ? (int)$personal['height'] : 0;
    $addressRegion = $personal['region'] ?? null;
    $addressProvince = $personal['province'] ?? null;
    $addressCity = $personal['city_municipality'] ?? null;
    $addressBarangay = $personal['barangay'] ?? null;
    $addressStreet = $personal['house_address'] ?? null;
    $email = $personal['email'] ?? null;
    $mobileNo = $personal['mobile_no'] ?? null;
    $telephone = $personal['telephone_no'] ?? '';
    $emergencyContactName = $personal['contact_name'] ?? null;
    $emergencyContactAddress = $personal['contact_address'] ?? null;
    $emergencyContactPhone = $personal['contact_mobile'] ?? null;
    $emergencyContactRelationship = $personal['contact_relationship'] ?? null;
    $firstMemberToApply = $personal['first_member_to_apply'] ?? 'No';
    $recipientOf4ps = $personal['recipient_of_4ps'] ?? 'No';
    $memberOfIndigenousGroup = $personal['member_of_indigenous_group'] ?? 'No';
    $memberOfLgbtqia = $personal['member_of_lgbtqia'] ?? 'Prefer not to say';
    $internallyDisplacedPerson = $personal['internally_displaced_person'] ?? 'No';
    $soloParent = $personal['solo_parent'] ?? 'No';
    $fatherName = $personal['father_name'] ?? '';
    $motherName = $personal['mother_name'] ?? '';
    $motherMaidenName = $personal['mother_maiden_name'] ?? null;
    $fatherAge = !empty($personal['father_age']) ? (int)$personal['father_age'] : 0;
    $motherAge = !empty($personal['mother_age']) ? (int)$personal['mother_age'] : 0;
    $fatherOccupation = $personal['father_occupation'] ?? null;
    $motherOccupation = $personal['mother_occupation'] ?? null;
    $fatherContactNo = $personal['father_contact_no'] ?? null;
    $motherContactNo = $personal['mother_contact_no'] ?? null;
    $familyIncomeRange = $personal['family_income_range'] ?? '';
    $siblingsInfo = $personal['siblings_info'] ?? null;
    $shsSchoolName = $education['shs'] ?? null;
    $shsEmail = $education['shs_email'] ?? null;
    $schoolType = $education['school_type'] ?? null;
    $shsTrack = $education['track'] ?? null;
    $shsStrand = $education['strand'] ?? null;
    $shsCompletionYear = !empty($education['senior_hs_completion_year']) ? (int)$education['senior_hs_completion_year'] : null;
    $categoryOfApplicant = $education['category_of_applicant'] ?? null;
    $jhsCompletionYear = !empty($education['junior_hs_completion_year']) ? (int)$education['junior_hs_completion_year'] : null;
    $specialization = $education['specialization'] ?? null;
    $jhsMathGrade = !empty($education['final_grades']['math']) ? (float)$education['final_grades']['math'] : null;
    $jhsScienceGrade = !empty($education['final_grades']['science']) ? (float)$education['final_grades']['science'] : null;
    $jhsEnglishGrade = !empty($education['final_grades']['english']) ? (float)$education['final_grades']['english'] : null;
    
    // Program choices
    $choices = $programs['choices'] ?? [];
    $choice1Program = isset($choices[0]) ? ($choices[0]['program'] ?? null) : null;
    $choice1Campus = isset($choices[0]) ? ($choices[0]['campus'] ?? null) : null;
    $choice2Program = isset($choices[1]) ? ($choices[1]['program'] ?? null) : null;
    $choice2Campus = isset($choices[1]) ? ($choices[1]['campus'] ?? null) : null;
    $choice3Program = isset($choices[2]) ? ($choices[2]['program'] ?? null) : null;
    $choice3Campus = isset($choices[2]) ? ($choices[2]['campus'] ?? null) : null;

    // Map SHS semester grades
    $grade11Records = $education['grade_11_records'] ?? [];
    $shsSem1MathSubj = isset($grade11Records['s1_math']) ? ($grade11Records['s1_math']['priority'] ?? null) : null;
    $shsSem1MathGrade = isset($grade11Records['s1_math']) && !empty($grade11Records['s1_math']['grade']) ? (float)$grade11Records['s1_math']['grade'] : null;
    $shsSem1ScienceSubj = isset($grade11Records['s1_science']) ? ($grade11Records['s1_science']['priority'] ?? null) : null;
    $shsSem1ScienceGrade = isset($grade11Records['s1_science']) && !empty($grade11Records['s1_science']['grade']) ? (float)$grade11Records['s1_science']['grade'] : null;
    $shsSem1EnglishSubj = isset($grade11Records['s1_english']) ? ($grade11Records['s1_english']['priority'] ?? null) : null;
    $shsSem1EnglishGrade = isset($grade11Records['s1_english']) && !empty($grade11Records['s1_english']['grade']) ? (float)$grade11Records['s1_english']['grade'] : null;
    $shsSem2MathSubj = isset($grade11Records['s2_math']) ? ($grade11Records['s2_math']['priority'] ?? null) : null;
    $shsSem2MathGrade = isset($grade11Records['s2_math']) && !empty($grade11Records['s2_math']['grade']) ? (float)$grade11Records['s2_math']['grade'] : null;
    $shsSem2ScienceSubj = isset($grade11Records['s2_science']) ? ($grade11Records['s2_science']['priority'] ?? null) : null;
    $shsSem2ScienceGrade = isset($grade11Records['s2_science']) && !empty($grade11Records['s2_science']['grade']) ? (float)$grade11Records['s2_science']['grade'] : null;
    $shsSem2EnglishSubj = isset($grade11Records['s2_english']) ? ($grade11Records['s2_english']['priority'] ?? null) : null;
    $shsSem2EnglishGrade = isset($grade11Records['s2_english']) && !empty($grade11Records['s2_english']['grade']) ? (float)$grade11Records['s2_english']['grade'] : null;

    // Add missing columns (evaluator_id and reviewer_comments) - both nullable
    $evaluatorId = null;
    $reviewerComments = null;

    // CRITICAL: Convert all values to strings to ensure consistent types
    // This helps avoid reference issues with mixed types
    // Convert userId to string since we're using 's' type in bind_param
    $userId = (string)$userId;
    $height = (string)$height;
    $fatherAge = (string)$fatherAge;
    $motherAge = (string)$motherAge;
    $shsCompletionYear = $shsCompletionYear ? (string)$shsCompletionYear : null;
    $jhsCompletionYear = $jhsCompletionYear ? (string)$jhsCompletionYear : null;
    $jhsMathGrade = $jhsMathGrade !== null ? (string)$jhsMathGrade : null;
    $jhsScienceGrade = $jhsScienceGrade !== null ? (string)$jhsScienceGrade : null;
    $jhsEnglishGrade = $jhsEnglishGrade !== null ? (string)$jhsEnglishGrade : null;
    $shsSem1MathGrade = $shsSem1MathGrade !== null ? (string)$shsSem1MathGrade : null;
    $shsSem1ScienceGrade = $shsSem1ScienceGrade !== null ? (string)$shsSem1ScienceGrade : null;
    $shsSem1EnglishGrade = $shsSem1EnglishGrade !== null ? (string)$shsSem1EnglishGrade : null;
    $shsSem2MathGrade = $shsSem2MathGrade !== null ? (string)$shsSem2MathGrade : null;
    $shsSem2ScienceGrade = $shsSem2ScienceGrade !== null ? (string)$shsSem2ScienceGrade : null;
    $shsSem2EnglishGrade = $shsSem2EnglishGrade !== null ? (string)$shsSem2EnglishGrade : null;

    // Build bind_param call - must handle references properly
    // Build variables array first, then create references array
    if ($hasUserId) {
        // Table has user_id - 74 parameters (73 + user_id)
        $bindTypes = str_repeat('s', 74);
        $variablesToBind = [
            $userId, $appNo, $applicationStatus, $lastName, $firstName, $middleName, $nameExtension,
            $birthdate, $sex, $nationality, $height, $addressRegion, $addressProvince, $addressCity,
            $addressBarangay, $addressStreet, $email, $mobileNo, $telephone, $emergencyContactName,
            $emergencyContactAddress, $emergencyContactPhone, $emergencyContactRelationship,
            $firstMemberToApply, $recipientOf4ps, $memberOfIndigenousGroup, $memberOfLgbtqia,
            $internallyDisplacedPerson, $disability, $soloParent, $fatherName, $motherName,
            $motherMaidenName, $fatherAge, $motherAge, $fatherOccupation, $motherOccupation,
            $fatherContactNo, $motherContactNo, $familyIncomeRange, $siblingsInfo, $picturePath,
            $shsSchoolName, $shsEmail, $schoolType, $shsTrack, $shsStrand, $shsCompletionYear,
            $categoryOfApplicant, $jhsCompletionYear, $specialization, $jhsMathGrade, $jhsScienceGrade,
            $jhsEnglishGrade, $shsSem1MathSubj, $shsSem1MathGrade, $shsSem1ScienceSubj, $shsSem1ScienceGrade,
            $shsSem1EnglishSubj, $shsSem1EnglishGrade, $shsSem2MathSubj, $shsSem2MathGrade,
            $shsSem2ScienceSubj, $shsSem2ScienceGrade, $shsSem2EnglishSubj, $shsSem2EnglishGrade,
            $choice1Program, $choice1Campus, $choice2Program, $choice2Campus, $choice3Program, $choice3Campus,
            $evaluatorId, $reviewerComments
        ];
    } else {
        // Table does NOT have user_id - 73 parameters
        $bindTypes = str_repeat('s', 73);
        $variablesToBind = [
            $appNo, $applicationStatus, $lastName, $firstName, $middleName, $nameExtension,
            $birthdate, $sex, $nationality, $height, $addressRegion, $addressProvince, $addressCity,
            $addressBarangay, $addressStreet, $email, $mobileNo, $telephone, $emergencyContactName,
            $emergencyContactAddress, $emergencyContactPhone, $emergencyContactRelationship,
            $firstMemberToApply, $recipientOf4ps, $memberOfIndigenousGroup, $memberOfLgbtqia,
            $internallyDisplacedPerson, $disability, $soloParent, $fatherName, $motherName,
            $motherMaidenName, $fatherAge, $motherAge, $fatherOccupation, $motherOccupation,
            $fatherContactNo, $motherContactNo, $familyIncomeRange, $siblingsInfo, $picturePath,
            $shsSchoolName, $shsEmail, $schoolType, $shsTrack, $shsStrand, $shsCompletionYear,
            $categoryOfApplicant, $jhsCompletionYear, $specialization, $jhsMathGrade, $jhsScienceGrade,
            $jhsEnglishGrade, $shsSem1MathSubj, $shsSem1MathGrade, $shsSem1ScienceSubj, $shsSem1ScienceGrade,
            $shsSem1EnglishSubj, $shsSem1EnglishGrade, $shsSem2MathSubj, $shsSem2MathGrade,
            $shsSem2ScienceSubj, $shsSem2ScienceGrade, $shsSem2EnglishSubj, $shsSem2EnglishGrade,
            $choice1Program, $choice1Campus, $choice2Program, $choice2Campus, $choice3Program, $choice3Campus,
            $evaluatorId, $reviewerComments
        ];
    }
    
    // Verify counts match
    if (count($variablesToBind) !== $expectedPlaceholders) {
        error_log("CRITICAL: Variable count mismatch! Expected $expectedPlaceholders, got " . count($variablesToBind));
        error_log("Table columns: " . implode(", ", $tableColumns));
        error_log("Has user_id: " . ($hasUserId ? 'yes' : 'no'));
        throw new Exception("Internal error: Variable count mismatch");
    }
    
    // Instead of using prepared statements with bind_param (which fails with 74 parameters),
    // we'll build the SQL with properly escaped values
    // This is less ideal but necessary for this many parameters
    function escapeValue($conn, $value) {
        if ($value === null) {
            return 'NULL';
        }
        return "'" . $conn->real_escape_string((string)$value) . "'";
    }
    
    // Build the VALUES clause with escaped values
    $values = [];
    foreach ($variablesToBind as $value) {
        $values[] = escapeValue($conn, $value);
    }
    
    // Build SQL by replacing placeholders one by one (more reliable than vsprintf)
    $finalSql = $insertSql;
    foreach ($values as $escapedValue) {
        $finalSql = preg_replace('/\?/', $escapedValue, $finalSql, 1);
    }
    
    // Debug: Log SQL execution
    error_log("DEBUG: About to execute INSERT");
    error_log("DEBUG: variablesToBind count: " . count($variablesToBind));
    error_log("DEBUG: values count: " . count($values));
    error_log("DEBUG: expectedPlaceholders: " . $expectedPlaceholders);
    error_log("DEBUG: hasUserId: " . ($hasUserId ? 'yes' : 'no'));
    error_log("DEBUG: SQL length: " . strlen($finalSql));
    
    // Execute the query directly
    $result = $conn->query($finalSql);
    if (!$result) {
        error_log("SQL Execute Error: " . $conn->error);
        error_log("SQL Error Number: " . $conn->errno);
        error_log("SQL (first 1000 chars): " . substr($finalSql, 0, 1000));
        throw new Exception("Failed to execute SQL statement: " . $conn->error);
    }
    
    $applicationId = $conn->insert_id;
    error_log("DEBUG: INSERT successful, applicationId: " . $applicationId);

    // Note: SHS semester grades are now included in the initial INSERT statement above
    // No need for separate UPDATE statement

    // 4. Insert educational_status
    error_log("DEBUG: About to insert educational_status");
    $stmt = $conn->prepare("INSERT INTO educational_status (
        user_id, academic_status, enrolled_in_college, first_time_application,
        transferred_in_high_school, transferred_from, graduate_of_batstateu, graduate_from
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $academicStatus = $confirmation['academic_status'] ?? '';
    $enrolledInCollege = $confirmation['already_enrolled'] ?? '';
    $firstTimeApplication = $confirmation['first_time_applying'] ?? '';
    $transferredInHighSchool = $confirmation['transferred'] ?? '';
    $transferredFrom = $confirmation['transferred_from'] ?? null;
    if ($transferredFrom && !empty($confirmation['transferred_year'])) {
        $transferredFrom = $transferredFrom . ' (' . $confirmation['transferred_year'] . ')';
    }
    $graduateOfBatstateu = $confirmation['bsu_graduate'] ?? '';
    $graduateFrom = $confirmation['bsu_school'] ?? null;
    $typeString = 'isssssss';
    error_log("DEBUG: educational_status type string length: " . strlen($typeString) . ", variables: 8");
    $stmt->bind_param($typeString, $userId, $academicStatus, $enrolledInCollege, $firstTimeApplication,
        $transferredInHighSchool, $transferredFrom, $graduateOfBatstateu, $graduateFrom);
    $stmt->execute();
    $stmt->close();
    error_log("DEBUG: educational_status inserted successfully");

    // 5. Insert AAP data
    $aapChoice = $aap['aap_choice'] ?? 'none';
    $isIndigentStudent = ($aapChoice === 'indigent') ? 'Yes' : 'No';
    $isAlsGraduate = ($aapChoice === 'als') ? 'Yes' : 'No';
    $isIndigenousPeople = ($aapChoice === 'indigenous') ? 'Yes' : 'No';
    $isPwd = ($aapChoice === 'pwd') ? 'Yes' : 'No';
    $isIscolarNgBayan = ($aapChoice === 'iskolar') ? 'Yes' : 'No';
    $isChildrenOfSoloParent = ($aapChoice === 'solo-parent') ? 'Yes' : 'No';
    $isBatstateuLabGraduate = ($aapChoice === 'lab-school') ? 'Yes' : 'No';
    
    $stmt = $conn->prepare("INSERT INTO aap (
        user_id, is_indigent_student, is_als_graduate, is_indigenous_people, is_pwd,
        is_iscolar_ng_bayan, is_children_of_solo_parent, is_batstateu_lab_graduate
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssssss', $userId, $isIndigentStudent, $isAlsGraduate, $isIndigenousPeople,
        $isPwd, $isIscolarNgBayan, $isChildrenOfSoloParent, $isBatstateuLabGraduate);
    $stmt->execute();
    $stmt->close();

    // 6. Insert program choices
    if (!empty($choices)) {
        $stmt = $conn->prepare("INSERT INTO program_choices (user_id, choice_number, program_name, campus_name) VALUES (?, ?, ?, ?)");
        foreach ($choices as $index => $choice) {
            $choiceNumber = $index + 1;
            $programName = $choice['program'] ?? '';
            $campusName = $choice['campus'] ?? '';
            if ($programName && $campusName) {
                $stmt->bind_param('iiss', $userId, $choiceNumber, $programName, $campusName);
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    // 7. Insert JHS final grades
    if ($jhsMathGrade !== null || $jhsScienceGrade !== null || $jhsEnglishGrade !== null) {
        $stmt = $conn->prepare("INSERT INTO jhs_final_grades (student_id, subject_name, grade_percentage) VALUES (?, ?, ?)");
        
        if ($jhsMathGrade !== null) {
            $subjectName = 'Mathematics';
            $stmt->bind_param('isi', $userId, $subjectName, $jhsMathGrade);
            $stmt->execute();
        }
        if ($jhsScienceGrade !== null) {
            $subjectName = 'Science';
            $stmt->bind_param('isi', $userId, $subjectName, $jhsScienceGrade);
            $stmt->execute();
        }
        if ($jhsEnglishGrade !== null) {
            $subjectName = 'English';
            $stmt->bind_param('isi', $userId, $subjectName, $jhsEnglishGrade);
            $stmt->execute();
        }
        $stmt->close();
    }

    // 8. Insert SHS final grades
    if (!empty($grade11Records)) {
        $stmt = $conn->prepare("INSERT INTO shs_final_grades (
            student_id, s1_math_priority, s1_math_alternative, s1_math_grade, s1_math_na,
            s1_science_priority, s1_science_alternative, s1_science_grade, s1_science_na,
            s1_english_priority, s1_english_alternative, s1_english_grade, s1_english_na,
            s2_math_priority, s2_math_alternative, s2_math_grade, s2_math_na,
            s2_science_priority, s2_science_alternative, s2_science_grade, s2_science_na,
            s2_english_priority, s2_english_alternative, s2_english_grade, s2_english_na
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $s1Math = $grade11Records['s1_math'] ?? [];
        $s1Science = $grade11Records['s1_science'] ?? [];
        $s1English = $grade11Records['s1_english'] ?? [];
        $s2Math = $grade11Records['s2_math'] ?? [];
        $s2Science = $grade11Records['s2_science'] ?? [];
        $s2English = $grade11Records['s2_english'] ?? [];
        
        // Extract all array elements to variables to avoid reference issues
        $s1MathPriority = $s1Math['priority'] ?? '';
        $s1MathAlternative = $s1Math['alternative'] ?? null;
        $s1MathGrade = $s1Math['grade'] ?? null;
        $s1MathNa = $s1Math['na'] ?? 0;
        $s1SciencePriority = $s1Science['priority'] ?? '';
        $s1ScienceAlternative = $s1Science['alternative'] ?? null;
        $s1ScienceGrade = $s1Science['grade'] ?? null;
        $s1ScienceNa = $s1Science['na'] ?? 0;
        $s1EnglishPriority = $s1English['priority'] ?? '';
        $s1EnglishAlternative = $s1English['alternative'] ?? null;
        $s1EnglishGrade = $s1English['grade'] ?? null;
        $s1EnglishNa = $s1English['na'] ?? 0;
        $s2MathPriority = $s2Math['priority'] ?? '';
        $s2MathAlternative = $s2Math['alternative'] ?? null;
        $s2MathGrade = $s2Math['grade'] ?? null;
        $s2MathNa = $s2Math['na'] ?? 0;
        $s2SciencePriority = $s2Science['priority'] ?? '';
        $s2ScienceAlternative = $s2Science['alternative'] ?? null;
        $s2ScienceGrade = $s2Science['grade'] ?? null;
        $s2ScienceNa = $s2Science['na'] ?? 0;
        $s2EnglishPriority = $s2English['priority'] ?? '';
        $s2EnglishAlternative = $s2English['alternative'] ?? null;
        $s2EnglishGrade = $s2English['grade'] ?? null;
        $s2EnglishNa = $s2English['na'] ?? 0;
        
        // Type string: i (userId) + 24 fields (each semester has 4 fields: priority(s), alternative(s), grade(s), na(i))
        // Pattern: i + (s+s+s+i) * 6 = i + 4*6 = 25 characters
        $typeString = 'i' . str_repeat('sssi', 6); // This creates: isssisssisssisssisssisssisssi
        $varCount = 25; // userId + 24 grade fields
        error_log("DEBUG: SHS final grades type string: '$typeString', length: " . strlen($typeString) . ", expected variables: " . $varCount);
        $stmt->bind_param($typeString,
            $userId,
            $s1MathPriority, $s1MathAlternative, $s1MathGrade, $s1MathNa,
            $s1SciencePriority, $s1ScienceAlternative, $s1ScienceGrade, $s1ScienceNa,
            $s1EnglishPriority, $s1EnglishAlternative, $s1EnglishGrade, $s1EnglishNa,
            $s2MathPriority, $s2MathAlternative, $s2MathGrade, $s2MathNa,
            $s2SciencePriority, $s2ScienceAlternative, $s2ScienceGrade, $s2ScienceNa,
            $s2EnglishPriority, $s2EnglishAlternative, $s2EnglishGrade, $s2EnglishNa
        );
        $stmt->execute();
        $stmt->close();
    }

    // 9. Insert siblings
    $siblingsInfoJson = $personal['siblings_info'] ?? '[]';
    $siblings = json_decode($siblingsInfoJson, true);
    if (is_array($siblings) && !empty($siblings)) {
        $stmt = $conn->prepare("INSERT INTO siblings (personal_id, full_name, age, educational_attainment, school, year_graduated, option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($siblings as $sibling) {
            $fullName = $sibling['fullname'] ?? '';
            $age = !empty($sibling['age']) ? (int)$sibling['age'] : 0;
            $educationalAttainment = $sibling['education'] ?? '';
            $school = $sibling['school'] ?? null;
            $yearGraduated = !empty($sibling['year']) ? (int)$sibling['year'] : null;
            $option = $sibling['option'] ?? 'N/A';
            if ($fullName) {
                $stmt->bind_param('isissis', $applicationId, $fullName, $age, $educationalAttainment, $school, $yearGraduated, $option);
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    // 10. Insert documents
    $files = $education['files'] ?? [];
    $fileMap = [
        1 => ['type' => 'Grades Form 1', 'column' => 'grades_form_1', 'folder' => 'grades_form_1'],
        2 => ['type' => 'JHS Form 137', 'column' => 'form_137_junior', 'folder' => 'form137_jhs'],
        3 => ['type' => 'SHS Form 137', 'column' => 'form_137_senior', 'folder' => 'form137_shs'],
        4 => ['type' => 'Certificate of Enrollment', 'column' => 'certificate_of_enrollment_path', 'folder' => 'certificate_of_enrollment']
    ];
    
    foreach ($fileMap as $fileNum => $fileInfo) {
        if (isset($files[$fileNum]) && !empty($files[$fileNum])) {
            $filePath = saveBase64File($files[$fileNum], 'document_' . $fileNum . '.pdf', $appNo, $fileInfo['folder']);
            if ($filePath) {
                $stmt = $conn->prepare("INSERT INTO documents (application_id, document_type, file_path, file_status) VALUES (?, ?, ?, 'For Review')");
                $documentType = $fileInfo['type']; // Extract to variable to avoid reference issue
                $stmt->bind_param('iss', $applicationId, $documentType, $filePath);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'application_number' => $appNo,
        'pin' => $pin,
        'message' => 'Application submitted successfully.'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit application: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

