<?php
session_start();
require_once 'config/database.php';
require_once 'config/session_check.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validazione email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Formato email non valido";
    } else {
        // Verifica se l'email è già in uso da un altro utente
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Questa email è già in uso";
        } else {
            // Aggiorna i dati dell'utente
            $stmt = $conn->prepare("UPDATE users SET email = ?, nome = ?, cognome = ? WHERE id = ?");
            $stmt->bind_param("sssi", $email, $nome, $cognome, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['email'] = $email;
                $_SESSION['nome'] = $nome;
                $_SESSION['cognome'] = $cognome;
                $success = true;
            } else {
                $error = "Errore durante l'aggiornamento del profilo";
            }
        }
    }
    
    // Validazione password
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $error = "La password deve essere di almeno 8 caratteri";
        }
        if ($password !== $confirm_password) {
            $error = "Le password non coincidono";
        }
    }
}

// Gestione upload immagine profilo
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
        $error = "Formato immagine non supportato. Usa JPG, PNG o GIF";
    } elseif ($_FILES['profile_image']['size'] > $max_size) {
        $error = "L'immagine non può superare i 5MB";
    } else {
        $user_dir = __DIR__ . '/src/users/' . $_SESSION['username'];
        $profile_dir = $user_dir . '/profile';
        
        if (!file_exists($profile_dir)) {
            mkdir($profile_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . time() . '.' . $file_extension;
        $target_path = $profile_dir . '/' . $new_filename;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
            // Aggiorna il percorso nel database
            $relative_path = 'src/users/' . $_SESSION['username'] . '/profile/' . $new_filename;
            $stmt = $conn->prepare("UPDATE users SET profile_path = ? WHERE id = ?");
            $stmt->bind_param("si", $relative_path, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['profile_path'] = $relative_path;
                $success = true;
            } else {
                $error = "Errore durante l'aggiornamento dell'immagine del profilo";
            }
        } else {
            $error = "Errore durante il caricamento dell'immagine";
        }
    }
}

header("Location: profilo.php?success=" . ($success ? "1" : "0") . "&error=" . urlencode($error));
exit; 