<?php
require_once 'config/database.php';
require_once 'config/session_check.php';
require_once 'includes/header.php';

// Se l'utente è già autenticato, reindirizza alla home
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

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
        $nome = trim($_POST['nome'] ?? '');
        $cognome = trim($_POST['cognome'] ?? '');
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, nome, cognome) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $nome, $cognome);
        
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" maxlength="30">
                            </div>
                            <div class="col-md-6">
                                <label for="cognome" class="form-label">Cognome</label>
                                <input type="text" class="form-control" id="cognome" name="cognome" maxlength="30">
                            </div>
                        </div>
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

<?php require_once 'includes/footer.php'; ?>
</body>
</html>