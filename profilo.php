<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

$user_id = $_SESSION['user_id'];
$success = false;
$errors = [];

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email, nome, cognome, profile_path FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Se non ci sono dati nel database, usa quelli della sessione
$email = $user['email'] ?? $_SESSION['email'] ?? '';
$nome = $user['nome'] ?? $_SESSION['nome'] ?? '';
$cognome = $user['cognome'] ?? $_SESSION['cognome'] ?? '';
$profile_path = $user['profile_path'] ?? $_SESSION['profile_path'] ?? '';
$username = $user['username'] ?? $_SESSION['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['password_confirm'];

    // Validation
    if (empty($username)) {
        $errors[] = "Il nome utente è obbligatorio";
    }
    if (empty($email)) {
        $errors[] = "L'email è obbligatoria";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Formato email non valido";
    }

    // Check if username or email already exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Nome utente o email già in uso";
    }
    $stmt->close();

    // Handle profile image upload
    $profile_path = $user['profile_path'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Tipo di file non supportato. Utilizzare JPG, PNG o GIF";
        } else {
            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = $username . '-profile.' . $extension;
            $upload_dir = __DIR__ . '/src/users/' . $username . '/logo/';
            $upload_path = $upload_dir . $new_filename;

            // Delete old profile image if it exists
            if ($profile_path && file_exists(__DIR__ . '/' . $profile_path)) {
                unlink(__DIR__ . '/' . $profile_path);
            }

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_path = 'src/users/' . $username . '/logo/' . $new_filename;
            } else {
                $errors[] = "Errore durante il caricamento dell'immagine";
            }
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE users SET username = ?, email = ?";
        $types = "ss";
        $bindParams = array(&$types, &$username, &$email);
    
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $errors[] = "La password deve essere di almeno 8 caratteri";
            } elseif ($password !== $confirm_password) {
                $errors[] = "Le password non coincidono";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = ?";
                $types .= "s";
                $bindParams[] = &$hashed_password;
            }
        }
    
        if ($profile_path !== $user['profile_path']) {
            $sql .= ", profile_path = ?";
            $types .= "s";
            $bindParams[] = &$profile_path;
        }
    
        $sql .= " WHERE id = ?";
        $types .= "i";
        $bindParams[] = &$user_id;
    
        if (empty($errors)) {
            $stmt = $conn->prepare($sql);
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
            
            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                $_SESSION['profile_path'] = $profile_path;
                $success = true;
                // Refresh user data
                $user['username'] = $username;
                $user['email'] = $email;
                $user['profile_path'] = $profile_path;
                
                // Add cache control headers
                header("Cache-Control: no-cache, must-revalidate");
                header("Pragma: no-cache");
            } else {
                $errors[] = "Errore durante l'aggiornamento del profilo";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo - Timetable Generator</title>
    <link rel="icon" type="image/png" href="assets/favicon/favicon.png">
    <link rel="apple-touch-icon" href="assets/favicon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            padding-top: 80px;
            padding-bottom: 100px;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 500;
        }
        .version-text {
            font-size: 0.875rem;
            color: #ffffff !important;
            margin-left: 0.5rem;
        }
        .profile-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
            overflow: hidden;
        }
        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: #6c757d;
            overflow: hidden;
        }
        .profile-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .floating-footer {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 40px);
            max-width: 1200px;
            background-color: #fff;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            z-index: 1000;
            font-size: 11px;
        }
        .floating-footer a {
            font-size: 11px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Timetable Generator <span class="version-text">v1.0</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link profile-image" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if (isset($_SESSION['profile_path']) && $_SESSION['profile_path']): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['profile_path']); ?>?v=<?php echo time(); ?>" alt="Profile" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-circle"></i>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li><span class="dropdown-item-text">Ciao, <?php echo htmlspecialchars($_SESSION['nome'] ?? '') . ' ' . htmlspecialchars($_SESSION['cognome'] ?? ''); ?>!</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="profilo.php">Profilo</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-list"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="cronologici.php">Cronologici</a></li>
                            <li><a class="dropdown-item" href="crono-view.php">Nuovo Cronologico</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="definizioni.php">Definizioni</a></li>
                            <li><a class="dropdown-item" href="profilo.php">Profilo</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Profilo Utente</h3>
                    </div>
                    <div class="card-body">
                        <form id="profileForm" method="POST" action="update_profile.php" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" name="nome" maxlength="30" value="<?php echo htmlspecialchars($nome); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="cognome" class="form-label">Cognome</label>
                                    <input type="text" class="form-control" id="cognome" name="cognome" maxlength="30" value="<?php echo htmlspecialchars($cognome); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Nuova Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Conferma Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="profile_image" class="form-label">Immagine Profilo</label>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if (isset($_SESSION['profile_path']) && $_SESSION['profile_path']): ?>
                                                <img src="<?php echo htmlspecialchars($_SESSION['profile_path']); ?>?v=<?php echo time(); ?>" alt="Profile" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                            <?php else: ?>
                                                <i class="bi bi-person-circle" style="font-size: 100px;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="floating-footer">
        <div class="container text-center">
            <span>© 2025 - Timetable Generator v1.0 by Christian Gentilini - All rights reserved</span>
            <span class="mx-2">|</span>
            <a href="privacy-policy.php" class="text-decoration-none">Privacy Policy</a>
            <span class="mx-2">|</span>
            <a href="cookie-policy.php" class="text-decoration-none">Cookie Policy</a>
            <span class="mx-2">|</span>
            <a href="terms.php" class="text-decoration-none">Termini e Condizioni</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('profile_image').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePreview');
                    if (!preview) {
                        const newPreview = document.createElement('img');
                        newPreview.id = 'profilePreview';
                        newPreview.alt = 'Profile Preview';
                        const previewContainer = document.querySelector('.profile-preview');
                        previewContainer.innerHTML = '';
                        previewContainer.appendChild(newPreview);
                    }
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>