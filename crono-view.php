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
            border-radius: 0.375rem;
            margin-right: 0.5rem;
        }
        .btn:last-child {
            margin-right: 0;
        }
        .table {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .table td, .table th {
            padding: 0.5rem;
            text-align: center;
        }
        .table tr {
            border-radius: 8px;
            overflow: hidden;
            background-color: #ffffff;
        }
        .table tr:hover {
            background-color: #f8f9fa;
        }
        .table td:first-child,
        .table td:nth-child(7),
        .table td:nth-child(8),
        .table td:nth-child(9),
        .table td:nth-child(10) {
            width: 30px;
            min-width: 30px;
            max-width: 30px;
        }
        .draggable-row {
            cursor: move;
        }
        .drag-handle {
            cursor: move;
            padding: 4px;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 14px;
        }
        #dragHandleContainer, #actionContainer {
            display: flex;
            flex-direction: column;
            margin-top: 42px;
            height: auto;
        }

        #actionContainer {
            padding: 0 50px;
        }
        
        #dragHandleContainer > div, #actionContainer > div {
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #dragHandleContainer .drag-handle, #actionContainer .btn-sm {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 24px;
            width: 24px;
            padding: 0;
            margin-bottom: 4px;
        }
        
        .descriptive-row + #dragHandleContainer > div, .descriptive-row + #actionContainer > div {
            background-color: #f8f9fa;
            height: 38px;
        }
        .row {
            margin-bottom: 0.5rem;
            display: flex;
        }
        .col-md-6 {
            display: flex;

            .left {
                padding-right:0px !important;
            }

            .right {
                padding-left:0px !important;
            }
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
        .table td:first-child,
        .table td:nth-child(7),
        .table td:nth-child(8),
        .table td:nth-child(9),
        .table td:nth-child(10) {
            text-align: center;
        }
        .draggable-row {
            cursor: move;
        }
        .drag-handle {
            cursor: move;
            padding: 8px;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        #dragHandleContainer, #actionContainer {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        #dragHandleContainer > div, #actionContainer > div {
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .descriptive-row + #dragHandleContainer > div, .descriptive-row + #actionContainer > div {
            background-color: #f8f9fa;
        }
        .row {
            margin-bottom: 0.5rem;
            display: flex;
        }
        .col-md-6 {
            display: flex;

            .left {
                padding-right:0px !important;
            }

            .right {
                padding-left:0px !important;
            }

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
            font-weight: bold;
            text-align: center;
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
            <div class="col-md-6 left">
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
            <div class="col-md-6 right">
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
                <div class="d-flex">
                    <div class="me-2" id="dragHandleContainer" style="width: 30px;"></div>
                    <div class="flex-grow-1">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30px; text-align: center;">Orario</th>
                                        <th style="width: 100px; text-align: center;">Disciplina</th>
                                        <th style="width: 40px; text-align: center;">Categoria</th>
                                        <th style="width: 35px; text-align: center;">Classe</th>
                                        <th style="width: 100px; text-align: center;">Tipo</th>
                                        <th style="width: 100px; text-align: center;">Turno</th>
                                        <th style="width: 35px; text-align: center;">Da</th>
                                        <th style="width: 35px; text-align: center;">A</th>
                                        <th style="width: 35px; text-align: center;">Balli</th>
                                        <th style="width: 15px; text-align: center;">Batterie</th>
                                    </tr>
                                </thead>
                                <tbody id="scheduleBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="ms-2" id="actionContainer" style="width: 40px;"></div>
                </div>
                <div class="mt-3">
                    <button onclick="window.print()" class="btn btn-secondary">Stampa Timetable</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Modifica Riga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editRowId">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="editTime" class="form-label small mb-1">Orario</label>
                                <input type="time" class="form-control form-control-sm" id="editTime" required>
                            </div>
                            <div class="col-md-10">
                                <label class="form-label small mb-1">Tipo di riga</label>
                                <div class="btn-group" role="group" aria-label="Tipo di riga">
                                    <input type="radio" class="btn-check" name="editRowType" id="editNormalRowType" value="normal">
                                    <label class="btn btn-outline-primary btn-sm" for="editNormalRowType">Normale</label>
                                    <input type="radio" class="btn-check" name="editRowType" id="editDescriptiveRowType" value="descriptive">
                                    <label class="btn btn-outline-primary btn-sm" for="editDescriptiveRowType">Descrittiva</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit Descriptive Row Section -->
                        <div class="descriptive-fields hidden" id="editDescriptiveFields">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="editDescription" class="form-label small mb-1">Descrizione</label>
                                    <input type="text" class="form-control form-control-sm" id="editDescription">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit Normal Row Section -->
                        <div class="normal-fields" id="editNormalFields">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="editDiscipline" class="form-label small mb-1">Disciplina</label>
                                    <input type="text" class="form-control form-control-sm" id="editDiscipline">
                                </div>
                                <div class="col-md-2">
                                    <label for="editCategory" class="form-label small mb-1">Categoria</label>
                                    <input type="text" class="form-control form-control-sm" id="editCategory">
                                </div>
                                <div class="col-md-2">
                                    <label for="editClass" class="form-label small mb-1">Classe</label>
                                    <input type="text" class="form-control form-control-sm" id="editClass">
                                </div>
                                <div class="col-md-2">
                                    <label for="editType" class="form-label small mb-1">Tipo</label>
                                    <select class="form-select form-select-sm" id="editType">
                                        <option value="Solo">Solo</option>
                                        <option value="Coppia">Coppia</option>
                                        <option value="Duo">Duo</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="editRound" class="form-label small mb-1">Turno</label>
                                    <input type="text" class="form-control form-control-sm" id="editRound">
                                </div>
                                <div class="col-md-2">
                                    <label for="editStartNumber" class="form-label small mb-1">Da</label>
                                    <input type="number" class="form-control form-control-sm" id="editStartNumber" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="editEndNumber" class="form-label small mb-1">A</label>
                                    <input type="number" class="form-control form-control-sm" id="editEndNumber" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="editDances" class="form-label small mb-1">Balli</label>
                                    <input type="number" class="form-control form-control-sm" id="editDances" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="editHeats" class="form-label small mb-1">Batterie</label>
                                    <input type="number" class="form-control form-control-sm" id="editHeats" min="1">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" onclick="saveEditedRow()">Salva</button>
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
                const dragHandleContainer = document.getElementById('dragHandleContainer');
                const actionContainer = document.getElementById('actionContainer');
                
                scheduleBody.innerHTML = '';
                dragHandleContainer.innerHTML = '';
                actionContainer.innerHTML = '';
                
                rows.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.draggable = true;
                    tr.dataset.rowId = row.id;
                    tr.classList.add('draggable-row');
                    if (row.entry_type === 'descriptive') {
                        tr.classList.add('descriptive-row');
                        tr.innerHTML = `
                            <td>${row.time_slot}</td>
                            <td colspan="9">${row.description}</td>
                        `;
                        
                        const dragHandle = document.createElement('div');
                        dragHandle.innerHTML = `<i class="bi bi-grip-vertical drag-handle"></i>`;
                        document.getElementById('dragHandleContainer').appendChild(dragHandle);
                        
                        const actionButton = document.createElement('div');
                        actionButton.innerHTML = `
                            <div class="d-flex">
                                <button class="btn btn-warning btn-sm" onclick="editRow(${row.id})"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-primary btn-sm" onclick="duplicateRow(${row.id})"><i class="bi bi-files"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRow(${row.id})"><i class="bi bi-trash"></i></button>
                            </div>
                        `;
                        document.getElementById('actionContainer').appendChild(actionButton);
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
                        
                        const dragHandle = document.createElement('div');
                        dragHandle.innerHTML = `<i class="bi bi-grip-vertical drag-handle"></i>`;
                        document.getElementById('dragHandleContainer').appendChild(dragHandle);
                        
                        const actionButton = document.createElement('div');
                        actionButton.innerHTML = `
                            <div class="d-flex">
                                <button class="btn btn-warning btn-sm" onclick="editRow(${row.id})"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-primary btn-sm" onclick="duplicateRow(${row.id})"><i class="bi bi-files"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRow(${row.id})"><i class="bi bi-trash"></i></button>
                            </div>
                        `;
                        document.getElementById('actionContainer').appendChild(actionButton);
                    }
                    scheduleBody.appendChild(tr);
                });
            }

            // Duplicate row function
            window.duplicateRow = function(rowId) {
                fetch('/api/duplicate_timetable_detail.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        timetable_id: timetableId,
                        row_id: rowId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateTimetableDisplay(data.rows);
                    } else {
                        alert('Errore durante la duplicazione: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore durante la duplicazione');
                });
            };

            // Delete row function
            window.deleteRow = function(rowId) {
                if (confirm('Sei sicuro di voler eliminare questa riga?')) {
                    fetch('/api/save_timetable_detail.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            timetable_id: timetableId,
                            entry_type: 'delete',
                            row_id: rowId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateTimetableDisplay(data.rows);
                        } else {
                            alert('Errore durante l\'eliminazione: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Errore durante l\'eliminazione');
                    });
                }
            };

            // Edit row function
            window.editRow = function(rowId) {
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
                        const row = data.rows.find(r => r.id === rowId);
                        if (row) {
                            document.getElementById('editRowId').value = row.id;
                            document.getElementById('editTime').value = row.time_slot;
                            
                            if (row.entry_type === 'descriptive') {
                                document.getElementById('editDescriptiveRowType').checked = true;
                                document.getElementById('editDescription').value = row.description;
                                document.getElementById('editDescriptiveFields').classList.remove('hidden');
                                document.getElementById('editNormalFields').classList.add('hidden');
                            } else {
                                document.getElementById('editNormalRowType').checked = true;
                                document.getElementById('editDiscipline').value = row.discipline || '';
                                document.getElementById('editCategory').value = row.category || '';
                                document.getElementById('editClass').value = row.class_name || '';
                                document.getElementById('editType').value = row.type || 'Solo';
                                document.getElementById('editRound').value = row.turn || '';
                                document.getElementById('editStartNumber').value = row.da || '';
                                document.getElementById('editEndNumber').value = row.a || '';
                                document.getElementById('editDances').value = row.balli || '';
                                document.getElementById('editHeats').value = row.batterie || '';
                                document.getElementById('editNormalFields').classList.remove('hidden');
                                document.getElementById('editDescriptiveFields').classList.add('hidden');
                            }
                            
                            new bootstrap.Modal(document.getElementById('editModal')).show();
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            };

            // Save edited row
            window.saveEditedRow = function() {
                const rowId = document.getElementById('editRowId').value;
                const rowType = document.querySelector('input[name="editRowType"]:checked').value;
                const timeSlot = document.getElementById('editTime').value;
                
                if (!timeSlot) {
                    alert('Orario è richiesto');
                    return;
                }
                
                let formData = {
                    timetable_id: timetableId,
                    row_id: parseInt(rowId),
                    entry_type: rowType,
                    time_slot: timeSlot
                };
                
                if (rowType === 'descriptive') {
                    const description = document.getElementById('editDescription').value;
                    if (!description) {
                        alert('Descrizione è richiesta per le righe descrittive');
                        return;
                    }
                    formData.description = description;
                } else {
                    formData.discipline = document.getElementById('editDiscipline').value;
                    formData.category = document.getElementById('editCategory').value;
                    formData.class_name = document.getElementById('editClass').value;
                    formData.type = document.getElementById('editType').value;
                    formData.turn = document.getElementById('editRound').value;
                    formData.da = document.getElementById('editStartNumber').value;
                    formData.a = document.getElementById('editEndNumber').value;
                    formData.balli = document.getElementById('editDances').value;
                    formData.batterie = document.getElementById('editHeats').value;
                }
                
                fetch('/api/edit_timetable_detail.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                        updateTimetableDisplay(data.rows);
                    } else {
                        alert('Errore durante il salvataggio: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore durante il salvataggio');
                });
            };

            // Toggle between normal and descriptive fields in edit modal
            document.getElementById('editNormalRowType').addEventListener('change', function() {
                if (this.checked) {
                    document.getElementById('editNormalFields').classList.remove('hidden');
                    document.getElementById('editDescriptiveFields').classList.add('hidden');
                }
            });

            document.getElementById('editDescriptiveRowType').addEventListener('change', function() {
                if (this.checked) {
                    document.getElementById('editDescriptiveFields').classList.remove('hidden');
                    document.getElementById('editNormalFields').classList.add('hidden');
                }
            });

            // Load initial data
            loadTimetableDetails();

            // Drag and drop functionality
            const scheduleBody = document.getElementById('scheduleBody');
            let draggedRow = null;

            scheduleBody.addEventListener('dragstart', (e) => {
                draggedRow = e.target.closest('tr');
                e.target.style.opacity = '0.5';
            });

            scheduleBody.addEventListener('dragend', (e) => {
                e.target.style.opacity = '';
            });

            scheduleBody.addEventListener('dragover', (e) => {
                e.preventDefault();
                const row = e.target.closest('tr');
                if (row && row !== draggedRow) {
                    const rect = row.getBoundingClientRect();
                    const midpoint = rect.top + rect.height / 2;
                    if (e.clientY < midpoint) {
                        row.parentNode.insertBefore(draggedRow, row);
                    } else {
                        row.parentNode.insertBefore(draggedRow, row.nextSibling);
                    }
                }
            });

            scheduleBody.addEventListener('dragend', (e) => {
                const rows = Array.from(scheduleBody.querySelectorAll('tr'));
                const orderData = rows.map((row, index) => ({
                    id: parseInt(row.dataset.rowId),
                    order: index + 1
                }));

                fetch('/api/update_timetable_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        timetable_id: timetableId,
                        order_data: orderData
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Reorder failed:', data.error);
                        loadTimetableDetails(); // Reload original order if failed
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadTimetableDetails(); // Reload original order if failed
                });
            });

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