<?php
require_once '../config/database.php';
require_once '../config/session_check.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$timetable_id = isset($data['timetable_id']) ? (int)$data['timetable_id'] : 0;
$row_id = isset($data['row_id']) ? (int)$data['row_id'] : 0;

if (!$timetable_id || !$row_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Get the row to duplicate
$stmt = $conn->prepare("SELECT * FROM timetable_details WHERE id = ? AND timetable_id = ?");
$stmt->bind_param("ii", $row_id, $timetable_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'error' => 'Row not found']);
    exit;
}

// Get the maximum order value and increment all higher orders
$conn->begin_transaction();

try {
    // Get the current row's order
    $current_order = $row['order_number'];
    
    // Increment orders of all rows after the current one
    $stmt = $conn->prepare("UPDATE timetable_details SET order_number = order_number + 1 WHERE timetable_id = ? AND order_number > ?");
    $stmt->bind_param("ii", $timetable_id, $current_order);
    $stmt->execute();
    $stmt->close();
    
    // The new row will be inserted right after the current one
    $new_order = $current_order + 1;

    // Insert the duplicated row
    $stmt = $conn->prepare("INSERT INTO timetable_details (timetable_id, entry_type, time_slot, description, discipline, category, class_name, type, turn, da, a, balli, batterie, order_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssssssssi", 
        $timetable_id,
        $row['entry_type'],
        $row['time_slot'],
        $row['description'],
        $row['discipline'],
        $row['category'],
        $row['class_name'],
        $row['type'],
        $row['turn'],
        $row['da'],
        $row['a'],
        $row['balli'],
        $row['batterie'],
        $new_order
    );

    if ($stmt->execute()) {
        // Fetch all rows to return updated list
        $stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY order_number");
        $stmt->bind_param("i", $timetable_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        $conn->commit();
        echo json_encode(['success' => true, 'rows' => $rows]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Failed to duplicate row']);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();