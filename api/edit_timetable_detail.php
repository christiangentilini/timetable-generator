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

// Validate timetable ownership
$stmt = $conn->prepare("SELECT id FROM timetables WHERE id = ? AND user_created = ?");
$stmt->bind_param("ii", $timetable_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Update the row
try {
    if ($data['entry_type'] === 'descriptive') {
        $stmt = $conn->prepare("UPDATE timetable_details SET time_slot = ?, description = ?, entry_type = ? WHERE id = ? AND timetable_id = ?");
        $stmt->bind_param("sssii", 
            $data['time_slot'],
            $data['description'],
            $data['entry_type'],
            $row_id,
            $timetable_id
        );
    } else {
        // Ensure empty values are stored as empty strings instead of NULL
        $pannello = isset($data['pannello']) ? $data['pannello'] : '';
        
        $stmt = $conn->prepare("UPDATE timetable_details SET time_slot = ?, discipline = ?, category = ?, class_name = ?, type = ?, turn = ?, da = ?, a = ?, balli = ?, batterie = ?, pannello = ?, entry_type = ? WHERE id = ? AND timetable_id = ?");
        $stmt->bind_param("ssssssssssssii", 
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
            $pannello,
            $data['entry_type'],
            $row_id,
            $timetable_id
        );
    }

    if ($stmt->execute()) {
        // Fetch all rows to return updated list
        $stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY order_number");
        $stmt->bind_param("i", $timetable_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        echo json_encode(['success' => true, 'rows' => $rows]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update row']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();