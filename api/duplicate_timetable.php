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
    $original_timetable = $result->fetch_assoc();
    $stmt->close();

    if (!$original_timetable) {
        throw new Exception('Non sei il proprietario di questo cronologico');
    }

    // 2. Duplica il record principale nella tabella timetables
    $stmt = $conn->prepare("INSERT INTO timetables (user_created, titolo, sottotitolo, desc1, desc2, disclaimer, logo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $new_title = $original_timetable['titolo'] . ' (Copia)';
    $stmt->bind_param(
        "issssss",
        $user_id,
        $new_title,
        $original_timetable['sottotitolo'],
        $original_timetable['desc1'],
        $original_timetable['desc2'],
        $original_timetable['disclaimer'],
        $original_timetable['logo']
    );
    $stmt->execute();
    $new_timetable_id = $conn->insert_id;
    $stmt->close();

    // 3. Duplica i dettagli del cronologico (se presenti)
    // Prima verifica quali colonne esistono nella tabella
    $stmt = $conn->prepare("SHOW COLUMNS FROM timetable_details");
    $stmt->execute();
    $result = $stmt->get_result();
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    $stmt->close();
    
    // Costruisci la query dinamicamente in base alle colonne esistenti
    $select_fields = [];
    $insert_fields = [];
    
    foreach ($columns as $column) {
        if ($column !== 'id' && $column !== 'timetable_id') { // Escludi la colonna id e timetable_id
            $insert_fields[] = $column;
            $select_fields[] = $column;
        }
    }
    
    $insert_fields_str = implode(', ', $insert_fields);
    $select_fields_str = implode(', ', $select_fields);
    
    $stmt = $conn->prepare("INSERT INTO timetable_details (timetable_id, $insert_fields_str) SELECT ?, $select_fields_str FROM timetable_details WHERE timetable_id = ?");
    
    $stmt->bind_param("ii", $new_timetable_id, $timetable_id);
    $stmt->execute();
    $stmt->close();

    // Conferma transazione
    $conn->commit();

    echo json_encode(['success' => true, 'new_id' => $new_timetable_id]);
} catch (Exception $e) {
    // Annulla transazione in caso di errore
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>