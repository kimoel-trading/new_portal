<?php
session_start();
header('Content-Type: application/json');

// Check if session is started (user clicked Start button)
if (!isset($_SESSION['session_started'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session not started.']);
    exit();
}

echo json_encode([
    'success' => true,
    'session_started' => true,
    'user_id' => isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
    'application_number' => isset($_SESSION['application_number']) ? $_SESSION['application_number'] : null
]);
?>

