<?php
require_once '../config/database.php';
require_once '../config/session_check.php';

header('Content-Type: application/json');

// Verifica che la richiesta sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

// Leggi i dati JSON dalla richiesta
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID cronologico mancante']);
    exit;
}

$timetable_id = (int)$input['id'];
$user_id = $_SESSION['user_id'];

// Inizia transazione
$conn->begin_transaction();

try {
    // 1. Verifica che l'utente sia il proprietario del cronologico
    $stmt = $conn->prepare("SELECT * FROM timetables WHERE id = ? AND user_created = ?");
    $stmt->bind_param("ii", $timetable_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $timetable = $result->fetch_assoc();
    $stmt->close();

    if (!$timetable) {
        throw new Exception('Non sei il proprietario di questo cronologico');
    }

    // 2. Elimina i dettagli del cronologico
    $stmt = $conn->prepare("DELETE FROM timetable_details WHERE timetable_id = ?");
    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();
    $stmt->close();

    // 3. Elimina le condivisioni del cronologico
    $stmt = $conn->prepare("DELETE FROM timetable_shares WHERE timetable_id = ?");
    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();
    $stmt->close();

    // 4. Elimina il cronologico principale
    $stmt = $conn->prepare("DELETE FROM timetables WHERE id = ?");
    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();
    $stmt->close();

    // Conferma transazione
    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Annulla transazione in caso di errore
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>