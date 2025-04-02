<?php
require_once 'config/database.php';
require_once 'config/session_check.php';
require_once 'includes/header.php';

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

<?php require_once 'includes/footer.php'; ?>
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
        const formData = new FormData();
        
        // Aggiungi i campi del form
        formData.append('id', document.getElementById('edit_id').value);
        formData.append('title', document.getElementById('edit_title').value);
        formData.append('version', document.getElementById('edit_version').value);
        formData.append('date', document.getElementById('edit_date').value);
        
        // Gestisci gli items come array
        const items = Array.from(document.querySelectorAll('#edit-items-container .item-input'))
            .map(input => input.value)
            .filter(item => item.trim() !== '');
        
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
<?php require_once 'includes/footer.php'; ?>
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
        .changelog-card {
            transition: transform 0.2s;
        }
        .changelog-card:hover {
            transform: translateY(-5px);
        }
    </style>
</body>
</html>