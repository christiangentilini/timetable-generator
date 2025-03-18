<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

// Verifica se l'utente è admin
$stmt = $conn->prepare("SELECT type FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Recupera i dati della nota di rilascio
    $stmt = $conn->prepare("SELECT * FROM changelog WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $changelog = $result->fetch_assoc();
    
    if ($changelog) {
        // Recupera gli item
        $stmt = $conn->prepare("SELECT item FROM changelog_data WHERE version_id = ? ORDER BY id");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row['item'];
        }
        
        $changelog['items'] = $items;
        echo json_encode($changelog);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Nota di rilascio non trovata']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID non specificato']);
} 