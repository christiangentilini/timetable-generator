<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

// Abilita la visualizzazione degli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se l'utente è admin
$stmt = $conn->prepare("SELECT type FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['type'] !== 'admin') {
    error_log("Tentativo di creazione utente da non admin");
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug dei dati ricevuti
    error_log("Dati POST ricevuti: " . print_r($_POST, true));
    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $type = $_POST['type'];
    
    // Validazione dei campi obbligatori
    if (empty($username) || empty($email) || empty($nome) || empty($cognome) || empty($type)) {
        error_log("Campi obbligatori mancanti");
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Tutti i campi sono obbligatori']);
        exit;
    }
    
    // Genera una password temporanea numerica di 8 cifre
    $temp_password = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    
    // Debug dei dati preparati
    error_log("Username: $username");
    error_log("Email: $email");
    error_log("Nome: $nome");
    error_log("Cognome: $cognome");
    error_log("Type: $type");
    error_log("Temp Password: $temp_password");
    
    // Verifica se username o email esistono già
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        error_log("Username o email già in uso");
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Username o email già in uso']);
        exit;
    }
    
    // Inserisci il nuovo utente
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, nome, cognome, type, temp_password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Errore nella preparazione della query: " . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore nella preparazione della query: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("sssssss", $username, $email, $hashed_password, $nome, $cognome, $type, $temp_password);
    
    if ($stmt->execute()) {
        error_log("Utente creato con successo");
        // Verifica che l'utente sia stato effettivamente creato
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Utente creato con successo',
                'temp_password' => $temp_password
            ]);
        } else {
            error_log("Errore: Utente non trovato dopo l'inserimento");
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Errore durante la verifica della creazione dell\'utente']);
        }
    } else {
        error_log("Errore SQL: " . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore durante la creazione dell\'utente: ' . $conn->error]);
    }
} 