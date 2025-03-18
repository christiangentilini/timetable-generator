<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

// Recupera il tipo di utente
$stmt = $conn->prepare("SELECT type FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verifica se l'utente è admin
if ($user['type'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Recupera tutti gli utenti
$query = "SELECT u.*, 
          COALESCE((SELECT COUNT(*) FROM timetables WHERE user_created = u.id), 0) as timetables_count
          FROM users u
          ORDER BY u.username";
$result = $conn->query($query);

// Include l'header comune
require_once 'includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Gestione Utenti</h1>
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
                            <th>Timetables</th>
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
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><?php echo $row['timetables_count']; ?></td>
                            <td><?php echo $row['last_login'] ? date('d/m/Y H:i', strtotime($row['last_login'])) : 'Mai'; ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $row['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $row['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New User Modal -->
<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuovo Utente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="cognome" class="form-label">Cognome</label>
                        <input type="text" class="form-control" id="cognome" name="cognome" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo Utente</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="user">Utente</option>
                            <option value="admin">Amministratore</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Crea Utente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifica Utente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="edit_nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_cognome" class="form-label">Cognome</label>
                        <input type="text" class="form-control" id="edit_cognome" name="cognome" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">Tipo Utente</label>
                        <select class="form-select" id="edit_type" name="type" required>
                            <option value="user">Utente</option>
                            <option value="admin">Amministratore</option>
                        </select>
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

<!-- Temporary Password Modal -->
<div class="modal fade" id="tempPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Password Temporanea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>La password temporanea per l'utente è:</p>
                <div class="alert alert-info">
                    <code id="tempPassword"></code>
                </div>
                <p class="text-danger">Salva questa password! Non sarà più visibile dopo aver chiuso questa finestra.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ho Salvato la Password</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Gestione form nuovo utente
document.getElementById('newUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('create_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('tempPassword').textContent = data.temp_password;
            new bootstrap.Modal(document.getElementById('tempPasswordModal')).show();
            location.reload();
        } else {
            alert('Errore durante la creazione: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore durante la creazione');
    });
});

// Funzione per modificare un utente
function editUser(id) {
    fetch('get_user.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_nome').value = data.nome;
            document.getElementById('edit_cognome').value = data.cognome;
            document.getElementById('edit_type').value = data.type;
            
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Errore durante il caricamento dei dati');
        });
}

// Gestione form modifica utente
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

// Funzione per eliminare un utente
function deleteUser(id) {
    if (confirm('Vuoi davvero eliminare questo utente?')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('delete_user.php', {
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