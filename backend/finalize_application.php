<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expired. Please restart your application.']);
    exit();
}
$userId = (int)$_SESSION['user_id'];

// 1) Generate unique application number: 2026xxxxxx
function generateApplicationNumber(mysqli $conn) {
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

// 2) Generate PIN
$pin = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);

// 3) Save to users table
$appNo = generateApplicationNumber($conn);
$stmt = $conn->prepare("UPDATE users SET application_number = ?, pin = ? WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to finalize application.']);
    exit();
}
$stmt->bind_param('ssi', $appNo, $pin, $userId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to save application number.']);
    exit();
}
$stmt->close();

// Optionally: store in session so future pages can show it
$_SESSION['application_number'] = $appNo;

echo json_encode([
  'success' => true,
  'application_number' => $appNo,
  'pin' => $pin
]);