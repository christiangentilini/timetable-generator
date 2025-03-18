<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

// Verifica se l'utente è admin
$stmt = $conn->prepare("SELECT type FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['type'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Gestione eliminazione utente
if (isset($_POST['delete_user'])) {
    $user_id_to_delete = $_POST['user_id'];
    if ($user_id_to_delete != $_SESSION['user_id']) { // Impedisce l'eliminazione del proprio account
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id_to_delete);
        $stmt->execute();
    }
    header("Location: gestione-utenti.php");
    exit;
}

// Recupera tutti gli utenti con conteggio timetables
$query = "SELECT u.*, 
          COALESCE((SELECT COUNT(*) FROM timetables WHERE user_created = u.id), 0) as timetables_count
          FROM users u
          ORDER BY u.username";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti - Timetable Generator</title>
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
        .table th {
            background-color: #f8f9fa;
        }
        .badge-admin {
            background-color: #dc3545;
        }
        .badge-user {
            background-color: #28a745;
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
                            <?php if ($user['type'] === 'admin'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="gestione-utenti.php">Gestione Utenti</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestione Utenti</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUserModal">
                <i class="bi bi-plus-circle"></i> Nuovo Utente
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Nome</th>
                                <th>Cognome</th>
                                <th>Tipo</th>
                                <th>Cronologici</th>
                                <th>Ultimo Accesso</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['cognome']); ?></td>
                                <td><?php echo $row['type'] === 'admin' ? 'Amministratore' : 'Utente'; ?></td>
                                <td><?php echo $row['timetables_count']; ?></td>
                                <td><?php echo $row['last_login'] ? date('d/m/Y H:i', strtotime($row['last_login'])) : 'Mai'; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary" onclick="loadUserData(<?php echo $row['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Sei sicuro di voler eliminare questo utente?');">
                                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
        // Funzione per mostrare i feedback
        function showFeedback(message, isError = false) {
            const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
            const feedbackMessage = document.getElementById('feedbackMessage');
            const modalTitle = document.getElementById('feedbackModalLabel');
            
            if (isError) {
                modalTitle.textContent = 'Errore';
                feedbackMessage.innerHTML = `<div class="alert alert-danger">${message}</div>`;
            } else {
                modalTitle.textContent = 'Successo';
                feedbackMessage.innerHTML = `<div class="alert alert-success">${message}</div>`;
            }
            
            feedbackModal.show();
        }

        // Funzione per gestire l'invio del form di creazione
        function submitCreateUserForm() {
            const form = document.getElementById('createUserForm');
            const formData = new FormData(form);
            
            // Debug: stampa i dati del form
            console.log('Dati del form:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            fetch('create_user.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                console.log('Status della risposta:', response.status);
                if (!response.ok) {
                    throw new Error('Errore nella risposta del server');
                }
                return response.json();
            })
            .then(data => {
                console.log('Risposta ricevuta:', data);
                if (data.success) {
                    // Chiudi la modale di creazione utente
                    const newUserModal = bootstrap.Modal.getInstance(document.getElementById('newUserModal'));
                    if (newUserModal) {
                        newUserModal.hide();
                    }
                    
                    // Mostra la password temporanea in un modal
                    const tempPasswordModal = new bootstrap.Modal(document.getElementById('tempPasswordModal'));
                    document.getElementById('tempPassword').textContent = data.temp_password;
                    tempPasswordModal.show();
                } else {
                    showFeedback(data.error || 'Errore durante la creazione dell\'utente', true);
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                showFeedback('Errore durante la creazione dell\'utente: ' + error.message, true);
            });
        }

        // Funzione per gestire l'invio del form di modifica
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('update_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFeedback('Utente aggiornato con successo');
                    // Chiudi la modale e ricarica la pagina
                    bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showFeedback(data.error || 'Errore durante l\'aggiornamento dell\'utente', true);
                }
            })
            .catch(error => {
                showFeedback(error.message, true);
            });
        });

        // Funzione per caricare i dati dell'utente nella modale
        function loadUserData(userId) {
            fetch(`get_user_data.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_user_id').value = data.id;
                    document.getElementById('edit_username').value = data.username;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_nome').value = data.nome || '';
                    document.getElementById('edit_cognome').value = data.cognome || '';
                    document.getElementById('edit_type').value = data.type;
                    
                    // Mostra la modale
                    new bootstrap.Modal(document.getElementById('editUserModal')).show();
                })
                .catch(error => {
                    showFeedback('Errore durante il caricamento dei dati dell\'utente', true);
                });
        }
    </script>

    <!-- Modale Modifica Utente -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Modifica Utente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="edit_nome" name="nome" maxlength="30">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_cognome" class="form-label">Cognome</label>
                                <input type="text" class="form-control" id="edit_cognome" name="cognome" maxlength="30">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_type" class="form-label">Tipo Utente</label>
                            <select class="form-select" id="edit_type" name="type" required>
                                <option value="user">Utente</option>
                                <option value="admin">Amministratore</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Nuova Password (lascia vuoto per non modificare)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" form="editUserForm" class="btn btn-primary">Salva Modifiche</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale Nuovo Utente -->
    <div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newUserModalLabel">Nuovo Utente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createUserForm">
                        <div class="mb-3">
                            <label for="new_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="new_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="new_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="new_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_cognome" class="form-label">Cognome</label>
                            <input type="text" class="form-control" id="new_cognome" name="cognome" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_type" class="form-label">Tipo Utente</label>
                            <select class="form-select" id="new_type" name="type" required>
                                <option value="user">Utente</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" onclick="submitCreateUserForm()">Crea Utente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale Password Temporanea -->
    <div class="modal fade" id="tempPasswordModal" tabindex="-1" aria-labelledby="tempPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tempPasswordModalLabel">Password Temporanea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Utente creato con successo!</p>
                    <p>Password temporanea:</p>
                    <div class="alert alert-warning">
                        <code id="tempPassword" class="fs-5"></code>
                    </div>
                    <p class="text-danger">Salva questa password! L'utente dovrà cambiarla al primo accesso.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale Feedback -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="feedbackMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 