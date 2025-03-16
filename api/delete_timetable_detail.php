<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session_check.php';

// Decode JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate data
if (!$data || !isset($data['row_id']) || !isset($data['timetable_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit;
}

// Validate timetable ownership
$stmt = $conn->prepare("SELECT id FROM timetables WHERE id = ? AND user_created = ?");
$stmt->bind_param("ii", $data['timetable_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Delete the row
$delete_stmt = $conn->prepare("DELETE FROM timetable_details WHERE id = ? AND timetable_id = ?");
$delete_stmt->bind_param("ii", $data['row_id'], $data['timetable_id']);

if ($delete_stmt->execute()) {
    // Get updated timetable details
    $select_stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY time_slot");
    $select_stmt->bind_param("i", $data['timetable_id']);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'rows' => $rows]);
} else {
    echo json_encode(['success' => false, 'error' => $delete_stmt->error]);
}

$delete_stmt->close();
$conn->close();