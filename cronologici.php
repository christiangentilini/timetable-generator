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
            <h2>I tuoi Cronologici</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTimetableModal">
                <i class="bi bi-plus-circle me-2"></i>Nuovo Cronologico
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Cronologico creato con successo!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (empty($timetables)): ?>
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    Non hai ancora creato nessun cronologico. Clicca su "Nuovo Cronologico" per iniziare!
                </div>
            </div>
            <?php else: ?>
                <?php foreach ($timetables as $timetable): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card timetable-card h-100">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-4">
                                    <img src="<?php echo htmlspecialchars($timetable['logo']); ?>" alt="Logo" class="timetable-logo w-100">
                                </div>
                                <div class="col-8">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($timetable['titolo']); ?></h5>
                                        <?php if ($timetable['access_type'] === 'owner'): ?>
                                            <span class="badge bg-primary">Proprietario</span>
                                        <?php elseif ($timetable['access_type'] === 'edit'): ?>
                                            <span class="badge bg-warning">Modifica</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Visualizzazione</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="card-text text-muted mb-2"><?php echo htmlspecialchars($timetable['sottotitolo']); ?></p>
                                    <p class="card-text mb-1"><?php echo htmlspecialchars($timetable['desc1']); ?></p>
                                    <p class="card-text mb-0"><?php echo htmlspecialchars($timetable['desc2']); ?></p>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <a href="crono-view.php?id=<?php echo $timetable['id']; ?>" class="btn btn-primary">
                                    <i class="bi bi-eye me-2"></i>Visualizza
                                </a>
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
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs mb-3" id="newTimetableTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab" aria-controls="manual" aria-selected="true">Manuale</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="csv-tab" data-bs-toggle="tab" data-bs-target="#csv" type="button" role="tab" aria-controls="csv" aria-selected="false">Importa CSV</button>
                            </li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content" id="newTimetableTabsContent">
                            <!-- Manual Tab -->
                            <div class="tab-pane fade show active" id="manual" role="tabpanel">
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
                                            <button class="nav-link active" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery" type="button" role="tab" aria-controls="gallery" aria-selected="true">Galleria</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab" aria-controls="upload" aria-selected="false">Carica Nuovo</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content mt-3" id="logoTabsContent">
                                        <div class="tab-pane fade show active" id="gallery" role="tabpanel">
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
                                        <div class="tab-pane fade" id="upload" role="tabpanel">
                                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CSV Import Tab -->
                            <div class="tab-pane fade" id="csv" role="tabpanel">
                                <div class="alert alert-info">
                                    <small><i class="bi bi-info-circle me-2"></i>Carica un file CSV per importare le voci del cronologico. Il file deve contenere le seguenti colonne: Orario, Disciplina, Categoria, Classe, Tipo, Turno, Da, A, Balli, Batterie, Pannello.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="csvFile" class="form-label">File CSV</label>
                                    <input type="file" class="form-control" id="csvFile" accept=".csv" required>
                                </div>
                                <div id="csvPreview" class="d-none">
                                    <h6 class="mb-3">Anteprima Importazione</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Orario</th>
                                                    <th>Disciplina</th>
                                                    <th>Categoria</th>
                                                    <th>Classe</th>
                                                    <th>Tipo</th>
                                                    <th>Turno</th>
                                                    <th>Da</th>
                                                    <th>A</th>
                                                    <th>Balli</th>
                                                    <th>Batt.</th>
                                                    <th>Pan.</th>
                                                </tr>
                                            </thead>
                                            <tbody id="csvPreviewBody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary" id="saveTimetableBtn">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logo selection
    document.querySelectorAll('.select-logo').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('selected_logo').value = this.dataset.logo;
        });
    });

    // CSV Import handling
    const csvFile = document.getElementById('csvFile');
    const csvPreview = document.getElementById('csvPreview');
    const csvPreviewBody = document.getElementById('csvPreviewBody');
    let csvData = null;

    csvFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const text = e.target.result;
                const rows = text.split('\n').map(row => row.split(','));
                const headers = rows[0];
                
                // Validate headers
                const requiredHeaders = ['Orario', 'Disciplina', 'Categoria', 'Classe', 'Tipo', 'Turno', 'Da', 'A', 'Balli', 'Batterie', 'Pannello'];
                const missingHeaders = requiredHeaders.filter(h => !headers.includes(h));
                
                if (missingHeaders.length > 0) {
                    alert('Il file CSV non contiene tutte le colonne richieste. Colonne mancanti: ' + missingHeaders.join(', '));
                    csvFile.value = '';
                    return;
                }

                // Store CSV data
                csvData = rows.slice(1).map(row => {
                    const obj = {};
                    headers.forEach((header, index) => {
                        obj[header] = row[index]?.trim() || '';
                    });
                    return obj;
                });

                // Show preview
                csvPreviewBody.innerHTML = '';
                csvData.slice(0, 5).forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row['Orario']}</td>
                        <td>${row['Disciplina']}</td>
                        <td>${row['Categoria']}</td>
                        <td>${row['Classe']}</td>
                        <td>${row['Tipo']}</td>
                        <td>${row['Turno']}</td>
                        <td>${row['Da']}</td>
                        <td>${row['A']}</td>
                        <td>${row['Balli']}</td>
                        <td>${row['Batterie']}</td>
                        <td>${row['Pannello']}</td>
                    `;
                    csvPreviewBody.appendChild(tr);
                });

                csvPreview.classList.remove('d-none');
            };
            reader.readAsText(file);
        }
    });

    // Form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const activeTab = document.querySelector('#newTimetableTabs .nav-link.active');
        if (activeTab.id === 'csv-tab') {
            // Handle CSV import
            if (!csvData) {
                alert('Per favore carica un file CSV valido');
                return;
            }

            // First create the timetable
            const formData = new FormData(this);
            fetch('save_timetable.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Then import the CSV data
                    const columnMapping = {
                        'time_slot': 'Orario',
                        'discipline': 'Disciplina',
                        'category': 'Categoria',
                        'class_name': 'Classe',
                        'type': 'Tipo',
                        'turn': 'Turno',
                        'da': 'Da',
                        'a': 'A',
                        'balli': 'Balli',
                        'batterie': 'Batterie',
                        'pannello': 'Pannello'
                    };

                    return fetch('api/import_csv.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            timetable_id: data.timetable_id,
                            csv_data: csvData,
                            column_mapping: columnMapping
                        })
                    });
                } else {
                    throw new Error(data.error || 'Failed to create timetable');
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'crono-view.php?id=' + data.timetable_id;
                } else {
                    throw new Error(data.error || 'Failed to import CSV data');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        } else {
            // Handle manual creation
            this.submit();
        }
    });
});
</script>
</body>
</html>