<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

// Recupera il tipo di utente
$stmt = $conn->prepare("SELECT type FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Recupera tutte le note di rilascio
$query = "SELECT c.*, COUNT(cd.id) as items_count 
          FROM changelog c 
          LEFT JOIN changelog_data cd ON c.id = cd.version_id 
          GROUP BY c.id 
          ORDER BY c.date DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Note di Rilascio - Timetable Generator</title>
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
        .changelog-card {
            transition: transform 0.2s;
        }
        .changelog-card:hover {
            transform: translateY(-5px);
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
                            <?php if ($user['type'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="gestione-utenti.php">Gestione Utenti</a></li>
                            <?php endif; ?>
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
            <h1 class="h3 mb-0">Note di Rilascio</h1>
            <?php if ($user['type'] === 'admin'): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newChangelogModal">
                <i class="bi bi-plus-circle"></i> Nuova Nota di Rilascio
            </button>
            <?php endif; ?>
        </div>

        <div class="row">
            <?php while ($changelog = $result->fetch_assoc()): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 changelog-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($changelog['title']); ?></h5>
                            <?php if ($user['type'] === 'admin'): ?>
                            <div class="dropdown">
                                <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="editChangelog(<?php echo $changelog['id']; ?>)">
                                        <i class="bi bi-pencil"></i> Modifica
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="duplicateChangelog(<?php echo $changelog['id']; ?>)">
                                        <i class="bi bi-files"></i> Duplica
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteChangelog(<?php echo $changelog['id']; ?>)">
                                        <i class="bi bi-trash"></i> Elimina
                                    </a></li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        <p class="card-text text-muted mb-2">
                            Versione <?php echo htmlspecialchars($changelog['version']); ?> - 
                            <?php echo date('d/m/Y', strtotime($changelog['date'])); ?>
                        </p>
                        <div class="changelog-items">
                            <?php
                            $items_query = "SELECT item FROM changelog_data WHERE version_id = ? ORDER BY id";
                            $stmt = $conn->prepare($items_query);
                            $stmt->bind_param("i", $changelog['id']);
                            $stmt->execute();
                            $items_result = $stmt->get_result();
                            while ($item = $items_result->fetch_assoc()):
                            ?>
                            <p class="mb-1">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?php echo htmlspecialchars($item['item']); ?>
                            </p>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- New Changelog Modal -->
    <div class="modal fade" id="newChangelogModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuova Nota di Rilascio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newChangelogForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Titolo</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="version" class="form-label">Versione</label>
                            <input type="text" class="form-control" id="version" name="version" required>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Data</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modifiche</label>
                            <div id="items-container">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control item-input" name="items[]" required>
                                    <button type="button" class="btn btn-outline-danger remove-item" style="display: none;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-item">
                                <i class="bi bi-plus-circle"></i> Aggiungi Modifica
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Changelog Modal -->
    <div class="modal fade" id="editChangelogModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica Nota di Rilascio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editChangelogForm">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Titolo</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_version" class="form-label">Versione</label>
                            <input type="text" class="form-control" id="edit_version" name="version" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_date" class="form-label">Data</label>
                            <input type="date" class="form-control" id="edit_date" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modifiche</label>
                            <div id="edit-items-container">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control item-input" name="items[]" required>
                                    <button type="button" class="btn btn-outline-danger remove-item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-edit-item">
                                <i class="bi bi-plus-circle"></i> Aggiungi Modifica
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestione form nuova nota di rilascio
        document.getElementById('newChangelogForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Rimuovi gli item vuoti
            const items = Array.from(formData.getAll('items[]')).filter(item => item.trim() !== '');
            formData.delete('items[]');
            items.forEach(item => formData.append('items[]', item));
            
            fetch('create_changelog.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore durante il salvataggio: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore durante il salvataggio');
            });
        });

        // Gestione aggiunta/rimozione item
        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.className = 'input-group mb-2';
            newItem.innerHTML = `
                <input type="text" class="form-control item-input" name="items[]" required>
                <button type="button" class="btn btn-outline-danger remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(newItem);
        });

        // Gestione rimozione item
        document.getElementById('items-container').addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                e.target.closest('.input-group').remove();
            }
        });

        // Mostra/nascondi pulsante rimozione
        document.getElementById('items-container').addEventListener('input', function(e) {
            if (e.target.classList.contains('item-input')) {
                const removeButton = e.target.nextElementSibling;
                removeButton.style.display = e.target.value.trim() !== '' ? 'block' : 'none';
            }
        });

        // Funzione per modificare una nota di rilascio
        function editChangelog(id) {
            fetch('get_changelog.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_title').value = data.title;
                    document.getElementById('edit_version').value = data.version;
                    document.getElementById('edit_date').value = data.date;
                    
                    // Pulisci e popola il container degli item
                    const container = document.getElementById('edit-items-container');
                    container.innerHTML = '';
                    data.items.forEach(item => {
                        const newItem = document.createElement('div');
                        newItem.className = 'input-group mb-2';
                        newItem.innerHTML = `
                            <input type="text" class="form-control item-input" name="items[]" value="${item}" required>
                            <button type="button" class="btn btn-outline-danger remove-item">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                        container.appendChild(newItem);
                    });
                    
                    new bootstrap.Modal(document.getElementById('editChangelogModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore durante il caricamento dei dati');
                });
        }

        // Gestione form modifica nota di rilascio
        document.getElementById('editChangelogForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Rimuovi gli item vuoti
            const items = Array.from(formData.getAll('items[]')).filter(item => item.trim() !== '');
            formData.delete('items[]');
            items.forEach(item => formData.append('items[]', item));
            
            fetch('update_changelog.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore durante l\'aggiornamento: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore durante l\'aggiornamento');
            });
        });

        // Gestione aggiunta/rimozione item nel form di modifica
        document.getElementById('add-edit-item').addEventListener('click', function() {
            const container = document.getElementById('edit-items-container');
            const newItem = document.createElement('div');
            newItem.className = 'input-group mb-2';
            newItem.innerHTML = `
                <input type="text" class="form-control item-input" name="items[]" required>
                <button type="button" class="btn btn-outline-danger remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(newItem);
        });

        // Gestione rimozione item nel form di modifica
        document.getElementById('edit-items-container').addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                e.target.closest('.input-group').remove();
            }
        });

        // Mostra/nascondi pulsante rimozione nel form di modifica
        document.getElementById('edit-items-container').addEventListener('input', function(e) {
            if (e.target.classList.contains('item-input')) {
                const removeButton = e.target.nextElementSibling;
                removeButton.style.display = e.target.value.trim() !== '' ? 'block' : 'none';
            }
        });

        // Funzione per duplicare una nota di rilascio
        function duplicateChangelog(id) {
            if (confirm('Vuoi davvero duplicare questa nota di rilascio?')) {
                const formData = new FormData();
                formData.append('id', id);
                
                fetch('duplicate_changelog.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Errore durante la duplicazione: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore durante la duplicazione');
                });
            }
        }

        // Funzione per eliminare una nota di rilascio
        function deleteChangelog(id) {
            if (confirm('Vuoi davvero eliminare questa nota di rilascio?')) {
                const formData = new FormData();
                formData.append('id', id);
                
                fetch('delete_changelog.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Errore durante l\'eliminazione: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore durante l\'eliminazione');
                });
            }
        }
    </script>
</body>
</html>