<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session_check.php';

// Decode JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate data
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

// Handle load request
if ($data['entry_type'] === 'load') {
    $select_stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY time_slot");
    $select_stmt->bind_param("i", $data['timetable_id']);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'rows' => $rows]);
    exit;
}

if (!isset($data['timetable_id']) || !isset($data['entry_type'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
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

// Prepare SQL based on entry type
if ($data['entry_type'] === 'descriptive') {
    $sql = "INSERT INTO timetable_details (timetable_id, entry_type, time_slot, description) VALUES (?, ?, ?, ?)"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $data['timetable_id'], $data['entry_type'], $data['time_slot'], $data['description']);
} else {
    $sql = "INSERT INTO timetable_details (timetable_id, entry_type, time_slot, discipline, category, class_name, type, turn, da, a, balli, batterie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssssss", 
        $data['timetable_id'], 
        $data['entry_type'], 
        $data['time_slot'],
        $data['discipline'],
        $data['category'],
        $data['class_name'],
        $data['type'],
        $data['turn'],
        $data['da'],
        $data['a'],
        $data['balli'],
        $data['batterie']
    );
}

if ($stmt->execute()) {
    // Get all timetable details ordered by time_slot
    $select_stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY time_slot");
    $select_stmt->bind_param("i", $data['timetable_id']);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'rows' => $rows]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();