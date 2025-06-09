<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

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

    if (!isset($data['headers']) || !isset($data['data'])) {
        throw new Exception('Dati CSV mancanti o non validi');
    }

    // Validate CSV data structure
    if (!is_array($data['headers']) || !is_array($data['data'])) {
        throw new Exception('Formato dati CSV non valido');
    }

    // Store CSV data in session
    $_SESSION['csv_data'] = [
        'headers' => $data['headers'],
        'data' => $data['data']
    ];

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {
    error_log('Errore in store_csv_data.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 