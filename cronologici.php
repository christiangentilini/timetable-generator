<?php
require_once 'config/database.php';
require_once 'config/session_check.php';
require_once 'includes/header.php';

// Fetch timetables for current user (both created and shared)
$user_id = $_SESSION['user_id'];

// Query per ottenere sia i cronologici creati dall'utente che quelli condivisi con lui
$query = "SELECT t.*, 
          CASE 
              WHEN t.user_created = ? THEN 'owner' 
              ELSE ts.permission_level 
          END as access_type 
          FROM timetables t 
          LEFT JOIN timetable_shares ts ON t.id = ts.timetable_id AND ts.user_id = ? 
          WHERE t.user_created = ? OR ts.id IS NOT NULL 
          ORDER BY t.id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$timetables = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronologici - Timetable Generator</title>
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
        .timetable-card {
            transition: transform 0.2s;
        }
        .timetable-card:hover {
            transform: translateY(-5px);
        }
        .card-text {
            font-size: 0.9rem;
            line-height: 1.4;
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
            <h2>Cronologici</h2>
            <div>
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#uploadCsvModal">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Genera da CSV
                </button>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTimetableModal">
                    <i class="bi bi-plus-circle me-2"></i>Nuovo Cronologico
                </a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Cronologico creato con successo!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <?php if (empty($timetables)): ?>
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    Non hai ancora creato nessun cronologico. Clicca su "Nuovo Cronologico" per iniziare!
                </div>
            </div>
            <?php else: ?>
                <?php foreach ($timetables as $timetable): ?>
                <div class="col-12 mb-4">
                    <div class="card timetable-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex flex-wrap align-items-center" style="flex: 1;">
                                    <div class="flex-shrink-0 me-3" style="width: 120px">
                                        <img src="<?php echo htmlspecialchars($timetable['logo']); ?>" alt="Logo" class="timetable-logo w-100">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($timetable['titolo']); ?></h5>
                                        <p class="card-text text-muted mb-2"><?php echo htmlspecialchars($timetable['sottotitolo']); ?></p>
                                        <p class="card-text mb-1"><?php echo htmlspecialchars($timetable['desc1']); ?></p>
                                        <p class="card-text mb-0"><?php echo htmlspecialchars($timetable['desc2']); ?></p>
                                    </div>
                                </div>
                                <div class="d-flex flex-column justify-content-between" style="width: auto;">
                                    <div class="d-flex justify-content-end">
                                        <?php if ($timetable['access_type'] === 'owner'): ?>
                                            <span class="badge bg-primary">Proprietario</span>
                                        <?php elseif ($timetable['access_type'] === 'edit'): ?>
                                            <span class="badge bg-warning">Modifica</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Visualizzazione</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="crono-view.php?id=<?php echo $timetable['id']; ?>" class="btn btn-primary">
                                            <i class="bi bi-eye me-2"></i>Visualizza
                                        </a>
                                        <?php if ($timetable['access_type'] === 'owner'): ?>
                                            <button class="btn btn-success duplicate-timetable" data-id="<?php echo $timetable['id']; ?>">
                                                <i class="bi bi-files me-2"></i>Duplica
                                            </button>
                                            <button class="btn btn-danger delete-timetable" data-id="<?php echo $timetable['id']; ?>">
                                                <i class="bi bi-trash me-2"></i>Elimina
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- New Timetable Modal -->
    <div class="modal fade" id="newTimetableModal" tabindex="-1" aria-labelledby="newTimetableModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newTimetableModalLabel">Nuovo Cronologico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="save_timetable.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="titolo" class="form-label">Titolo</label>
                            <input type="text" class="form-control" id="titolo" name="titolo" required>
                        </div>
                        <div class="mb-3">
                            <label for="sottotitolo" class="form-label">Sottotitolo</label>
                            <input type="text" class="form-control" id="sottotitolo" name="sottotitolo" required>
                        </div>
                        <div class="mb-3">
                            <label for="desc1" class="form-label">Descrizione 1</label>
                            <textarea class="form-control" id="desc1" name="desc1" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="desc2" class="form-label">Descrizione 2</label>
                            <textarea class="form-control" id="desc2" name="desc2" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="disclaimer" class="form-label">Disclaimer</label>
                            <textarea class="form-control" id="disclaimer" name="disclaimer" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <ul class="nav nav-tabs" id="logoTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">Carica Logo</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery" type="button" role="tab">Galleria</button>
                                </li>
                            </ul>
                            <div class="tab-content mt-3" id="logoTabsContent">
                                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                </div>
                                <div class="tab-pane fade" id="gallery" role="tabpanel">
                                    <div class="row g-3" id="logoGallery">
                                        <?php
                                        $logos_dir = __DIR__ . '/assets/logos/';
                                        $logos = glob($logos_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                                        foreach ($logos as $logo) {
                                            $logo_url = 'assets/logos/' . basename($logo);
                                            echo '<div class="col-4">
                                                <div class="card h-100">
                                                    <img src="' . $logo_url . '" class="card-img-top p-2" alt="Logo">
                                                    <div class="card-body text-center">
                                                        <button type="button" class="btn btn-sm btn-primary select-logo" data-logo="' . $logo_url . '">Seleziona</button>
                                                    </div>
                                                </div>
                                            </div>';
                                        }
                                        ?>
                                    </div>
                                    <input type="hidden" name="selected_logo" id="selected_logo">
                                </div>
                            </div>
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

    <!-- Modal per l'upload del CSV -->
    <div class="modal fade" id="uploadCsvModal" tabindex="-1" aria-labelledby="uploadCsvModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadCsvModalLabel">Genera Timetable da CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="csvUploadForm">
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Seleziona il file CSV</label>
                            <input type="file" class="form-control" id="csvFile" accept=".csv" required>
                            <div class="form-text">
                                Il file CSV deve contenere le seguenti colonne:<br>
                                disciplina, categoria, classe, tipo, turno, da, a, balli, batterie
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <h6>Formato del CSV:</h6>
                            <p class="mb-0">
                                - Prima riga: intestazioni delle colonne<br>
                                - Righe successive: dati delle categorie<br>
                                - I numeri (da, a, balli, batterie) devono essere interi<br>
                                - Le categorie devono essere: Principianti, Intermedi, Avanzati
                            </p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="uploadCsvBtn">Genera Timetable</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestione duplicazione cronologico
    document.querySelectorAll('.duplicate-timetable').forEach(button => {
        button.addEventListener('click', function() {
            const timetableId = this.dataset.id;
            
            if (confirm('Sei sicuro di voler duplicare questo cronologico?')) {
                fetch('api/duplicate_timetable.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: timetableId })
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
                    alert('Si è verificato un errore durante la duplicazione');
                });
            }
        });
    });

    // Gestione eliminazione cronologico
    document.querySelectorAll('.delete-timetable').forEach(button => {
        button.addEventListener('click', function() {
            const timetableId = this.dataset.id;
            
            if (confirm('Sei sicuro di voler eliminare questo cronologico? Tutti i dati associati verranno cancellati.')) {
                fetch('api/delete_timetable.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: timetableId })
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
                    alert('Si è verificato un errore durante l\'eliminazione');
                });
            }
        });
    });

    // Gestione selezione logo dalla galleria
    document.querySelectorAll('.select-logo').forEach(button => {
        button.addEventListener('click', function() {
            const logoPath = this.dataset.logo;
            document.getElementById('selected_logo').value = logoPath;
            
            // Evidenzia il logo selezionato
            document.querySelectorAll('.select-logo').forEach(btn => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
                btn.textContent = 'Seleziona';
            });
            this.classList.remove('btn-primary');
            this.classList.add('btn-success');
            this.textContent = 'Selezionato';
            
            // Disabilita il campo di upload
            document.getElementById('logo').value = '';
            document.getElementById('logo').disabled = true;
        });
    });
    
    // Gestione cambio tab
    document.getElementById('upload-tab').addEventListener('click', function() {
        document.getElementById('logo').disabled = false;
        document.getElementById('selected_logo').value = '';
        
        // Resetta i pulsanti della galleria
        document.querySelectorAll('.select-logo').forEach(btn => {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            btn.textContent = 'Seleziona';
        });
    });

    // Gestione upload CSV
    document.getElementById('uploadCsvBtn').addEventListener('click', function() {
        const fileInput = document.getElementById('csvFile');
        const file = fileInput.files[0];
        
        if (!file) {
            alert('Seleziona un file CSV');
            return;
        }
        
        const formData = new FormData();
        formData.append('csv_file', file);
        
        fetch('api/upload_csv.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Timetable generato con successo!');
                window.location.href = 'configure_timetable.php';
            } else {
                alert('Errore: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Si è verificato un errore durante l\'upload del file');
        });
    });
});
</script>
</body>
</html>