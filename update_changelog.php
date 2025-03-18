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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $version = $_POST['version'];
    $date = $_POST['date'];
    $items = $_POST['items'];
    
    // Rimuovi le righe vuote
    $items = array_filter(explode("\n", $items), function($item) {
        return trim($item) !== '';
    });
    
    // Inizia la transazione
    $conn->begin_transaction();
    
    try {
        // Aggiorna la nota di rilascio
        $stmt = $conn->prepare("UPDATE changelog SET title = ?, version = ?, date = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $version, $date, $id);
        $stmt->execute();
        
        // Elimina gli item esistenti
        $stmt = $conn->prepare("DELETE FROM changelog_data WHERE version_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Inserisci i nuovi item
        $stmt = $conn->prepare("INSERT INTO changelog_data (version_id, item) VALUES (?, ?)");
        foreach ($items as $item) {
            $stmt->bind_param("is", $id, $item);
            $stmt->execute();
        }
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore durante l\'aggiornamento']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
} 