<?php
require_once '../config/database.php';
require_once '../config/session_check.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false];

if (!isset($input['timetable_id']) || !isset($input['order_data']) || !is_array($input['order_data'])) {
    $response['error'] = 'Invalid input data';
    echo json_encode($response);
    exit;
}

// Validate timetable access permissions
$can_edit = false;

// Check if user is the owner
$stmt = $conn->prepare("SELECT id FROM timetables WHERE id = ? AND user_created = ?");
$stmt->bind_param("ii", $input['timetable_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $can_edit = true;
} else {
    // Check if user has edit permission through sharing
    $stmt = $conn->prepare("SELECT id FROM timetable_shares WHERE timetable_id = ? AND user_id = ? AND permission_level = 'edit'");
    $stmt->bind_param("ii", $input['timetable_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $can_edit = true;
    }
}

if (!$can_edit) {
    $response['error'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

try {
    $conn->begin_transaction();

    foreach ($input['order_data'] as $item) {
        if (!isset($item['id']) || !isset($item['order'])) {
            throw new Exception('Invalid order data structure');
        }

        $stmt = $conn->prepare("UPDATE timetable_details SET order_number = ? WHERE id = ? AND timetable_id = ?");
        $stmt->bind_param("iii", $item['order'], $item['id'], $input['timetable_id']);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    $response['success'] = true;

} catch (Exception $e) {
    $conn->rollback();
    $response['error'] = $e->getMessage();
}

echo json_encode($response);