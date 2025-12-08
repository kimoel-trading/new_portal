<?php
/**
 * List All Files for a User
 * 
 * This script lists all files uploaded by a specific user.
 * 
 * Usage:
 *   - By user_id: list_user_files.php?user_id=1
 *   - By application_number: list_user_files.php?app_no=2026781424
 *   - JSON output: list_user_files.php?user_id=1&format=json
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

$userId = $_GET['user_id'] ?? null;
$appNumber = $_GET['app_no'] ?? null;
$format = $_GET['format'] ?? 'html'; // 'html' or 'json'

if (!$userId && !$appNumber) {
    http_response_code(400);
    die('Invalid request. Provide user_id or app_no.');
}

// Get user_id from application_number if needed
if ($appNumber && !$userId) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE application_number = ? LIMIT 1");
    $stmt->bind_param('s', $appNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if (!$row) {
        http_response_code(404);
        die('User not found.');
    }
    
    $userId = $row['id'];
}

// Get user info
$stmt = $conn->prepare("SELECT id, application_number, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    die('User not found.');
}

// Get personal data (student_id_image)
$stmt = $conn->prepare("SELECT student_id_image FROM personal WHERE user_id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$personal = $result->fetch_assoc();
$stmt->close();

// Get education attachments
$stmt = $conn->prepare("SELECT grades_form_1, form_137_junior, form_137_senior, certificate_of_enrollment_path FROM education_attachments WHERE student_id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$education = $result->fetch_assoc();
$stmt->close();

// Compile file list
$files = [];

if ($personal && $personal['student_id_image']) {
    $files[] = [
        'type' => 'Student ID Photo',
        'path' => $personal['student_id_image'],
        'url' => 'view_file.php?file=' . urlencode($personal['student_id_image']),
        'download_url' => 'view_file.php?file=' . urlencode($personal['student_id_image']) . '&download=1',
        'exists' => file_exists(__DIR__ . '/../' . $personal['student_id_image'])
    ];
}

if ($education) {
    $fileTypes = [
        'grades_form_1' => 'Grades Form 1',
        'form_137_junior' => 'Form 137 (Junior High)',
        'form_137_senior' => 'Form 137 (Senior High)',
        'certificate_of_enrollment_path' => 'Certificate of Enrollment'
    ];
    
    foreach ($fileTypes as $column => $label) {
        if ($education[$column]) {
            $files[] = [
                'type' => $label,
                'path' => $education[$column],
                'url' => 'view_file.php?file=' . urlencode($education[$column]),
                'download_url' => 'view_file.php?file=' . urlencode($education[$column]) . '&download=1',
                'exists' => file_exists(__DIR__ . '/../' . $education[$column])
            ];
        }
    }
}

// Check for signature file (if stored separately)
$signatureDir = __DIR__ . '/../uploads/applicant_' . $user['application_number'] . '/signature/';
if (is_dir($signatureDir)) {
    $dirFiles = glob($signatureDir . '*.png');
    foreach ($dirFiles as $file) {
        $fileName = basename($file);
        // Check if it's a signature (usually PNG files that aren't in database)
        $relativePath = 'uploads/applicant_' . $user['application_number'] . '/signature/' . $fileName;
        $isInDatabase = false;
        foreach ($files as $f) {
            if ($f['path'] === $relativePath) {
                $isInDatabase = true;
                break;
            }
        }
        if (!$isInDatabase && pathinfo($file, PATHINFO_EXTENSION) === 'png') {
            $files[] = [
                'type' => 'Signature',
                'path' => $relativePath,
                'url' => 'view_file.php?file=' . urlencode($relativePath),
                'download_url' => 'view_file.php?file=' . urlencode($relativePath) . '&download=1',
                'exists' => true
            ];
        }
    }
}

// Output based on format
if ($format === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'user_id' => $user['id'],
        'application_number' => $user['application_number'],
        'created_at' => $user['created_at'],
        'files' => $files,
        'total_files' => count($files)
    ], JSON_PRETTY_PRINT);
} else {
    // HTML output
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Files for User <?php echo htmlspecialchars($user['application_number']); ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { border-collapse: collapse; width: 100%; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #4CAF50; color: white; }
            tr:nth-child(even) { background-color: #f2f2f2; }
            .exists { color: green; }
            .missing { color: red; }
            a { color: #2196F3; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <h1>Files for User: <?php echo htmlspecialchars($user['application_number']); ?></h1>
        <p><strong>User ID:</strong> <?php echo $user['id']; ?></p>
        <p><strong>Application Number:</strong> <?php echo htmlspecialchars($user['application_number']); ?></p>
        <p><strong>Created:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
        
        <h2>Uploaded Files (<?php echo count($files); ?>)</h2>
        <?php if (empty($files)): ?>
            <p>No files found for this user.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>File Type</th>
                        <th>File Path</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($file['type']); ?></td>
                            <td><code><?php echo htmlspecialchars($file['path']); ?></code></td>
                            <td class="<?php echo $file['exists'] ? 'exists' : 'missing'; ?>">
                                <?php echo $file['exists'] ? '✓ Exists' : '✗ Missing'; ?>
                            </td>
                            <td>
                                <a href="<?php echo htmlspecialchars($file['url']); ?>" target="_blank">View</a> |
                                <a href="<?php echo htmlspecialchars($file['download_url']); ?>">Download</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <p><a href="list_user_files.php?user_id=<?php echo $user['id']; ?>&format=json">View as JSON</a></p>
    </body>
    </html>
    <?php
}

$conn->close();
?>

