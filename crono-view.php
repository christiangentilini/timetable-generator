<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

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
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            padding-top: 80px;
            padding-bottom: 10px;
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
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
            color: #6c757d;
        }
        .card {
            margin-bottom: 1rem;
            padding: 0;
        }
        .card-header {
            padding: 0.5rem 1rem;
        }
        .card-body {
            padding: 1rem;
        }
        .form-label {
            margin-bottom: 0.25rem;
        }
        .form-control, .form-select {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        .table {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .table td, .table th {
            padding: 0.5rem;
        }
        .row {
            margin-bottom: 0.5rem;
            display: flex;
        }
        .col-md-6 {
            display: flex;
        }
        .card {
            margin-bottom: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;

        }
        .card-body {
            flex: 1;
        }
        .mb-2 {
            margin-bottom: 0.5rem !important;
        }
        .mb-3 {
            margin-bottom: 0.75rem !important;
        }
        .mb-4 {
            margin-bottom: 1rem !important;
        }
        .competition-logo {
            max-height: 100px;
        }
        .logo-upload-box {
            min-height: 100px;
            padding: 0.5rem;
        }
        .navbar-toggler {
            padding: 0.5rem;
        }
        
        .version-text {
            font-size: 0.875rem;
            color: #ffffff;
            margin-left: 0.75rem;
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
        .card {
            margin-bottom: 1rem;
            padding: 0;
        }
        .card-header {
            padding: 0.5rem 1rem;
        }
        .card-body {
            padding: 1rem;
        }
        .form-label {
            margin-bottom: 0.25rem;
        }
        .form-control, .form-select {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        .table {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .table td, .table th {
            padding: 0.5rem;
        }
        .row {
            margin-bottom: 0.5rem;
            display: flex;
        }
        .col-md-6 {
            display: flex;
        }
        .card {
            margin-bottom: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;

        }
        .card-body {
            flex: 1;
        }
        .mb-2 {
            margin-bottom: 0.5rem !important;
        }
        .mb-3 {
            margin-bottom: 0.75rem !important;
        }
        .mb-4 {
            margin-bottom: 1rem !important;
        }
        .competition-logo {
            max-height: 100px;
        }
        .logo-upload-box {
            min-height: 100px;
            padding: 0.5rem;
        }
        .version-text {
            font-size: 0.875rem;
        }
        h1 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }
        .card-title {
            font-size: 1.25rem;
            margin-bottom: 0;
        }
        .descriptive-row {
            background-color: #f8f9fa;
            font-style: italic;
            padding: 0.25rem 0.5rem;
        }
        .normal-fields, .descriptive-fields {
            transition: opacity 0.3s ease-in-out;
            margin-top: 20px;
            margin-bottom: 0.5rem;
        }
        .hidden {
            display: none;
            opacity: 0;
        }
        .btn-group-toggle .btn {
            min-width: 100px;
            padding: 0.25rem 0.5rem;
        }
        .competition-logo {
            max-height: 120px;
            object-fit: contain;
        }
        .competition-header {
            margin-bottom: 1.5rem;
        }
        .version-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-left: 0.75rem;
        }
        .logo-upload-box {
            border: 2px dashed #ced4da;
            border-radius: 4px;
            padding: 0.75rem;
            text-align: center;
            cursor: pointer;
            background-color: #f8f9fa;
            height: 100%;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: border-color 0.3s ease;
        }
        .logo-upload-box:hover {
            border-color: #6c757d;
        }
        .logo-upload-box i {
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 0.375rem;
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
                            <li><a class="dropdown-item" href="nuovo.php">Nuovo Cronologico</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="definizioni.php">Definizioni</a></li>
                            <li><a class="dropdown-item" href="profilo.php">Profilo</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
       

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Dati competizione</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <input type="text" class="form-control form-control-lg mb-2" id="competitionTitle" placeholder="Titolo della Competizione" value="<?php echo htmlspecialchars($timetable['titolo']); ?>">
                                <input type="text" class="form-control mb-2" id="competitionSubtitle" placeholder="Sottotitolo" value="<?php echo htmlspecialchars($timetable['sottotitolo']); ?>">
                                <textarea class="form-control mb-2" id="competitionDescription1" rows="1" placeholder="Prima riga di descrizione"><?php echo htmlspecialchars($timetable['desc1']); ?></textarea>
                                <textarea class="form-control mb-2" id="competitionDescription2" rows="1" placeholder="Seconda riga di descrizione"><?php echo htmlspecialchars($timetable['desc2']); ?></textarea>
                                <textarea class="form-control mb-2" id="competitionDisclaimer" rows="2" placeholder="Disclaimer"><?php echo htmlspecialchars($timetable['disclaimer']); ?></textarea>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="logo-upload-box" id="logoUploadBox">
                                    <?php if (!empty($timetable['logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($timetable['logo']); ?>" id="logoPreview" class="competition-logo mb-2" alt="Logo">
                                        <p class="mb-0 d-none" id="uploadText">Carica il logo qui</p>
                                    <?php else: ?>
                                        <i class="bi bi-cloud-upload"></i>
                                        <p class="mb-0" id="uploadText">Carica il logo qui</p>
                                        <img id="logoPreview" class="competition-logo mb-2 d-none" alt="Logo">
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="d-none" id="logoUpload" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Inserisci Voce</h3>
                    </div>
                    <div class="card-body">
                        <form id="competitionForm">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="time" class="form-label small mb-1">Orario</label>
                                    <input type="time" class="form-control form-control-sm" id="time" required>
                                </div>
                                <div class="col-md-10">
                                    <label class="form-label small mb-1">Tipo di riga</label>
                                    <div class="btn-group" role="group" aria-label="Tipo di riga">
                                        <input type="radio" class="btn-check" name="rowType" id="normalRowType" value="normal" checked>
                                        <label class="btn btn-outline-primary btn-sm" for="normalRowType">Normale</label>
                                        <input type="radio" class="btn-check" name="rowType" id="descriptiveRowType" value="descriptive">
                                        <label class="btn btn-outline-primary btn-sm" for="descriptiveRowType">Descrittiva</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Descriptive Row Section -->
                            <div class="descriptive-fields hidden" id="descriptiveFields">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="description" class="form-label small mb-1">Descrizione</label>
                                        <input type="text" class="form-control form-control-sm" id="description">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Normal Row Section -->
                            <div class="normal-fields" id="normalFields">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label for="discipline" class="form-label small mb-1">Disciplina</label>
                                        <input type="text" class="form-control form-control-sm" id="discipline">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="category" class="form-label small mb-1">Categoria</label>
                                        <input type="text" class="form-control form-control-sm" id="category">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="class" class="form-label small mb-1">Classe</label>
                                        <input type="text" class="form-control form-control-sm" id="class">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="type" class="form-label small mb-1">Tipo</label>
                                        <select class="form-select form-select-sm" id="type">
                                            <option value="Solo">Solo</option>
                                            <option value="Coppia">Coppia</option>
                                            <option value="Duo">Duo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="round" class="form-label small mb-1">Turno</label>
                                        <input type="text" class="form-control form-control-sm" id="round" value="1° Turno Finale">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="startNumber" class="form-label small mb-1">Da</label>
                                        <input type="number" class="form-control form-control-sm" id="startNumber" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="endNumber" class="form-label small mb-1">A</label>
                                        <input type="number" class="form-control form-control-sm" id="endNumber" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="dances" class="form-label small mb-1">Balli</label>
                                        <input type="number" class="form-control form-control-sm" id="dances" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="heats" class="form-label small mb-1">Batterie</label>
                                        <input type="number" class="form-control form-control-sm" id="heats" min="1">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="button" id="addRowBtn" class="btn btn-primary btn-sm">Aggiungi Riga</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Timetable</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-light">
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
                                <th>Batterie</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleBody"></tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button onclick="window.print()" class="btn btn-secondary">Stampa Timetable</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timetableId = <?php echo $timetable_id; ?>;
            const updateFields = ['competitionTitle', 'competitionSubtitle', 'competitionDescription1', 'competitionDescription2', 'competitionDisclaimer'];
            const fieldMapping = {
                'competitionTitle': 'titolo',
                'competitionSubtitle': 'sottotitolo',
                'competitionDescription1': 'desc1',
                'competitionDescription2': 'desc2',
                'competitionDisclaimer': 'disclaimer'
            };

            // Toggle between normal and descriptive fields
            const normalRowType = document.getElementById('normalRowType');
            const descriptiveRowType = document.getElementById('descriptiveRowType');
            const normalFields = document.getElementById('normalFields');
            const descriptiveFields = document.getElementById('descriptiveFields');

            normalRowType.addEventListener('change', function() {
                if (this.checked) {
                    normalFields.classList.remove('hidden');
                    descriptiveFields.classList.add('hidden');
                }
            });

            descriptiveRowType.addEventListener('change', function() {
                if (this.checked) {
                    descriptiveFields.classList.remove('hidden');
                    normalFields.classList.add('hidden');
                }
            });

            // Load existing timetable details
            function loadTimetableDetails() {
                fetch(`/api/save_timetable_detail.php?id=${timetableId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        timetable_id: timetableId,
                        entry_type: 'load'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateTimetableDisplay(data.rows);
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            // Update timetable display
            function updateTimetableDisplay(rows) {
                const scheduleBody = document.getElementById('scheduleBody');
                scheduleBody.innerHTML = '';
                
                rows.forEach(row => {
                    const tr = document.createElement('tr');
                    if (row.entry_type === 'descriptive') {
                        tr.classList.add('descriptive-row');
                        tr.innerHTML = `
                            <td>${row.time_slot}</td>
                            <td colspan="9">${row.description}</td>
                        `;
                    } else {
                        tr.innerHTML = `
                            <td>${row.time_slot}</td>
                            <td>${row.discipline || ''}</td>
                            <td>${row.category || ''}</td>
                            <td>${row.class_name || ''}</td>
                            <td>${row.type || ''}</td>
                            <td>${row.turn || ''}</td>
                            <td>${row.da || ''}</td>
                            <td>${row.a || ''}</td>
                            <td>${row.balli || ''}</td>
                            <td>${row.batterie || ''}</td>
                        `;
                    }
                    scheduleBody.appendChild(tr);
                });
            }

            // Load initial data
            loadTimetableDetails();

            // Handle row submission
            document.getElementById('addRowBtn').addEventListener('click', function() {
                const timeSlot = document.getElementById('time').value;
                const rowType = document.querySelector('input[name="rowType"]:checked').value;
                
                if (!timeSlot) {
                    alert('Orario è richiesto');
                    return;
                }
                
                let formData = {
                    timetable_id: timetableId,
                    entry_type: rowType,
                    time_slot: timeSlot
                };
                
                if (rowType === 'descriptive') {
                    const description = document.getElementById('description').value;
                    if (!description) {
                        alert('Descrizione è richiesta per le righe descrittive');
                        return;
                    }
                    formData.description = description;
                } else {
                    const discipline = document.getElementById('discipline').value;
                    const category = document.getElementById('category').value;
                    const class_name = document.getElementById('class').value;
                    const type = document.getElementById('type').value;
                    const turn = document.getElementById('round').value;
                    const da = document.getElementById('startNumber').value;
                    const a = document.getElementById('endNumber').value;
                    const balli = document.getElementById('dances').value;
                    const batterie = document.getElementById('heats').value;
                    
                    if (!discipline || !da || !a || !balli || !batterie) {
                        alert('Tutti i campi contrassegnati sono obbligatori');
                        return;
                    }
                    
                    Object.assign(formData, {
                        discipline,
                        category,
                        class_name,
                        type,
                        turn,
                        da,
                        a,
                        balli,
                        batterie
                    });
                }
                
                saveFormData(formData);
            });

            // Common function to save form data
            function saveFormData(formData) {
                fetch('/api/save_timetable_detail.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateTimetableDisplay(data.rows);
                        resetForm();
                    } else {
                        console.error('Save failed:', data.error);
                        alert('Errore durante il salvataggio: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore durante il salvataggio');
                });
            }

            // Reset form fields
            function resetForm() {
                document.getElementById('time').value = '';
                document.getElementById('description').value = '';
                document.getElementById('discipline').value = '';
                document.getElementById('category').value = '';
                document.getElementById('class').value = '';
                document.getElementById('type').value = 'Solo';
                document.getElementById('round').value = '1° Turno Finale';
                document.getElementById('startNumber').value = '';
                document.getElementById('endNumber').value = '';
                document.getElementById('dances').value = '';
                document.getElementById('heats').value = '';
            }

            let updateTimeout;
            updateFields.forEach(fieldId => {
                const element = document.getElementById(fieldId);
                if (element) {
                    element.addEventListener('input', function(e) {
                        clearTimeout(updateTimeout);
                        const field = fieldMapping[fieldId];
                        const value = e.target.value;

                        updateTimeout = setTimeout(() => {
                            fetch('/api/update_timetable.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    field: field,
                                    value: value,
                                    timetable_id: timetableId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    console.error('Update failed:', data.error);
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        }, 500);
                    });
                }
            });
        });
    </script>
</body>
</html>