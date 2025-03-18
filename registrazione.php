<?php
session_start();
require_once 'config/database.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username)) {
        $errors[] = "Il nome utente è obbligatorio";
    }
    if (empty($email)) {
        $errors[] = "L'email è obbligatoria";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Formato email non valido";
    }
    if (empty($password)) {
        $errors[] = "La password è obbligatoria";
    } elseif (strlen($password) < 8) {
        $errors[] = "La password deve essere di almeno 8 caratteri";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Le password non coincidono";
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $bindParams = array(&$username, &$email);
    call_user_func_array([$stmt, 'bind_param'], array_merge(array("ss"), $bindParams));
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Nome utente o email già in uso";
    }
    $stmt->close();

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            // Create user directories
            $user_dir = __DIR__ . '/src/users/' . $username;
            $logo_dir = $user_dir . '/logo';
            $media_dir = $user_dir . '/media';

            if (!file_exists($user_dir) && mkdir($user_dir, 0755, true)) {
                mkdir($logo_dir, 0755);
                mkdir($media_dir, 0755);
                $success = true;
            } else {
                $errors[] = "Errore durante la creazione delle cartelle utente. Riprova più tardi.";
            }
        } else {
            $errors[] = "Errore durante la registrazione. Riprova più tardi.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - Timetable Generator</title>
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
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['profile_path']) && $_SESSION['profile_path']): ?>
                    <img src="<?php echo htmlspecialchars($_SESSION['profile_path']); ?>" alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                <?php else: ?>
                    <i class="bi bi-person-circle text-white" style="font-size: 2rem;"></i>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Registrazione</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                Registrazione completata con successo! <a href="login.php">Accedi ora</a>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nome Utente</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Conferma Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Registrati</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            Hai già un account? <a href="login.php">Accedi</a>
                        </div>
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
</body>
</html>