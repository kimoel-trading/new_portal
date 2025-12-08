<?php
session_start();
header('Content-Type: application/json');

// Check if session is started (user clicked Start button)
if (!isset($_SESSION['session_started'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please start application first.']);
    exit();
}

// Get selected AAP option (support both POST and JSON)
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);
$aapChoice = $jsonData['aap'] ?? $_POST['aap'] ?? null;

if (!$aapChoice) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please select an AAP option.']);
    exit();
}

// Default all flags to 'no'
$is_indigent_student          = 'no';
$is_als_graduate             = 'no';
$is_indigenous_people        = 'no';
$is_pwd                      = 'no';
$is_iscolar_ng_bayan         = 'no';
$is_children_of_solo_parent  = 'no';
$is_batstateu_lab_graduate   = 'no';

// Turn on the one corresponding to the selection (if not "none")
switch ($aapChoice) {
    case 'indigent':
        $is_indigent_student = 'yes';
        break;
    case 'als':
        $is_als_graduate = 'yes';
        break;
    case 'indigenous':
        $is_indigenous_people = 'yes';
        break;
    case 'pwd':
        $is_pwd = 'yes';
        break;
    case 'iskolar':
        $is_iscolar_ng_bayan = 'yes';
        break;
    case 'solo-parent':
        $is_children_of_solo_parent = 'yes';
        break;
    case 'lab-school':
        $is_batstateu_lab_graduate = 'yes';
        break;
    case 'none':
    default:
        // leave all as 'no'
        break;
}

// Store AAP data in session instead of database
$_SESSION['aap_data'] = [
    'aap_choice' => $aapChoice,
    'is_indigent_student' => $is_indigent_student,
    'is_als_graduate' => $is_als_graduate,
    'is_indigenous_people' => $is_indigenous_people,
    'is_pwd' => $is_pwd,
    'is_iscolar_ng_bayan' => $is_iscolar_ng_bayan,
    'is_children_of_solo_parent' => $is_children_of_solo_parent,
    'is_batstateu_lab_graduate' => $is_batstateu_lab_graduate,
];

echo json_encode(['success' => true]);

?>


