<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $errors = [];
    $logo_path = '';

    // Handle file upload
    // Gestione del logo (caricamento nuovo o selezione dalla galleria)
    if (isset($_POST['selected_logo'])) {
        // Logo selezionato dalla galleria
        $logo_path = $_POST['selected_logo'];
        
        // Verifica che il percorso del logo sia valido
        $logos_dir = realpath(__DIR__ . '/assets/logos');
        if (!is_dir($logos_dir)) {
            $errors[] = "Directory dei loghi non trovata";
        } else {
            $absolute_logo_path = realpath($logos_dir . '/' . basename($logo_path));
            if (!$absolute_logo_path || !file_exists($absolute_logo_path) || dirname($absolute_logo_path) !== $logos_dir) {
                $errors[] = "Percorso del logo non valido";
            }
        }
    } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        // Caricamento nuovo logo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['logo']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Tipo di file non supportato. Utilizzare JPG, PNG o GIF";
        } else {
            $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
            $upload_path = __DIR__ . '/assets/logos/' . $filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                $logo_path = 'assets/logos/' . $filename;
            } else {
                $errors[] = "Errore durante il caricamento del logo";
            }
        }
    } else {
        $errors[] = "Logo richiesto";
    }

    // Validate other fields
    $titolo = trim($_POST['title']);
    $sottotitolo = trim($_POST['subtitle']);
    $desc1 = trim($_POST['desc1']);
    $desc2 = trim($_POST['desc2']);
    $disclaimer = trim($_POST['disclaimer']);

    if (empty($titolo)) $errors[] = "Titolo richiesto";
    if (empty($sottotitolo)) $errors[] = "Sottotitolo richiesto";
    if (empty($desc1)) $errors[] = "Descrizione 1 richiesta";
    if (empty($desc2)) $errors[] = "Descrizione 2 richiesta";
    if (empty($disclaimer)) $errors[] = "Disclaimer richiesto";

    if (empty($errors)) {
        // Insert into database
        if (!$conn) {
            $errors[] = "Errore di connessione al database";
        } else {
            $stmt = $conn->prepare("INSERT INTO timetables (user_created, titolo, sottotitolo, desc1, desc2, disclaimer, logo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("issssss", $user_id, $titolo, $sottotitolo, $desc1, $desc2, $disclaimer, $logo_path);
                
                if ($stmt->execute()) {
                    header("Location: cronologici.php?success=1");
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

    if (!empty($errors)) {
        $_SESSION['error'] = implode(', ', $errors);
        header("Location: index.php");
        exit;
    }
}

header("Location: index.php");
exit;