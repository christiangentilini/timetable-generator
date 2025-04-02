<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session_check.php';

// Decode JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate data
if (!$data || !isset($data['timetable_id']) || !isset($data['row_ids']) || !is_array($data['row_ids']) || empty($data['row_ids'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data or missing required fields']);
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

// Handle different update operations
$operation = isset($data['operation']) ? $data['operation'] : '';

// Start transaction
$conn->begin_transaction();

try {
    switch ($operation) {
        case 'update_pannello':
            // Update pannello for multiple rows
            if (!isset($data['pannello'])) {
                throw new Exception('Missing pannello value');
            }
            
            $pannello = $data['pannello'];
            $row_ids = $data['row_ids'];
            $success = true;
            
            // Prepare statement outside the loop for better performance
            $update_stmt = $conn->prepare("UPDATE timetable_details SET pannello = ? WHERE id = ? AND timetable_id = ? AND entry_type = 'normal'");
            
            foreach ($row_ids as $row_id) {
                $update_stmt->bind_param("sii", $pannello, $row_id, $data['timetable_id']);
                if (!$update_stmt->execute()) {
                    $success = false;
                    break;
                }
            }
            
            if (!$success) {
                throw new Exception('Failed to update pannello for some rows');
            }
            break;
            
        case 'batch_edit':
            // Update multiple fields for multiple rows
            if (!isset($data['fields']) || !is_array($data['fields'])) {
                throw new Exception('Missing or invalid fields data');
            }
            
            $fields = $data['fields'];
            $row_ids = $data['row_ids'];
            $success = true;
            
            // Build dynamic SQL based on provided fields
            $update_fields = [];
            $param_types = '';
            $params = [];
            
            // Only include fields that are provided
            if (isset($fields['discipline'])) {
                $update_fields[] = "discipline = ?";
                $param_types .= 's';
                $params[] = $fields['discipline'];
            }
            
            if (isset($fields['category'])) {
                $update_fields[] = "category = ?";
                $param_types .= 's';
                $params[] = $fields['category'];
            }
            
            if (isset($fields['class_name'])) {
                $update_fields[] = "class_name = ?";
                $param_types .= 's';
                $params[] = $fields['class_name'];
            }
            
            if (isset($fields['type'])) {
                $update_fields[] = "type = ?";
                $param_types .= 's';
                $params[] = $fields['type'];
            }
            
            if (isset($fields['turn'])) {
                $update_fields[] = "turn = ?";
                $param_types .= 's';
                $params[] = $fields['turn'];
            }
            
            if (isset($fields['balli'])) {
                $update_fields[] = "balli = ?";
                $param_types .= 's';
                $params[] = $fields['balli'];
            }
            
            if (isset($fields['batterie'])) {
                $update_fields[] = "batterie = ?";
                $param_types .= 's';
                $params[] = $fields['batterie'];
            }
            
            if (isset($fields['pannello'])) {
                $update_fields[] = "pannello = ?";
                $param_types .= 's';
                $params[] = $fields['pannello'];
            }
            
            // If no fields to update, return error
            if (empty($update_fields)) {
                throw new Exception('No fields to update');
            }
            
            // Create SQL statement
            $sql = "UPDATE timetable_details SET " . implode(", ", $update_fields) . " WHERE id = ? AND timetable_id = ? AND entry_type = 'normal'";
            
            // Add row_id and timetable_id to param types and params
            $param_types .= 'ii';
            
            // Execute for each row
            $update_stmt = $conn->prepare($sql);
            
            foreach ($row_ids as $row_id) {
                // Create a copy of params array and add row_id and timetable_id
                $row_params = $params;
                $row_params[] = $row_id;
                $row_params[] = $data['timetable_id'];
                
                // Create reference array for bind_param
                $ref_params = [];
                $ref_params[] = $param_types;
                
                foreach ($row_params as $key => $value) {
                    $ref_params[] = &$row_params[$key];
                }
                
                // Call bind_param with references
                call_user_func_array([$update_stmt, 'bind_param'], $ref_params);
                
                if (!$update_stmt->execute()) {
                    $success = false;
                    break;
                }
            }
            
            if (!$success) {
                throw new Exception('Failed to update some rows');
            }
            break;
            
        default:
            throw new Exception('Invalid operation');
    }
    
    // Commit transaction
    $conn->commit();
    
    // Get all timetable details ordered by order_number
    $select_stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY order_number, time_slot");
    $select_stmt->bind_param("i", $data['timetable_id']);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'rows' => $rows]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();