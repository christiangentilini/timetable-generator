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

if (!isset($_POST['user_id'], $_POST['username'], $_POST['email'], $_POST['nome'], $_POST['cognome'], $_POST['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

$user_id = $_POST['user_id'];
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$nome = trim($_POST['nome']);
$cognome = trim($_POST['cognome']);
$type = $_POST['type'];
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validazione dei dati
if (empty($username) || empty($email) || empty($nome) || empty($cognome)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tutti i campi sono obbligatori']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email non valida']);
    exit;
}

if ($type !== 'admin' && $type !== 'user') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tipo utente non valido']);
    exit;
}

// Verifica se l'username è già in uso
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$stmt->bind_param("si", $username, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Username già in uso']);
    exit;
}

// Verifica se l'email è già in uso
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
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, nome = ?, cognome = ?, type = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $username, $email, $nome, $cognome, $type, $hashed_password, $user_id);
} else {
    // Altrimenti aggiorna solo i dati senza la password
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, nome = ?, cognome = ?, type = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $username, $email, $nome, $cognome, $type, $user_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore durante l\'aggiornamento']);
}