<?php
// Disabilita l'output degli errori PHP
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Imposta l'header per JSON
header('Content-Type: application/json');

// Funzione per gestire gli errori PHP
function handleError($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('handleError');

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/session_check.php';

    // Verifica autenticazione
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
        exit;
    }

    // Verifica che sia stato caricato un file
    if (!isset($_FILES['csv_file'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Nessun file caricato']);
        exit;
    }

    $file = $_FILES['csv_file'];

    // Verifica che sia un file CSV
    if ($file['type'] !== 'text/csv' && $file['type'] !== 'application/vnd.ms-excel') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Il file deve essere in formato CSV']);
        exit;
    }

    // Leggi il contenuto del file
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        throw new Exception('Impossibile leggere il file');
    }
    
    // Leggi l'header del CSV
    $header = fgetcsv($handle);
    if (!$header) {
        throw new Exception('File CSV vuoto o non valido');
    }
    
    // Verifica che l'header contenga i campi necessari
    $required_fields = ['Disciplina', 'Tipo Unità', 'Gruppo Età', 'Classe', 'Balli', 'Totale Registrazioni'];
    $missing_fields = array_diff($required_fields, $header);
    if (!empty($missing_fields)) {
        throw new Exception('Campi mancanti nel CSV: ' . implode(', ', $missing_fields));
    }
    
    // Leggi i dati
    $entries = [];
    $row_number = 2; // Inizia da 2 perché la riga 1 è l'header
    while (($data = fgetcsv($handle)) !== FALSE) {
        $entry = array_combine($header, $data);
        
        // Validazione base dei dati
        if (empty($entry['Disciplina']) || empty($entry['Tipo Unità']) || empty($entry['Gruppo Età']) || empty($entry['Classe'])) {
            $row_number++;
            continue; // Salta questa riga se mancano i campi obbligatori
        }
        
        // Salta la riga se Totale Registrazioni è vuoto
        if (empty($entry['Totale Registrazioni'])) {
            $row_number++;
            continue;
        }
        
        // Converti i numeri
        $totale_registrazioni = (int)$entry['Totale Registrazioni'];
        $balli = array_filter(array_map('trim', explode(',', $entry['Balli'])));
        
        // Salta la riga se il numero di registrazioni non è valido
        if ($totale_registrazioni <= 0) {
            $row_number++;
            continue;
        }
        
        // Determina il turno in base al numero di registrazioni
        $turno = 1;
        if ($totale_registrazioni > 30) {
            $turno = 2;
        }
        
        $entries[] = [
            'disciplina' => $entry['Disciplina'],
            'tipo' => $entry['Tipo Unità'],
            'categoria' => $entry['Gruppo Età'],
            'classe' => $entry['Classe'],
            'turno' => $turno,
            'balli' => count($balli),
            'da' => $totale_registrazioni,
            'a' => $totale_registrazioni,
            'batterie' => 1 // Valore predefinito
        ];
        
        $row_number++;
    }
    fclose($handle);
    
    if (empty($entries)) {
        throw new Exception('Nessuna categoria valida trovata nel file CSV');
    }
    
    // Funzione per calcolare il tempo stimato per una categoria
    function calculateEstimatedTime($entry) {
        // Tempo base per categoria (in minuti)
        $base_times = [
            'Principianti' => 2,
            'Intermedi' => 3,
            'Avanzati' => 4,
            'Over 55 (55+)' => 2.5
        ];
        
        // Tempo base per disciplina
        $discipline_times = [
            'Standard' => 1.5,
            'Latino' => 1.2,
            'Smooth' => 1.3,
            'Rhythm' => 1.2
        ];
        
        // Calcola il tempo base
        $base_time = $base_times[$entry['categoria']] ?? 3;
        $discipline_time = $discipline_times[$entry['disciplina']] ?? 1.5;
        
        // Calcola il numero di coppie
        $num_couples = $entry['a'] - $entry['da'] + 1;
        
        // Calcola il tempo totale
        $total_time = $base_time * $discipline_time * ceil($num_couples / $entry['batterie']);
        
        return $total_time;
    }

    // Funzione per ottenere la priorità di una categoria
    function getCategoryPriority($category) {
        $category_priorities = [
            'Avanzati' => 3,
            'Intermedi' => 2,
            'Principianti' => 1,
            'Over 55 (55+)' => 2
        ];
        
        return $category_priorities[$category] ?? 0;
    }

    // Ordina le entries per priorità
    usort($entries, function($a, $b) {
        // Prima le categorie più avanzate
        $a_priority = getCategoryPriority($a['categoria']);
        $b_priority = getCategoryPriority($b['categoria']);
        
        if ($a_priority !== $b_priority) {
            return $b_priority - $a_priority;
        }
        
        // Poi per numero di coppie (più coppie = più priorità)
        $a_couples = $a['a'] - $a['da'] + 1;
        $b_couples = $b['a'] - $b['da'] + 1;
        
        return $b_couples - $a_couples;
    });

    // Salva le entries in sessione per la seconda fase
    $_SESSION['csv_categories'] = $entries;

    echo json_encode([
        'success' => true,
        'message' => 'File CSV caricato e validato con successo',
        'categories' => $entries
    ]);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} 