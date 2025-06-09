<?php
require_once '../config/database.php';
require_once '../config/session_check.php';

// Disabilita l'output degli errori
error_reporting(0);
ini_set('display_errors', 0);

// Assicuriamoci che non ci sia output prima del JSON
ob_clean();
header('Content-Type: application/json');

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    if (!$json) {
        throw new Exception('Nessun dato ricevuto');
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Errore nel parsing dei dati JSON: ' . json_last_error_msg());
    }

    // Verifica la struttura dei dati
    if (!isset($data['timetable_id']) || !isset($data['csv_data']) || !isset($data['column_mapping'])) {
        throw new Exception('Struttura dati non valida');
    }

    $timetable_id = intval($data['timetable_id']);
    $csv_data = $data['csv_data'];
    $column_mapping = $data['column_mapping'];

    // Verifica i dati CSV
    if (!isset($csv_data['headers']) || !isset($csv_data['data']) || 
        !is_array($csv_data['headers']) || !is_array($csv_data['data'])) {
        throw new Exception('Dati CSV non validi');
    }

    // Verifica che tutti i campi obbligatori siano mappati
    $required_fields = ['disciplina', 'categoria', 'classe', 'tipo', 'da', 'balli'];
    foreach ($required_fields as $field) {
        if (!isset($column_mapping[$field]) || $column_mapping[$field] === '') {
            throw new Exception("Campo obbligatorio mancante: $field");
        }
    }

    // Ottieni la modalità balli
    $balli_mode = $data['balli_mode'] ?? 'numeric';

    // Verifica che il cronologico esista e che l'utente abbia i permessi
    $check_stmt = $conn->prepare("SELECT user_created FROM timetables WHERE id = ?");
    if (!$check_stmt) {
        throw new Exception('Errore nella preparazione della query di verifica: ' . $conn->error);
    }

    $check_stmt->bind_param("i", $timetable_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        $check_stmt->close();
        throw new Exception('Cronologico non trovato');
    }

    $timetable = $result->fetch_assoc();
    $check_stmt->close();
    
    // Verifica i permessi
    if ($timetable['user_created'] !== $_SESSION['user_id']) {
        $share_stmt = $conn->prepare("SELECT 1 FROM timetable_shares WHERE timetable_id = ? AND user_id = ? AND can_edit = 1");
        if (!$share_stmt) {
            throw new Exception('Errore nella preparazione della query di verifica permessi: ' . $conn->error);
        }

        $share_stmt->bind_param("ii", $timetable_id, $_SESSION['user_id']);
        $share_stmt->execute();
        if ($share_stmt->get_result()->num_rows === 0) {
            $share_stmt->close();
            throw new Exception('Non hai i permessi per modificare questo cronologico');
        }
        $share_stmt->close();
    }

    // Prepara la query per l'inserimento dei dettagli
    $insert_stmt = $conn->prepare("INSERT INTO timetable_details (timetable_id, discipline, category, class_name, type, da, balli) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$insert_stmt) {
        throw new Exception('Errore nella preparazione della query per i dettagli: ' . $conn->error);
    }

    // Inizia la transazione
    $conn->begin_transaction();

    try {
        $rows_imported = 0;
        $rows_skipped = 0;
        $errors = [];

        // Inserisci i dettagli del cronologico
        foreach ($csv_data['data'] as $row_index => $row) {
            try {
                // Estrai i valori mappati
                $disciplina = trim($row[$column_mapping['disciplina']] ?? '');
                $categoria = trim($row[$column_mapping['categoria']] ?? '');
                $classe = trim($row[$column_mapping['classe']] ?? '');
                $tipo = trim($row[$column_mapping['tipo']] ?? '');
                $da = trim($row[$column_mapping['da']] ?? '');
                $balli_raw = trim($row[$column_mapping['balli']] ?? '');

                // Gestione speciale per il campo balli
                $balli = $balli_raw;
                if ($balli_mode === 'text') {
                    // Conta i balli nel testo
                    $balli = count(array_filter(array_map('trim', explode(',', $balli_raw))));
                }

                // Verifica i campi obbligatori
                if (empty($disciplina) || empty($categoria) || empty($classe) || 
                    empty($tipo) || empty($da) || empty($balli)) {
                    $rows_skipped++;
                    $errors[] = "Riga " . ($row_index + 1) . ": campi obbligatori mancanti";
                    continue;
                }

                // Normalizza i dati
                $disciplina = mb_convert_case($disciplina, MB_CASE_TITLE, 'UTF-8');
                $categoria = mb_convert_case($categoria, MB_CASE_TITLE, 'UTF-8');
                $classe = mb_convert_case($classe, MB_CASE_TITLE, 'UTF-8');
                $tipo = mb_convert_case($tipo, MB_CASE_TITLE, 'UTF-8');

                // Rimuovi spazi extra e caratteri non necessari
                $disciplina = preg_replace('/\s+/', ' ', $disciplina);
                $categoria = preg_replace('/\s+/', ' ', $categoria);
                $classe = preg_replace('/\s+/', ' ', $classe);
                $tipo = preg_replace('/\s+/', ' ', $tipo);

                // Log dei dati normalizzati
                error_log("Riga {$row_index} - Dati normalizzati:");
                error_log("Disciplina: '{$disciplina}'");
                error_log("Categoria: '{$categoria}'");
                error_log("Classe: '{$classe}'");
                error_log("Tipo: '{$tipo}'");
                error_log("Da: '{$da}'");
                error_log("Balli: '{$balli}'");

                $insert_stmt->bind_param("sssssss", 
                    $timetable_id,
                    $disciplina,
                    $categoria,
                    $classe,
                    $tipo,
                    $da,
                    $balli
                );

                if (!$insert_stmt->execute()) {
                    throw new Exception('Errore durante l\'inserimento dei dati: ' . $insert_stmt->error);
                }

                $rows_imported++;
                error_log("Riga {$row_index} importata con successo");

            } catch (Exception $e) {
                $errors[] = "Errore nella riga {$row_index}: " . $e->getMessage();
                error_log("Errore nella riga {$row_index}: " . $e->getMessage());
                $rows_skipped++;
            }
        }

        // Commit della transazione
        $conn->commit();
        $insert_stmt->close();

        echo json_encode([
            'success' => true,
            'timetable_id' => $timetable_id,
            'rows_imported' => $rows_imported,
            'rows_skipped' => $rows_skipped,
            'errors' => $errors
        ]);

    } catch (Exception $e) {
        // Rollback in caso di errore
        $conn->rollback();
        if (isset($insert_stmt)) {
            $insert_stmt->close();
        }
        throw $e;
    }

} catch (Exception $e) {
    error_log('Errore in import_csv.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close(); 