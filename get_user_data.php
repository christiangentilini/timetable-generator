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
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID utente non specificato']);
    exit;
}

$user_id = $_GET['id'];

// Recupera i dati dell'utente
$stmt = $conn->prepare("SELECT id, username, email, nome, cognome, type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Utente non trovato']);
    exit;
}

$user_data = $result->fetch_assoc();

// Rimuovi eventuali dati sensibili
unset($user_data['password']);

header('Content-Type: application/json');
echo json_encode($user_data); 