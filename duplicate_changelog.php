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
        // Recupera i dati della nota di rilascio originale
        $stmt = $conn->prepare("SELECT title, version, date FROM changelog WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $changelog = $result->fetch_assoc();
        
        // Recupera gli item
        $stmt = $conn->prepare("SELECT item FROM changelog_data WHERE version_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row['item'];
        }
        
        // Inserisci la nuova nota di rilascio
        $stmt = $conn->prepare("INSERT INTO changelog (title, version, date) VALUES (?, ?, ?)");
        $new_title = $changelog['title'] . ' (Copia)';
        $stmt->bind_param("sss", $new_title, $changelog['version'], $changelog['date']);
        $stmt->execute();
        $new_id = $conn->insert_id;
        
        // Inserisci gli item
        $stmt = $conn->prepare("INSERT INTO changelog_data (version_id, item) VALUES (?, ?)");
        foreach ($items as $item) {
            $stmt->bind_param("is", $new_id, $item);
            $stmt->execute();
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'id' => $new_id]);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore durante la duplicazione']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID non specificato']);
} 