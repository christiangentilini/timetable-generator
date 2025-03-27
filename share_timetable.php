<?php
// Start output buffering to prevent header issues
ob_start();
require_once 'config/database.php';
require_once 'config/session_check.php';
require_once 'includes/header.php';

// Get timetable ID from URL
$timetable_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch timetable data
$timetable = null;
if ($timetable_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM timetables WHERE id = ? AND user_created = ?");
    $stmt->bind_param("ii", $timetable_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $timetable = $result->fetch_assoc();
    $stmt->close();
}

if (!$timetable) {
    header("Location: cronologici.php");
    exit;
}

// Fetch all users except current user
$stmt = $conn->prepare("SELECT id, username, nome, cognome FROM users WHERE id != ? ORDER BY username");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch current shares
$stmt = $conn->prepare("SELECT ts.*, u.username, u.nome, u.cognome 
                      FROM timetable_shares ts 
                      JOIN users u ON ts.user_id = u.id 
                      WHERE ts.timetable_id = ?");
$stmt->bind_param("i", $timetable_id);
$stmt->execute();
$result = $stmt->get_result();
$shares = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_share') {
            $user_id = (int)$_POST['user_id'];
            $permission = $_POST['permission'];
            
            // Validate permission
            if (!in_array($permission, ['view', 'edit'])) {
                $permission = 'view';
            }
            
            // Validate that user exists
            $user_check = $conn->prepare("SELECT id FROM users WHERE id = ?");
            $user_check->bind_param("i", $user_id);
            $user_check->execute();
            $user_result = $user_check->get_result();
            
            if ($user_result->num_rows === 0) {
                // User doesn't exist, show error
                $error_message = "L'utente selezionato non esiste.";
                // Continue with page rendering
                $user_check->close();
            } else {
                $user_check->close();
                
                // Check if share already exists
                $stmt = $conn->prepare("SELECT id FROM timetable_shares WHERE timetable_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $timetable_id, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Update existing share
                    $stmt = $conn->prepare("UPDATE timetable_shares SET permission_level = ? WHERE timetable_id = ? AND user_id = ?");
                    $stmt->bind_param("sii", $permission, $timetable_id, $user_id);
                } else {
                    // Add new share
                    $stmt = $conn->prepare("INSERT INTO timetable_shares (timetable_id, user_id, permission_level) VALUES (?, ?, ?)");
                    $stmt->bind_param("iis", $timetable_id, $user_id, $permission);
                }
                
                if ($stmt->execute()) {
                    header("Location: share_timetable.php?id={$timetable_id}&success=1");
                    exit;
                }
            }

        } elseif ($_POST['action'] === 'remove_share') {
            $share_id = (int)$_POST['share_id'];
            
            $stmt = $conn->prepare("DELETE FROM timetable_shares WHERE id = ? AND timetable_id = ?");
            $stmt->bind_param("ii", $share_id, $timetable_id);
            
            if ($stmt->execute()) {
                header("Location: share_timetable.php?id={$timetable_id}&removed=1");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Condividi Cronologico - Timetable Generator</title>
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
        .timetable-logo {
            width: 100%;
            height: auto;
            object-fit: contain;
            max-height: 120px;
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
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Condividi Cronologico</h2>
            <a href="crono-view.php?id=<?php echo $timetable_id; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Torna al Cronologico
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Cronologico condiviso con successo!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['removed'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            Condivisione rimossa con successo.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Dettagli Cronologico</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-4">
                                <img src="<?php echo htmlspecialchars($timetable['logo']); ?>" alt="Logo" class="timetable-logo w-100">
                            </div>
                            <div class="col-8">
                                <h5 class="card-title mb-2"><?php echo htmlspecialchars($timetable['titolo']); ?></h5>
                                <p class="card-text text-muted mb-2"><?php echo htmlspecialchars($timetable['sottotitolo']); ?></p>
                                <p class="card-text mb-1"><?php echo htmlspecialchars($timetable['desc1']); ?></p>
                                <p class="card-text mb-0"><?php echo htmlspecialchars($timetable['desc2']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Aggiungi Condivisione</h5>
                    </div>
                    <div class="card-body">
                        <form action="share_timetable.php?id=<?php echo $timetable_id; ?>" method="POST">
                            <input type="hidden" name="action" value="add_share">
                            <input type="hidden" id="user_id" name="user_id" value="">
                            <div class="mb-3">
                                <label for="user_search" class="form-label">Cerca utente (email o username)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="user_search" placeholder="Inserisci email o username" autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" id="search_button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div id="search_results" class="list-group mt-2" style="display:none;"></div>
                                <div id="selected_user" class="mt-2 p-2 border rounded" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong id="selected_username"></strong><br>
                                            <small id="selected_name" class="text-muted"></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="clear_selection">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Permessi</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="permission" id="permission_view" value="view" checked>
                                    <label class="form-check-label" for="permission_view">
                                        Solo visualizzazione
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="permission" id="permission_edit" value="edit">
                                    <label class="form-check-label" for="permission_edit">
                                        Visualizzazione e modifica
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-share me-2"></i>Condividi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Condivisioni Attuali</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($shares)): ?>
                        <div class="alert alert-info" role="alert">
                            Questo cronologico non è ancora condiviso con nessun utente.
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Utente</th>
                                        <th>Permessi</th>
                                        <th>Data</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shares as $share): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($share['username']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($share['nome'] . ' ' . $share['cognome']); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($share['permission_level'] === 'view'): ?>
                                            <span class="badge bg-info">Visualizzazione</span>
                                            <?php else: ?>
                                            <span class="badge bg-warning">Modifica</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i', strtotime($share['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <form action="share_timetable.php?id=<?php echo $timetable_id; ?>" method="POST" onsubmit="return confirm('Sei sicuro di voler rimuovere questa condivisione?');">
                                                <input type="hidden" name="action" value="remove_share">
                                                <input type="hidden" name="share_id" value="<?php echo $share['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userSearchInput = document.getElementById('user_search');
            const searchButton = document.getElementById('search_button');
            const searchResults = document.getElementById('search_results');
            const selectedUser = document.getElementById('selected_user');
            const selectedUsername = document.getElementById('selected_username');
            const selectedName = document.getElementById('selected_name');
            const userIdInput = document.getElementById('user_id');
            const clearSelectionBtn = document.getElementById('clear_selection');
            
            // Function to perform search
            function performSearch() {
                const searchTerm = userSearchInput.value.trim();
                
                if (searchTerm.length < 2) {
                    searchResults.innerHTML = '<div class="list-group-item text-danger">Inserisci almeno 2 caratteri</div>';
                    searchResults.style.display = 'block';
                    return;
                }
                
                // Show loading indicator
                searchResults.innerHTML = '<div class="list-group-item">Ricerca in corso...</div>';
                searchResults.style.display = 'block';
                
                // Make AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('GET', `api/search_users.php?term=${encodeURIComponent(searchTerm)}`, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                if (response.users.length > 0) {
                                    // Display results
                                    searchResults.innerHTML = '';
                                    response.users.forEach(user => {
                                        const item = document.createElement('a');
                                        item.href = '#';
                                        item.className = 'list-group-item list-group-item-action';
                                        item.innerHTML = `
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>${user.username}</strong><br>
                                                    <small class="text-muted">${user.nome} ${user.cognome}</small>
                                                </div>
                                                <small class="text-muted">${user.email}</small>
                                            </div>
                                        `;
                                        
                                        // Add click event to select user
                                        item.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            selectUser(user);
                                        });
                                        
                                        searchResults.appendChild(item);
                                    });
                                } else {
                                    searchResults.innerHTML = '<div class="list-group-item text-danger">Nessun utente trovato</div>';
                                }
                            } else {
                                searchResults.innerHTML = `<div class="list-group-item text-danger">${response.message}</div>`;
                            }
                        } catch (e) {
                            searchResults.innerHTML = '<div class="list-group-item text-danger">Errore durante la ricerca</div>';
                        }
                    } else {
                        searchResults.innerHTML = '<div class="list-group-item text-danger">Errore durante la ricerca</div>';
                    }
                };
                
                xhr.onerror = function() {
                    searchResults.innerHTML = '<div class="list-group-item text-danger">Errore di connessione</div>';
                };
                
                xhr.send();
            }
            
            // Function to select a user
            function selectUser(user) {
                userIdInput.value = user.id;
                selectedUsername.textContent = user.username;
                selectedName.textContent = `${user.nome} ${user.cognome} (${user.email})`;
                
                // Show selected user and hide search results
                selectedUser.style.display = 'block';
                searchResults.style.display = 'none';
                userSearchInput.value = '';
            }
            
            // Function to clear selection
            function clearSelection() {
                userIdInput.value = '';
                selectedUser.style.display = 'none';
                userSearchInput.value = '';
            }
            
            // Event listeners
            userSearchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });
            
            // Prevent form submission if no user is selected
            document.querySelector('form[action*="share_timetable.php"]').addEventListener('submit', function(e) {
                if (userIdInput.value === '') {
                    e.preventDefault();
                    alert('Seleziona un utente dalla ricerca prima di condividere il cronologico.');
                    return false;
                }
            });
            
            searchButton.addEventListener('click', performSearch);
            clearSelectionBtn.addEventListener('click', clearSelection);
            
            // Close search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchResults.contains(e.target) && e.target !== userSearchInput && e.target !== searchButton) {
                    searchResults.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>