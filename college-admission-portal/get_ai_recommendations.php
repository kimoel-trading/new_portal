<?php
// Return AI recommendations from session
session_start();

header('Content-Type: application/json');

// Debug logging
error_log("get_ai_recommendations.php called");
error_log("Session data: " . print_r($_SESSION, true));

if (isset($_SESSION['ai_recommendations'])) {
    $response = [
        'success' => true,
        'recommendations' => $_SESSION['ai_recommendations'],
        'questionnaire_data' => $_SESSION['questionnaire_data'] ?? null
    ];

    error_log("Returning AI recommendations: " . json_encode($response));

    echo json_encode($response);

    // Clear the session data after sending it
    unset($_SESSION['ai_recommendations']);
    unset($_SESSION['questionnaire_data']);
} else {
    error_log("No AI recommendations found in session");
    echo json_encode([
        'success' => false,
        'message' => 'No AI recommendations found'
    ]);
}
?>
