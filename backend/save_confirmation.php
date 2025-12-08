<?php
session_start();
header('Content-Type: application/json');

// Check if session is started (user clicked Start button)
if (!isset($_SESSION['session_started'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please start application first.']);
    exit();
}

// Collect POST/JSON data safely
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

$academicStatus     = $jsonData['academicStatus'] ?? $_POST['academicStatus'] ?? null;
$alreadyEnrolled    = $jsonData['alreadyEnrolled'] ?? $_POST['alreadyEnrolled'] ?? null;
$firstTimeApplying  = $jsonData['firstTimeApplying'] ?? $_POST['firstTimeApplying'] ?? null;
$transferred        = $jsonData['transferred'] ?? $_POST['transferred'] ?? null;
$transferredFrom    = $jsonData['transferredFrom'] ?? $_POST['transferredFrom'] ?? null;
$transferredYear    = $jsonData['transferredYear'] ?? $_POST['transferredYear'] ?? null;
$bsuGraduate        = $jsonData['bsuGraduate'] ?? $_POST['bsuGraduate'] ?? null;
$bsuSchool          = $jsonData['bsuSchool'] ?? $_POST['bsuSchool'] ?? null;

// Basic validation: make sure required radio fields are present
if (
    !$academicStatus ||
    !$alreadyEnrolled ||
    !$firstTimeApplying ||
    !$transferred ||
    !$bsuGraduate
) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please complete all required fields.']);
    exit();
}

// If user answered "yes" to transferred but left sub-fields empty, keep them as null
if ($transferred !== 'yes') {
    $transferredFrom = null;
    $transferredYear = null;
}

// If user is not a BSU graduate, clear the school field
if ($bsuGraduate !== 'yes') {
    $bsuSchool = null;
}

// If there is a year, append it to the "transferred_from" text so it still fits your table
if ($transferredFrom && $transferredYear) {
    $transferredFrom = $transferredFrom . ' (' . $transferredYear . ')';
}

// Store confirmation data in session instead of database
$_SESSION['confirmation_data'] = [
    'academic_status' => $academicStatus,
    'enrolled_in_college' => $alreadyEnrolled,
    'first_time_application' => $firstTimeApplying,
    'transferred_in_high_school' => $transferred,
    'transferred_from' => $transferredFrom,
    'graduate_of_batstateu' => $bsuGraduate,
    'graduate_from' => $bsuSchool,
];

echo json_encode(['success' => true]);

?>


