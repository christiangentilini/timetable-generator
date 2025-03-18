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

if (!isset($_POST['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID utente non specificato']);
    exit;
}

$user_id = $_POST['user_id'];
$email = $_POST['email'];
$nome = $_POST['nome'];
$cognome = $_POST['cognome'];
$type = $_POST['type'];
$password = $_POST['password'];

// Verifica che l'email non sia già in uso da un altro utente
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email già in uso']);
    exit;
}

// Aggiorna i dati dell'utente
if (!empty($password)) {
    // Se è stata fornita una nuova password, aggiorna anche quella
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET email = ?, nome = ?, cognome = ?, type = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $email, $nome, $cognome, $type, $hashed_password, $user_id);
} else {
    // Altrimenti aggiorna solo i dati senza la password
    $stmt = $conn->prepare("UPDATE users SET email = ?, nome = ?, cognome = ?, type = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $email, $nome, $cognome, $type, $user_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore durante l\'aggiornamento']);
} 