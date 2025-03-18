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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Inizia la transazione
    $conn->begin_transaction();
    
    try {
        // Elimina gli item associati
        $stmt = $conn->prepare("DELETE FROM changelog_data WHERE version_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Elimina la nota di rilascio
        $stmt = $conn->prepare("DELETE FROM changelog WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore durante l\'eliminazione']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID non specificato']);
} 