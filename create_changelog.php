<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

// Abilita la visualizzazione degli errori per il debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log dei dati ricevuti
    error_log("POST data ricevuto: " . print_r($_POST, true));
    
    $title = $_POST['title'];
    $version = $_POST['version'];
    $date = $_POST['date'];
    $items = isset($_POST['items']) ? $_POST['items'] : [];
    
    // Log dei dati elaborati
    error_log("Dati elaborati:");
    error_log("Title: " . $title);
    error_log("Version: " . $version);
    error_log("Date: " . $date);
    error_log("Items: " . print_r($items, true));
    
    // Rimuovi gli item vuoti
    $items = array_filter($items, function($item) {
        return trim($item) !== '';
    });
    
    // Log degli item filtrati
    error_log("Items filtrati: " . print_r($items, true));
    
    // Inizia la transazione
    $conn->begin_transaction();
    
    try {
        // Inserisci la nota di rilascio
        $stmt = $conn->prepare("INSERT INTO changelog (title, version, date) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Errore nella preparazione della query: " . $conn->error);
        }
        
        $stmt->bind_param("sss", $title, $version, $date);
        if (!$stmt->execute()) {
            throw new Exception("Errore nell'esecuzione della query: " . $stmt->error);
        }
        
        $changelog_id = $conn->insert_id;
        error_log("Changelog ID creato: " . $changelog_id);
        
        // Inserisci gli item
        $stmt = $conn->prepare("INSERT INTO changelog_data (version_id, item) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Errore nella preparazione della query degli item: " . $conn->error);
        }
        
        foreach ($items as $item) {
            $stmt->bind_param("is", $changelog_id, $item);
            if (!$stmt->execute()) {
                throw new Exception("Errore nell'inserimento dell'item: " . $stmt->error);
            }
        }
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Errore durante il salvataggio: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore durante il salvataggio: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
} 