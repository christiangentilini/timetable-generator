<?php
// Abilita il logging degli errori
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

// Imposta l'header per JSON
header('Content-Type: application/json');

// Funzione per gestire gli errori PHP
function handleError($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('handleError');

// Funzione per inviare una risposta JSON
function sendJsonResponse($success, $data = null, $error = null, $code = 200) {
    http_response_code($code);
    $response = ['success' => $success];
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    if ($error !== null) {
        $response['error'] = $error;
    }
    $json = json_encode($response);
    if ($json === false) {
        error_log("JSON encode error: " . json_last_error_msg());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore interno del server']);
        exit;
    }
    echo $json;
    exit;
}

try {
    require_once '../config/session_check.php';
    error_log('DEBUG SESSION: ' . print_r(isset($_SESSION), true));
    error_log('DEBUG SESSION user_id: ' . print_r(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set', true));
    
    // Log dei dati in ingresso
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    error_log("SESSION data: " . print_r($_SESSION, true));

    require_once '../config/database.php';

    // Verifica autenticazione
    if (!isLoggedIn()) {
        error_log("User not logged in");
        sendJsonResponse(false, null, 'Non autorizzato', 401);
    }

    // Verifica che ci siano categorie in sessione
    if (!isset($_SESSION['csv_categories'])) {
        error_log("No categories found in session");
        sendJsonResponse(false, null, 'Nessuna categoria trovata in sessione', 400);
    }

    // Verifica i parametri richiesti
    $required_params = ['titolo', 'sottotitolo', 'ora_inizio', 'ora_apertura'];
    $missing_params = [];
    foreach ($required_params as $param) {
        if (!isset($_POST[$param]) || empty($_POST[$param])) {
            $missing_params[] = $param;
        }
    }

    if (!empty($missing_params)) {
        error_log("Missing parameters: " . implode(', ', $missing_params));
        sendJsonResponse(false, null, 'Parametri mancanti: ' . implode(', ', $missing_params), 400);
    }

    $conn = getDBConnection();
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        sendJsonResponse(false, null, 'Errore di connessione al database', 500);
    }
    
    error_log("Database connection successful");
    
    // Inizia la transazione
    $conn->autocommit(FALSE);
    error_log("Transaction started");
    
    try {
        // Gestione del logo
        $logo = null;
        if (isset($_POST['existing_logo']) && !empty($_POST['existing_logo'])) {
            // Usa il logo dalle definizioni
            require_once '../config/definizioni.php';
            if (isset($definizioni_loghi[$_POST['existing_logo']])) {
                $logo = $definizioni_loghi[$_POST['existing_logo']]['path'];
            }
        } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            // Carica un nuovo logo
            $upload_dir = '../uploads/logos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception("Formato file non supportato. Usa JPG, PNG o GIF.");
            }
            
            if ($_FILES['logo']['size'] > 2 * 1024 * 1024) { // 2MB
                throw new Exception("Il file è troppo grande. Dimensione massima: 2MB");
            }
            
            $logo_filename = uniqid() . '.' . $file_extension;
            $logo = 'uploads/logos/' . $logo_filename;
            
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_filename)) {
                throw new Exception("Errore nel caricamento del logo");
            }
        }
        
        // Crea il nuovo timetable
        $stmt = $conn->prepare("INSERT INTO timetables (user_created, titolo, sottotitolo, desc1, desc2, disclaimer, logo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Errore nella preparazione della query timetables: " . $conn->error);
        }
        
        $user_id = $_SESSION['user_id'];
        $titolo = $_POST['titolo'];
        $sottotitolo = $_POST['sottotitolo'];
        $desc1 = $_POST['desc1'] ?? "";
        $desc2 = $_POST['desc2'] ?? "";
        $disclaimer = $_POST['disclaimer'] ?? "";
        
        $stmt->bind_param("issssss", 
            $user_id,
            $titolo,
            $sottotitolo,
            $desc1,
            $desc2,
            $disclaimer,
            $logo
        );
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Errore nella creazione del timetable: " . $stmt->error);
        }
        
        $timetable_id = $conn->insert_id;
        error_log("Timetable created with ID: " . $timetable_id);
        
        // Salva la configurazione degli orari
        $stmt = $conn->prepare("INSERT INTO timetable_config (timetable_id, ora_inizio, ora_apertura) VALUES (?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Errore nella preparazione della query timetable_config: " . $conn->error);
        }
        
        $ora_inizio = $_POST['ora_inizio'];
        $ora_apertura = $_POST['ora_apertura'];
        
        $stmt->bind_param("iss", $timetable_id, $ora_inizio, $ora_apertura);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Errore nel salvataggio della configurazione degli orari: " . $stmt->error);
        }

        // Inserisci i dettagli generali
        $stmt = $conn->prepare("INSERT INTO timetable_details (timetable_id, entry_type, time_slot, order_number, description) VALUES (?, 'descriptive', ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Errore nella preparazione della query timetable_details: " . $conn->error);
        }
        
        // Dettagli generali - Dati competizione
        $order = 0;
        $description = "Dati competizione";
        $stmt->bind_param("isis", $timetable_id, $ora_inizio, $order, $description);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Errore nell'inserimento dei dettagli generali: " . $stmt->error);
        }

        // Dettagli generali - Orari
        $order++;
        $description = "Apertura porte: " . $ora_apertura . "\nInizio competizione: " . $ora_inizio;
        $stmt->bind_param("isis", $timetable_id, $ora_inizio, $order, $description);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Errore nell'inserimento degli orari: " . $stmt->error);
        }

        // Dettagli generali - Note
        $order++;
        $description = "Tutti i concorrenti devono presentarsi almeno 30 minuti prima della loro categoria\n" .
                      "È obbligatorio presentarsi con il numero di gara\n" .
                      "In caso di ritardo non sarà possibile partecipare alla categoria\n" .
                      $disclaimer;
        $stmt->bind_param("isis", $timetable_id, $ora_inizio, $order, $description);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Errore nell'inserimento delle note: " . $stmt->error);
        }
        
        // Inserisci le categorie
        $stmt = $conn->prepare("INSERT INTO timetable_details (timetable_id, entry_type, time_slot, discipline, category, class_name, type, turn, da, a, balli, batterie, order_number, pannello, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Errore nella preparazione della query per le categorie: " . $conn->error);
        }
        
        $order = 10; // Inizia dopo i dettagli generali
        foreach ($_SESSION['csv_categories'] as $category) {
            $entry_type = 'normal';
            $discipline = $category['disciplina'] ?? "";
            $category_name = $category['categoria'] ?? "";
            $class_name = $category['classe'] ?? "";
            $type = $category['tipo'] ?? "";
            $turn = $category['turno'] ?? "";
            $da = $category['da'] ?? "";
            $a = $category['a'] ?? "";
            $balli = $category['balli'] ?? "";
            $batterie = $category['batterie'] ?? "";
            $pannello = "A"; // Valore di default per il pannello
            $description = ""; // Campo description invece di desc1-5
            
            $stmt->bind_param("issssssssssssss",
                $timetable_id,
                $entry_type,
                $ora_inizio,
                $discipline,
                $category_name,
                $class_name,
                $type,
                $turn,
                $da,
                $a,
                $balli,
                $batterie,
                $order,
                $pannello,
                $description
            );
            
            if (!$stmt->execute()) {
                error_log("Execute failed for category: " . print_r($category, true));
                error_log("Error: " . $stmt->error);
                throw new Exception("Errore nell'inserimento della categoria: " . $stmt->error . "\nDati: " . print_r($category, true));
            }
            
            $order++;
        }
        
        // Commit della transazione
        $conn->commit();
        
        // Pulisci la sessione
        unset($_SESSION['csv_categories']);
        
        // Invia risposta di successo
        sendJsonResponse(true, ['timetable_id' => $timetable_id]);
        
    } catch (Exception $e) {
        // Rollback in caso di errore
        $conn->rollback();
        error_log("Error in configure_timetable.php: " . $e->getMessage());
        sendJsonResponse(false, null, $e->getMessage(), 500);
    }
    
    // Chiudi la connessione
    $conn->close();
    
} catch (Exception $e) {
    error_log("Fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse(false, null, $e->getMessage(), 500);
} finally {
    // Ripristina l'autocommit
    if (isset($conn)) {
        $conn->autocommit(TRUE);
    }
} 