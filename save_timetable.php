<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

// Set content type to JSON for API requests
header('Content-Type: application/json');

$errors = [];
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle logo upload
    $logo_path = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/logos/';
        $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Tipo di file non supportato. Usa JPG, JPEG, PNG o GIF.";
        } else {
            $new_filename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                $logo_path = $upload_path;
            } else {
                $errors[] = "Errore durante il caricamento del logo.";
            }
        }
    } elseif (isset($_POST['selected_logo']) && !empty($_POST['selected_logo'])) {
        $logo_path = $_POST['selected_logo'];
    }

    // Get form data
    $titolo = trim($_POST['titolo'] ?? '');
    $sottotitolo = trim($_POST['sottotitolo'] ?? '');
    $desc1 = trim($_POST['desc1'] ?? '');
    $desc2 = trim($_POST['desc2'] ?? '');
    $disclaimer = trim($_POST['disclaimer'] ?? '');

    // Validate only if not a CSV import
    if (!isset($_POST['is_csv_import'])) {
        if (empty($titolo)) $errors[] = "Titolo richiesto";
        if (empty($sottotitolo)) $errors[] = "Sottotitolo richiesto";
        if (empty($desc1)) $errors[] = "Descrizione 1 richiesta";
        if (empty($desc2)) $errors[] = "Descrizione 2 richiesta";
        if (empty($disclaimer)) $errors[] = "Disclaimer richiesto";
    }

    if (empty($errors)) {
        // Insert into database
        if (!$conn) {
            $errors[] = "Errore di connessione al database";
        } else {
            $stmt = $conn->prepare("INSERT INTO timetables (user_created, titolo, sottotitolo, desc1, desc2, disclaimer, logo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("issssss", $user_id, $titolo, $sottotitolo, $desc1, $desc2, $disclaimer, $logo_path);
                
                if ($stmt->execute()) {
                    $timetable_id = $stmt->insert_id;
                    $stmt->close();
                    echo json_encode(['success' => true, 'timetable_id' => $timetable_id]);
                    exit;
                } else {
                    $errors[] = "Errore durante il salvataggio del cronologico: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Errore nella preparazione della query: " . $conn->error;
            }
        }
    }

    // Handle CSV import
    if (isset($_POST['is_csv_import']) && $_POST['is_csv_import'] === '1') {
        // Store CSV data in session
        $_SESSION['csv_data'] = $_POST['csv_data'];
        
        // Return JSON response
        echo json_encode([
            'success' => true,
            'timetable_id' => $timetable_id
        ]);
        exit;
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
        exit;
    }
}

// If we get here, something went wrong
echo json_encode(['success' => false, 'error' => 'Invalid request method']);
exit;