<?php
session_start();
header('Content-Type: application/json');

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

// Check if session is started (user clicked Start button)
if (!isset($_SESSION['session_started'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please start application first.']);
    exit();
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data || !isset($data['choices']) || !is_array($data['choices']) || empty($data['choices'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing program choices.']);
    exit();
}

// Store program choices in session instead of database
$_SESSION['program_choices'] = $data['choices'];

echo json_encode(['success' => true, 'message' => 'Program choices saved successfully.']);
?>
