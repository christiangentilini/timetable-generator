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
    
    // Recupera i dati dell'utente
    $stmt = $conn->prepare("SELECT id, username, email, nome, cognome, type FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    if ($user_data) {
        echo json_encode($user_data);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Utente non trovato']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID non specificato']);
}