<?php
/**
 * File Viewer/Downloader Script
 * 
 * This script allows authorized access to view/download files uploaded by applicants.
 * 
 * Usage:
 *   - View file: view_file.php?file=uploads/user_1/file_xxx.jpg
 *   - Download file: view_file.php?file=uploads/user_1/file_xxx.jpg&download=1
 *   - By user_id: view_file.php?user_id=1&type=student_id
 *   - By application_number: view_file.php?app_no=2026123456&type=grades_form_1
 */

session_start();
require_once 'db_connection.php';

// Security: Check if user is logged in as admin (modify based on your auth system)
// Uncomment and modify based on your authentication system:
/*
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    die('Access denied. Admin login required.');
}
*/

// Get file path from query parameter
$filePath = $_GET['file'] ?? null;
$userId = $_GET['user_id'] ?? null;
$appNumber = $_GET['app_no'] ?? null;
$fileType = $_GET['type'] ?? null;
$download = isset($_GET['download']) && $_GET['download'] == '1';

// If file path is not directly provided, query database
if (!$filePath && ($userId || $appNumber) && $fileType) {
    $filePath = getFilePathFromDatabase($conn, $userId, $appNumber, $fileType);
}

if (!$filePath) {
    http_response_code(400);
    die('Invalid request. Provide file path, or user_id/app_no with type.');
}

// Security: Validate file path (prevent directory traversal)
$filePath = str_replace('..', '', $filePath); // Remove parent directory references
$filePath = ltrim($filePath, '/'); // Remove leading slashes

// Construct full file path
$fullPath = __DIR__ . '/../' . $filePath;

// Security: Ensure file is within uploads directory
$realUploadsPath = realpath(__DIR__ . '/../uploads/');
$realFilePath = realpath($fullPath);

if (!$realFilePath || strpos($realFilePath, $realUploadsPath) !== 0) {
    http_response_code(403);
    die('Access denied. Invalid file path.');
}

// Check if file exists
if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    die('File not found.');
}

// Get file info
$fileName = basename($fullPath);
$fileSize = filesize($fullPath);
$mimeType = getMimeType($fullPath);

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);

if ($download) {
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
} else {
    header('Content-Disposition: inline; filename="' . $fileName . '"');
}

// Security: Prevent caching of sensitive files
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output file
readfile($fullPath);
exit;

/**
 * Get file path from database based on user_id or application_number
 */
function getFilePathFromDatabase($conn, $userId, $appNumber, $fileType) {
    $fileTypeMap = [
        'student_id' => ['table' => 'personal', 'column' => 'student_id_image'],
        'grades_form_1' => ['table' => 'education_attachments', 'column' => 'grades_form_1'],
        'form_137_junior' => ['table' => 'education_attachments', 'column' => 'form_137_junior'],
        'form_137_senior' => ['table' => 'education_attachments', 'column' => 'form_137_senior'],
        'certificate_of_enrollment' => ['table' => 'education_attachments', 'column' => 'certificate_of_enrollment_path'],
    ];
    
    if (!isset($fileTypeMap[$fileType])) {
        return null;
    }
    
    $config = $fileTypeMap[$fileType];
    $table = $config['table'];
    $column = $config['column'];
    
    if ($userId) {
        $stmt = $conn->prepare("SELECT {$column} FROM {$table} WHERE user_id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
    } elseif ($appNumber) {
        $stmt = $conn->prepare("SELECT {$column} FROM {$table} WHERE user_id = (SELECT id FROM users WHERE application_number = ? LIMIT 1) LIMIT 1");
        $stmt->bind_param('s', $appNumber);
    } else {
        return null;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? $row[$column] : null;
}

/**
 * Get MIME type based on file extension
 */
function getMimeType($filePath) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}
?>

