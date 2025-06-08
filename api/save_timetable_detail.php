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
    error_log('Loading timetable details for ID: ' . $data['timetable_id']);
    
    $select_stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY order_number, time_slot");
    $select_stmt->bind_param("i", $data['timetable_id']);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    
    error_log('Found ' . count($rows) . ' rows');
    
    echo json_encode(['success' => true, 'rows' => $rows]);
    exit;
}

// Handle delete request
if ($data['entry_type'] === 'delete') {
    if (!isset($data['row_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing row ID']);
        exit;
    }
    
    // Validate timetable access permissions for delete operation
    $can_edit = false;

    // Check if user is the owner
    $stmt = $conn->prepare("SELECT id FROM timetables WHERE id = ? AND user_created = ?");
    $stmt->bind_param("ii", $data['timetable_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $can_edit = true;
    } else {
        // Check if user has edit permission through sharing
        $stmt = $conn->prepare("SELECT id FROM timetable_shares WHERE timetable_id = ? AND user_id = ? AND permission_level = 'edit'");
        $stmt->bind_param("ii", $data['timetable_id'], $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $can_edit = true;
        }
    }

    if (!$can_edit) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized access: You do not have permission to delete entries']);
        exit;
    }
    
    $delete_stmt = $conn->prepare("DELETE FROM timetable_details WHERE id = ? AND timetable_id = ?");
    $delete_stmt->bind_param("ii", $data['row_id'], $data['timetable_id']);
    
    if ($delete_stmt->execute()) {
        $select_stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY order_number, time_slot");
        $select_stmt->bind_param("i", $data['timetable_id']);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'rows' => $rows]);
    } else {
        echo json_encode(['success' => false, 'error' => $delete_stmt->error]);
    }
    exit;
}

if (!isset($data['timetable_id']) || !isset($data['entry_type'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Validate timetable access permissions
$can_edit = false;

// Check if user is the owner
$stmt = $conn->prepare("SELECT id FROM timetables WHERE id = ? AND user_created = ?");
$stmt->bind_param("ii", $data['timetable_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $can_edit = true;
} else {
    // Check if user has edit permission through sharing
    $stmt = $conn->prepare("SELECT id FROM timetable_shares WHERE timetable_id = ? AND user_id = ? AND permission_level = 'edit'");
    $stmt->bind_param("ii", $data['timetable_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $can_edit = true;
    }
}

if (!$can_edit) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Prepare SQL based on entry type
// Get the maximum order_number for this timetable
$max_order_stmt = $conn->prepare("SELECT COALESCE(MAX(order_number), -1) as max_order FROM timetable_details WHERE timetable_id = ?");
$max_order_stmt->bind_param("i", $data['timetable_id']);
$max_order_stmt->execute();
$max_order_result = $max_order_stmt->get_result();
$next_order = $max_order_result->fetch_assoc()['max_order'] + 1;
$max_order_stmt->close();

if ($data['entry_type'] === 'descriptive') {
    $sql = "INSERT INTO timetable_details (timetable_id, entry_type, time_slot, description, order_number) VALUES (?, ?, ?, ?, ?)"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssi", $data['timetable_id'], $data['entry_type'], $data['time_slot'], $data['description'], $next_order);
} else {
    $sql = "INSERT INTO timetable_details (timetable_id, entry_type, time_slot, discipline, category, class_name, type, turn, da, a, balli, batterie, pannello, order_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssssssssi", 
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
        $data['batterie'],
        $data['pannello'],
        $next_order
    );
}

if ($stmt->execute()) {
    // Get all timetable details ordered by time_slot
    $select_stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY order_number, time_slot");
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