<?php
require_once '../config/database.php';
require_once '../config/session_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['field']) || !isset($input['value']) || !isset($input['timetable_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$allowed_fields = ['titolo', 'sottotitolo', 'desc1', 'desc2', 'disclaimer'];
$field = $input['field'];
$value = $input['value'];
$timetable_id = (int)$input['timetable_id'];

if (!in_array($field, $allowed_fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid field']);
    exit;
}

// Verify user owns this timetable
$stmt = $conn->prepare("SELECT user_created FROM timetables WHERE id = ?");
$stmt->bind_param("i", $timetable_id);
$stmt->execute();
$result = $stmt->get_result();
$timetable = $result->fetch_assoc();
$stmt->close();

if (!$timetable || $timetable['user_created'] !== $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Update the field
$stmt = $conn->prepare("UPDATE timetables SET $field = ? WHERE id = ? AND user_created = ?");
$stmt->bind_param("sii", $value, $timetable_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update']);
}

$stmt->close();
$conn->close();