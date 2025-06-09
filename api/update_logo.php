<?php
require_once '../config/database.php';
require_once '../config/session_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verifica che l'ID del timetable sia presente
if (!isset($_POST['timetable_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$timetable_id = (int)$_POST['timetable_id'];

// Verifica che l'utente sia proprietario del timetable
$stmt = $conn->prepare("SELECT user_created FROM timetables WHERE id = ?");
$stmt->bind_param("i", $timetable_id);
$stmt->execute();
$result = $stmt->get_result();
$timetable = $result->fetch_assoc();
$stmt->close();

if (!$timetable || $timetable['user_created'] !== $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Gestione del logo esistente
if (isset($_POST['logo'])) {
    $logo_path = $_POST['logo'];
    
    // Verifica che il percorso del logo sia valido
    $logos_dir = realpath(__DIR__ . '/../assets/logos');
    if (!is_dir($logos_dir)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Directory dei loghi non trovata']);
        exit;
    }
    
    $absolute_logo_path = realpath($logos_dir . '/' . basename($logo_path));
    if (!$absolute_logo_path || !file_exists($absolute_logo_path) || dirname($absolute_logo_path) !== $logos_dir) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Percorso del logo non valido: ' . $logo_path]);
        exit;
    }
    
    // Aggiorna il logo nel database
    $stmt = $conn->prepare("UPDATE timetables SET logo = ? WHERE id = ? AND user_created = ?");
    $stmt->bind_param("sii", $logo_path, $timetable_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Logo aggiornato con successo',
            'logo_path' => $logo_path
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore durante l\'aggiornamento del logo nel database']);
    }
    $stmt->close();
}
// Gestione del caricamento di un nuovo logo
elseif (isset($_FILES['logo_image'])) {
    $file = $_FILES['logo_image'];
    
    // Verifica il tipo di file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tipo di file non valido']);
        exit;
    }
    
    // Verifica la dimensione del file (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File troppo grande']);
        exit;
    }
    
    // Genera un nome file unico
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_path = '../assets/logos/' . $filename;
    
    // Sposta il file caricato
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $logo_path = '/assets/logos/' . $filename;
        
        // Aggiorna il logo nel database
        $stmt = $conn->prepare("UPDATE timetables SET logo = ? WHERE id = ? AND user_created = ?");
        $stmt->bind_param("sii", $logo_path, $timetable_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Logo caricato e aggiornato con successo',
                'logo_path' => $logo_path
            ]);
        } else {
            unlink($upload_path); // Rimuovi il file se l'aggiornamento del database fallisce
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Impossibile aggiornare il logo']);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore durante il caricamento del logo. Riprova più tardi.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nessun logo fornito']);
}

$conn->close();