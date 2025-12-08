<?php
session_start();
header('Content-Type: application/json');

/**
 * For the requested flow, we don't create any database rows on "Start".
 * We simply mark the session as active so the front-end can proceed.
 * All records (user row, application_number, pin, and related data)
 * are created together in submit_application.php.
 */

if (!isset($_SESSION['session_started'])) {
    $_SESSION['session_started'] = true;
}

echo json_encode(['success' => true]);