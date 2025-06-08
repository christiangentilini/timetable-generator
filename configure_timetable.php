<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

// Verifica autenticazione
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Recupera le categorie dalla sessione
$categories = $_SESSION['csv_categories'] ?? [];

// Se non ci sono categorie, reindirizza alla pagina di upload
if (empty($categories)) {
    header('Location: cronologici.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configura Timetable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Configura Timetable</h1>
        
        <!-- Form di configurazione -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Configurazione Timetable</h5>
                    </div>
                    <div class="card-body">
                        <form id="configureForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="titolo" class="form-label">Titolo</label>
                                    <input type="text" class="form-control" id="titolo" name="titolo" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sottotitolo" class="form-label">Sottotitolo</label>
                                    <input type="text" class="form-control" id="sottotitolo" name="sottotitolo">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ora_inizio" class="form-label">Ora Inizio</label>
                                    <input type="time" class="form-control" id="ora_inizio" name="ora_inizio" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ora_apertura" class="form-label">Ora Apertura Porte</label>
                                    <input type="time" class="form-control" id="ora_apertura" name="ora_apertura" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="desc1" class="form-label">Descrizione 1</label>
                                    <input type="text" class="form-control" id="desc1" name="desc1" placeholder="Es: Organizzato da...">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="desc2" class="form-label">Descrizione 2</label>
                                    <input type="text" class="form-control" id="desc2" name="desc2" placeholder="Es: In collaborazione con...">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="disclaimer" class="form-label">Disclaimer</label>
                                    <textarea class="form-control" id="disclaimer" name="disclaimer" rows="3" placeholder="Inserisci eventuali note o avvisi importanti..."></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="logo" class="form-label">Logo</label>
                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                    <small class="form-text text-muted">Formati supportati: JPG, PNG, GIF. Dimensione massima: 2MB</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabella categorie -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Categorie Caricate</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Disciplina</th>
                                        <th>Categoria</th>
                                        <th>Classe</th>
                                        <th>Tipo</th>
                                        <th>Turno</th>
                                        <th>Balli</th>
                                        <th>Registrazioni</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $index => $category): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($category['disciplina']); ?></td>
                                        <td><?php echo htmlspecialchars($category['categoria']); ?></td>
                                        <td><?php echo htmlspecialchars($category['classe']); ?></td>
                                        <td><?php echo htmlspecialchars($category['tipo']); ?></td>
                                        <td><?php echo $category['turno']; ?></td>
                                        <td><?php echo $category['balli']; ?></td>
                                        <td><?php echo $category['da']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-category" data-index="<?php echo $index; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-category" data-index="<?php echo $index; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <button type="submit" form="configureForm" class="btn btn-success">
                    <i class="bi bi-calendar-check"></i> Genera Timetable
                </button>
                <a href="cronologici.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Torna ai Cronologici
                </a>
            </div>
        </div>
    </div>

    <!-- Modal per modifica categoria -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" id="editCategoryIndex">
                        <div class="mb-3">
                            <label class="form-label">Disciplina</label>
                            <input type="text" class="form-control" id="editDisciplina" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoria</label>
                            <input type="text" class="form-control" id="editCategoria" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Classe</label>
                            <input type="text" class="form-control" id="editClasse" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <input type="text" class="form-control" id="editTipo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Turno</label>
                            <select class="form-control" id="editTurno" required>
                                <option value="1">Turno 1</option>
                                <option value="2">Turno 2</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Balli</label>
                            <input type="number" class="form-control" id="editBalli" required min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Registrazioni</label>
                            <input type="number" class="form-control" id="editRegistrazioni" required min="1">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="saveCategoryChanges">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Imposta l'ora di inizio predefinita a 09:00
        document.getElementById('ora_inizio').value = '09:00';
        // Imposta l'ora di apertura porte predefinita a 08:30
        document.getElementById('ora_apertura').value = '08:30';

        // Gestione del form di configurazione
        document.getElementById('configureForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('api/configure_timetable.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new TypeError("La risposta non è in formato JSON!");
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect alla pagina del timetable
                    window.location.href = `crono-view.php?id=${data.timetable_id}`;
                } else {
                    alert('Errore: ' + (data.error || 'Errore sconosciuto'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Si è verificato un errore durante il salvataggio del timetable: ' + error.message);
            }
        });
    </script>
    <script src="js/configure_timetable.js"></script>
</body>
</html> 