<?php
require_once 'config/database.php';
require_once 'config/session_check.php';
require_once 'includes/header.php';


// Get timetable ID from URL
$timetable_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch definizioni data for dropdowns
$definizioni = [];
$types = ['disciplina', 'categoria', 'classe', 'tipo', 'turno'];
foreach ($types as $type) {
    $stmt = $conn->prepare("SELECT * FROM definizioni WHERE definition_parent = ? ORDER BY definition ASC");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $definizioni[$type] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch timetable data
$timetable = null;
$is_owner = false;
$can_edit = false;

if ($timetable_id > 0) {
    // Prima verifica se l'utente è il proprietario
    $stmt = $conn->prepare("SELECT * FROM timetables WHERE id = ? AND user_created = ?");
    $stmt->bind_param("ii", $timetable_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $timetable = $result->fetch_assoc();
    $stmt->close();
    
    if ($timetable) {
        $is_owner = true;
        $can_edit = true;
    } else {
        // Se non è il proprietario, verifica se è condiviso con l'utente
        $stmt = $conn->prepare("SELECT t.*, ts.permission_level 
                              FROM timetables t 
                              JOIN timetable_shares ts ON t.id = ts.timetable_id 
                              WHERE t.id = ? AND ts.user_id = ?");
        $stmt->bind_param("ii", $timetable_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $timetable = $result->fetch_assoc();
        $stmt->close();
        
        if ($timetable) {
            $can_edit = ($timetable['permission_level'] === 'edit');
        }
    }
}

if (!$timetable) {
    header("Location: cronologici.php");
    exit;
}

// Set the page title
$pageTitle = "Nuovo Cronologico - Timetable Generator";
?>

<style>
        body {
            padding-top: 80px;
            padding-bottom: 100px;
            background-color: #f8f9fa;
        }
        .required-field:after {
            content: ' *';
            color: #dc3545;
            font-weight: bold;
        }
        /* Removed red border styling for required inputs */
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
            margin-right: 1rem;
        }
        .card:last-child {
            margin-right: 0;
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
        #dragHandleContainer, #actionContainer, #checkboxContainer {
            display: flex;
            flex-direction: column;
            margin-top: 42px;
            height: auto;
        }

        #actionContainer {
            padding: 0 50px;
        }
        
        #dragHandleContainer > div, #actionContainer > div, #checkboxContainer > div {
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
        
        .descriptive-row + #dragHandleContainer > div, .descriptive-row + #actionContainer > div, .descriptive-row + #checkboxContainer > div {
            background-color: #f8f9fa;
            height: 38px;
        }
        .row {
            margin-bottom: 2rem;
            display: flex;
        }
        .batch-actions {
            margin-bottom: 1rem;
            padding: 0.75rem;
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
        }
        .batch-actions .btn {
            margin-right: 0.5rem;
        }
        .row-checkbox {
            width: 18px;
            height: 18px;
        }
        .selected-row {
            background-color: #e2f0ff !important;
        }
        .selection-info {
            font-size: 0.875rem;
            margin-right: 1rem;
        }
        .col-md-6 {
            display: flex;
            padding: 0;
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

            left {
    margin-right: 5px;
            }

            right {
    margin-left: 5px;
            }
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


            left {
    margin-right: 5px;
            }

            right {
    margin-left: 5px;
            }
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
            border-radius: 0.375rem;
            margin-right: 0.25rem;
        }
        .btn-group-toggle .btn:last-child {
            margin-right: 0;
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
            <h2><?php echo htmlspecialchars($timetable['titolo']); ?></h2>
            <div>
                <?php if ($is_owner): ?>
                <a href="share_timetable.php?id=<?php echo $timetable_id; ?>" class="btn btn-outline-primary me-2">
                    <i class="bi bi-share me-2"></i>Condividi
                </a>
                <?php endif; ?>
                <a href="cronologici.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Torna ai Cronologici
                </a>
            </div>
        </div>
        
        <?php if (!$can_edit): ?>
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i> Stai visualizzando un cronologico condiviso in modalità sola lettura. Non hai i permessi per modificarlo.
        </div>
        <?php endif; ?>
       

        <div class="row">
            <div class="col-md-6 left">
                <div class="card mb-4 left" style="margin-right: 10px;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Dati competizione</h3>
                        <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#competitionDataCollapse" aria-expanded="true" aria-controls="competitionDataCollapse">
                            <i class="bi bi-chevron-up" id="competitionDataCollapseIcon"></i>
                        </button>
                    </div>
                    <div class="card-body collapse show" id="competitionDataCollapse">
                        <div class="row">
                            <div class="col-md-9">
                                <?php if ($can_edit): ?>
                                <input type="text" class="form-control form-control-lg mb-2" id="competitionTitle" placeholder="Titolo della Competizione" value="<?php echo htmlspecialchars($timetable['titolo']); ?>">
                                <input type="text" class="form-control mb-2" id="competitionSubtitle" placeholder="Sottotitolo" value="<?php echo htmlspecialchars($timetable['sottotitolo']); ?>">
                                <textarea class="form-control mb-2" id="competitionDescription1" rows="1" placeholder="Prima riga di descrizione"><?php echo htmlspecialchars($timetable['desc1']); ?></textarea>
                                <textarea class="form-control mb-2" id="competitionDescription2" rows="1" placeholder="Seconda riga di descrizione"><?php echo htmlspecialchars($timetable['desc2']); ?></textarea>
                                <textarea class="form-control mb-2" id="competitionDisclaimer" rows="2" placeholder="Disclaimer"><?php echo htmlspecialchars($timetable['disclaimer']); ?></textarea>
                                <?php else: ?>
                                <h3 class="mb-2"><?php echo htmlspecialchars($timetable['titolo']); ?></h3>
                                <h5 class="mb-2"><?php echo htmlspecialchars($timetable['sottotitolo']); ?></h5>
                                <p class="mb-2"><?php echo htmlspecialchars($timetable['desc1']); ?></p>
                                <p class="mb-2"><?php echo htmlspecialchars($timetable['desc2']); ?></p>
                                <p class="mb-2 text-muted"><small><?php echo htmlspecialchars($timetable['disclaimer']); ?></small></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 text-center">
                                <?php if ($can_edit): ?>
                                <div class="logo-upload-box" id="logoUploadBox" data-bs-toggle="modal" data-bs-target="#logoModal">
                                    <?php if (!empty($timetable['logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($timetable['logo']); ?>" id="logoPreview" class="competition-logo mb-2" alt="Logo">
                                        <p class="mb-0 d-none" id="uploadText">Clicca per modificare il logo</p>
                                    <?php else: ?>
                                        <i class="bi bi-cloud-upload"></i>
                                        <p class="mb-0" id="uploadText">Clicca per selezionare o caricare un logo</p>
                                        <img id="logoPreview" class="competition-logo mb-2 d-none" alt="Logo">
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="d-none" id="logoUpload" accept="image/*">
                                
                                <!-- Logo Modal -->
                                <div class="modal fade" id="logoModal" tabindex="-1" aria-labelledby="logoModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="logoModalLabel">Seleziona o Carica Logo</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="nav nav-tabs mb-3" id="logoTabs" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="existing-tab" data-bs-toggle="tab" data-bs-target="#existing" type="button" role="tab">Loghi Esistenti</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">Carica Nuovo</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content" id="logoTabsContent">
                                                    <div class="tab-pane fade show active" id="existing" role="tabpanel">
                                                        <div class="row g-3" id="existingLogos">
                                                            <?php
                                                            $stmt = $conn->prepare("SELECT * FROM definizioni WHERE definition_parent = 'logo' ORDER BY definition ASC");
                                                            $stmt->execute();
                                                            $logos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                                            foreach ($logos as $logo): ?>
                                                                <div class="col-md-3 text-center">
                                                                    <div class="logo-item p-2 border rounded cursor-pointer" onclick="selectLogo('<?php echo htmlspecialchars($logo['image_path']); ?>')">
                                                                        <img src="<?php echo htmlspecialchars($logo['image_path']); ?>" class="img-fluid mb-2" alt="<?php echo htmlspecialchars($logo['definition']); ?>">
                                                                        <p class="mb-0 small"><?php echo htmlspecialchars($logo['definition']); ?></p>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="upload" role="tabpanel">
                                                        <div class="text-center p-4 border rounded">
                                                            <i class="bi bi-cloud-upload fs-1 mb-3"></i>
                                                            <p>Trascina un file qui o clicca per selezionare</p>
                                                            <input type="file" class="d-none" id="newLogoUpload" accept="image/*">
                                                            <button class="btn btn-primary" onclick="document.getElementById('newLogoUpload').click()">Seleziona File</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="text-center">
                                    <?php if (!empty($timetable['logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($timetable['logo']); ?>" class="competition-logo mb-2" alt="Logo">
                                    <?php else: ?>
                                        <div class="p-4 text-muted">
                                            <i class="bi bi-image"></i>
                                            <p class="mb-0">Nessun logo</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 right">
                <?php if ($can_edit): ?>
                <div class="card mb-4 right" style="margin-left: 10px;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Inserisci Voce</h3>
                        <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#insertEntryCollapse" aria-expanded="true" aria-controls="insertEntryCollapse">
                            <i class="bi bi-chevron-up" id="insertEntryCollapseIcon"></i>
                        </button>
                    </div>
                    <div class="card-body collapse show" id="insertEntryCollapse">
                <?php else: ?>
                <div class="card mb-4 right" style="margin-left: 10px;">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Informazioni</h3>
                    </div>
                    <div class="card-body">
                        <p>Stai visualizzando questo cronologico in modalità sola lettura.</p>
                <?php endif; ?>
                        <div class="alert alert-info mb-2" role="alert">
                            <small><i class="bi bi-info-circle me-1"></i> I campi contrassegnati con <span style="color: #dc3545; font-weight: bold;">*</span> sono obbligatori.</small>
                        </div>
                        <form id="competitionForm">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="time" class="form-label small mb-1 required-field">Orario</label>
                                    <input type="time" class="form-control form-control-sm" id="time" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small mb-1">Tipo di riga</label>
                                    <div class="d-flex gap-2">
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
                                        <label for="description" class="form-label small mb-1 required-field">Descrizione</label>
                                        <input type="text" class="form-control form-control-sm" id="description">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Normal Row Section -->
                            <div class="normal-fields" id="normalFields">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label for="discipline" class="form-label small mb-1 required-field">Disciplina</label>
                                        <select class="form-control form-control-sm" id="discipline">
                                            <?php foreach ($definizioni['disciplina'] as $def): ?>
                                                <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="category" class="form-label small mb-1 required-field">Categoria</label>
                                        <select class="form-control form-control-sm" id="category">
                                            <?php foreach ($definizioni['categoria'] as $def): ?>
                                                <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="class" class="form-label small mb-1 required-field">Classe</label>
                                        <select class="form-control form-control-sm" id="className">
                                            <?php foreach ($definizioni['classe'] as $def): ?>
                                                <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="type" class="form-label small mb-1 required-field">Tipo</label>
                                        <select class="form-select form-select-sm" id="type">
                                            <?php foreach ($definizioni['tipo'] as $def): ?>
                                                <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="turno_numero" class="form-label small mb-1 required-field">N°</label>
                                        <input type="number" class="form-control form-control-sm" id="turno_numero" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="turno_definition" class="form-label small mb-1 required-field">Turno</label>
                                        <select class="form-select form-select-sm" id="turno_definition">
                                            <?php foreach ($definizioni['turno'] as $def): ?>
                                                <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-2">
                                        <label for="startNumber" class="form-label small mb-1 required-field">Da</label>
                                        <input type="number" class="form-control form-control-sm" id="startNumber" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="endNumber" class="form-label small mb-1 required-field">A</label>
                                        <input type="number" class="form-control form-control-sm" id="endNumber" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="dances" class="form-label small mb-1 required-field">Balli</label>
                                        <input type="number" class="form-control form-control-sm" id="dances" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="heats" class="form-label small mb-1 required-field">Batterie</label>
                                        <input type="number" class="form-control form-control-sm" id="heats" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="pannello" class="form-label small mb-1">Pannello</label>
                                        <input type="text" class="form-control form-control-sm" id="pannello" maxlength="5">
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Timetable</h3>
                <div>
                    <a href="generate_pdf.php?id=<?php echo $timetable_id; ?>&show_pannello=0" class="btn btn-secondary btn-sm" target="_blank">Stampa Timetable</a>
                    <a href="generate_pdf.php?id=<?php echo $timetable_id; ?>&show_pannello=1" class="btn btn-secondary btn-sm" target="_blank">Stampa con Pannelli</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($can_edit): ?>
                <div class="batch-actions">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="selection-info">0 righe selezionate</span>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">Seleziona Tutte</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn">Deseleziona Tutte</button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary btn-sm" id="batchPannelloBtn" disabled>Assegna Pannello</button>
                            <button type="button" class="btn btn-warning btn-sm" id="batchEditBtn" disabled>Modifica in Batch</button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="d-flex">
                    <?php if ($can_edit): ?>
                    <div class="me-2" id="checkboxContainer" style="width: 30px;"></div>
                    <?php endif; ?>
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
                                        <th style="width: 35px; text-align: center;">Pannello</th>
                                    </tr>
                                </thead>
                                <tbody id="scheduleBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="ms-2" id="actionContainer" style="width: 40px;"></div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Gestione del logo
                        const newLogoUpload = document.getElementById('newLogoUpload');
                        if (newLogoUpload) {
                            newLogoUpload.addEventListener('change', function(e) {
                                const file = e.target.files[0];
                                if (file) {
                                    const formData = new FormData();
                                    formData.append('logo_image', file);
                                    formData.append('timetable_id', '<?php echo $timetable_id; ?>');

                                    fetch('api/update_logo.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            document.getElementById('logoPreview').src = data.logo_path;
                                            document.getElementById('logoPreview').classList.remove('d-none');
                                            document.getElementById('uploadText').classList.add('d-none');
                                            const logoModal = bootstrap.Modal.getInstance(document.getElementById('logoModal'));
                                            logoModal.hide();
                                        } else {
                                            alert(data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                    });
                                }
                            });
                        }

                        // Funzione per selezionare un logo esistente
                        window.selectLogo = function(logoPath) {
                            fetch('api/update_logo.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'timetable_id=<?php echo $timetable_id; ?>&logo=' + encodeURIComponent(logoPath)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    document.getElementById('logoPreview').src = logoPath;
                                    document.getElementById('logoPreview').classList.remove('d-none');
                                    document.getElementById('uploadText').classList.add('d-none');
                                    const logoModal = bootstrap.Modal.getInstance(document.getElementById('logoModal'));
                                    logoModal.hide();
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                        };

                        // Add event listener for double print button if needed
                        if(document.getElementById('doppiaPdfBtn')) {
                            document.getElementById('doppiaPdfBtn').addEventListener('click', function() {
                                // Open both PDF versions in new tabs
                                window.open('generate_pdf.php?id=<?php echo $timetable_id; ?>&show_pannello=0', '_blank');
                                setTimeout(function() {
                                    window.open('generate_pdf.php?id=<?php echo $timetable_id; ?>&show_pannello=1', '_blank');
                                }, 500); // Small delay to prevent popup blocking
                            });
                        }
                    });
                </script>
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
                                <label for="editTime" class="form-label small mb-1 required-field">Orario</label>
                                <input type="time" class="form-control form-control-sm" id="editTime" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small mb-1">Tipo di riga</label>
                                <div class="d-flex gap-2">
                                    <input type="radio" class="btn-check" name="editRowType" id="editNormalRowType" value="normal">
                                    <label class="btn btn-outline-primary btn-sm rounded-0" for="editNormalRowType">Normale</label>
                                    <input type="radio" class="btn-check" name="editRowType" id="editDescriptiveRowType" value="descriptive">
                                    <label class="btn btn-outline-primary btn-sm rounded-0" for="editDescriptiveRowType">Descrittiva</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit Descriptive Row Section -->
                        <div class="descriptive-fields hidden" id="editDescriptiveFields">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="editDescription" class="form-label small mb-1 required-field">Descrizione</label>
                                    <input type="text" class="form-control form-control-sm" id="editDescription">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit Normal Row Section -->
                        <div class="normal-fields" id="editNormalFields">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="editDiscipline" class="form-label small mb-1 required-field">Disciplina</label>
                                    <select class="form-select form-select-sm" id="editDiscipline">
                                            <?php foreach ($definizioni['disciplina'] as $def): ?>
                                                <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="editCategory" class="form-label small mb-1 required-field">Categoria</label>
                                    <select class="form-select form-select-sm" id="editCategory">
                                            <?php foreach ($definizioni['categoria'] as $def): ?>
                                                <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="editClass" class="form-label small mb-1 required-field">Classe</label>
                                    <select class="form-select form-select-sm" id="editClass">
                                            <?php foreach ($definizioni['classe'] as $def): ?>
                                                <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="editType" class="form-label small mb-1 required-field">Tipo</label>
                                    <select class="form-select form-select-sm" id="editType">
                                        <?php foreach ($definizioni['tipo'] as $def): ?>
                                            <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label for="edit_turno_numero" class="form-label small mb-1 required-field">N°</label>
                                    <input type="number" class="form-control form-control-sm" id="edit_turno_numero" min="1">
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_turno_definition" class="form-label small mb-1 required-field">Turno</label>
                                    <select class="form-select form-select-sm" id="edit_turno_definition">
                                            <?php foreach ($definizioni['turno'] as $def): ?>
                                                <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                            <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-2">
                                    <label for="editStartNumber" class="form-label small mb-1 required-field">Da</label>
                                    <input type="number" class="form-control form-control-sm" id="editStartNumber" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="editEndNumber" class="form-label small mb-1 required-field">A</label>
                                    <input type="number" class="form-control form-control-sm" id="editEndNumber" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="editDances" class="form-label small mb-1 required-field">Balli</label>
                                    <input type="number" class="form-control form-control-sm" id="editDances" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="editHeats" class="form-label small mb-1 required-field">Batterie</label>
                                    <input type="number" class="form-control form-control-sm" id="editHeats" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="editPannello" class="form-label small mb-1">Pannello</label>
                                    <input type="text" class="form-control form-control-sm" id="editPannello" maxlength="5">
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

    <!-- Pannello Batch Modal -->
    <div class="modal fade" id="pannelloBatchModal" tabindex="-1" aria-labelledby="pannelloBatchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pannelloBatchModalLabel">Assegna Pannello</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small><i class="bi bi-info-circle me-2"></i>Inserisci il pannello da assegnare a tutte le righe selezionate.</small>
                    </div>
                    <form id="pannelloBatchForm">
                        <div class="mb-3">
                            <label for="batchPannelloValue" class="form-label">Pannello</label>
                            <input type="text" class="form-control" id="batchPannelloValue" maxlength="5">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="saveBatchPannelloBtn">Assegna</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Batch Edit Modal -->
    <div class="modal fade" id="batchEditModal" tabindex="-1" aria-labelledby="batchEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="batchEditModalLabel">Modifica in Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small><i class="bi bi-info-circle me-2"></i>Seleziona i campi da modificare per tutte le righe selezionate.</small>
                    </div>
                    <form id="batchEditForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="editBatchDisciplineCheck">
                                    <label class="form-check-label" for="editBatchDisciplineCheck">Disciplina</label>
                                </div>
                                <select class="form-select form-select-sm" id="editBatchDiscipline" disabled>
                                    <?php foreach ($definizioni['disciplina'] as $def): ?>
                                        <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="editBatchCategoryCheck">
                                    <label class="form-check-label" for="editBatchCategoryCheck">Categoria</label>
                                </div>
                                <select class="form-select form-select-sm" id="editBatchCategory" disabled>
                                    <?php foreach ($definizioni['categoria'] as $def): ?>
                                        <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="editBatchClassCheck">
                                    <label class="form-check-label" for="editBatchClassCheck">Classe</label>
                                </div>
                                <select class="form-select form-select-sm" id="editBatchClass" disabled>
                                    <?php foreach ($definizioni['classe'] as $def): ?>
                                        <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="editBatchTypeCheck">
                                    <label class="form-check-label" for="editBatchTypeCheck">Tipo</label>
                                </div>
                                <select class="form-select form-select-sm" id="editBatchType" disabled>
                                    <?php foreach ($definizioni['tipo'] as $def): ?>
                                        <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="editBatchTurnoCheck">
                                    <label class="form-check-label" for="editBatchTurnoCheck">Turno</label>
                                </div>
                                <div class="d-flex">
                                    <input type="number" class="form-control form-control-sm me-2" id="editBatchTurnoNumero" placeholder="N°" min="1" disabled>
                                    <select class="form-select form-select-sm" id="editBatchTurnoDefinition" disabled>
                                        <?php foreach ($definizioni['turno'] as $def): ?>
                                            <option value="<?php echo htmlspecialchars($def['definition']); ?>"><?php echo htmlspecialchars($def['definition']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="editBatchBalliCheck">
                                    <label class="form-check-label" for="editBatchBalliCheck">Balli</label>
                                </div>
                                <input type="number" class="form-control form-control-sm" id="editBatchBalli" min="1" disabled>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="editBatchBatterieCheck">
                                    <label class="form-check-label" for="editBatchBatterieCheck">Batterie</label>
                                </div>
                                <input type="number" class="form-control form-control-sm" id="editBatchBatterie" min="1" disabled>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="editBatchPannelloCheck">
                                    <label class="form-check-label" for="editBatchPannelloCheck">Pannello</label>
                                </div>
                                <input type="text" class="form-control form-control-sm" id="editBatchPannello" maxlength="5" disabled>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="saveBatchEditBtn">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if Bootstrap is loaded
            console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
            
            // Rest of the code
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
                console.log('Loading timetable details for ID:', timetableId);
                fetch('api/save_timetable_detail.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        timetable_id: timetableId,
                        entry_type: 'load'
                    })
                })
                .then(response => {
                    console.log('API Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('API Response data:', data);
                    if (data.success) {
                        console.log('Number of rows received:', data.rows ? data.rows.length : 0);
                        updateTimetableDisplay(data.rows);
                    } else {
                        console.error('Error loading timetable details:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error in loadTimetableDetails:', error);
                });
            }

            // Update timetable display
            function updateTimetableDisplay(rows) {
                const scheduleBody = document.getElementById('scheduleBody');
                const dragHandleContainer = document.getElementById('dragHandleContainer');
                const actionContainer = document.getElementById('actionContainer');
                const checkboxContainer = document.getElementById('checkboxContainer');
                const canEdit = <?php echo $can_edit ? 'true' : 'false'; ?>;
                
                scheduleBody.innerHTML = '';
                dragHandleContainer.innerHTML = '';
                actionContainer.innerHTML = '';
                if (checkboxContainer) checkboxContainer.innerHTML = '';
                
                rows.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.draggable = canEdit;
                    tr.dataset.rowId = row.id;
                    tr.dataset.rowType = row.entry_type;
                    if (canEdit) {
                        tr.classList.add('draggable-row');
                    }
                    if (row.entry_type === 'descriptive') {
                        tr.classList.add('descriptive-row');
                        tr.innerHTML = `
                            <td>${row.time_slot}</td>
                            <td colspan="10">${row.description}</td>
                        `;
                        
                        if (canEdit) {
                            // Add checkbox for descriptive row
                            if (checkboxContainer) {
                                const checkboxDiv = document.createElement('div');
                                checkboxDiv.innerHTML = `<div></div>`; // Empty div for descriptive rows
                                checkboxContainer.appendChild(checkboxDiv);
                            }
                            
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
                            // Add empty div for spacing in read-only mode
                            document.getElementById('dragHandleContainer').appendChild(document.createElement('div'));
                            document.getElementById('actionContainer').appendChild(document.createElement('div'));
                        }
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
                            <td>${row.pannello || ''}</td>
                        `;
                        
                        if (canEdit) {
                            // Add checkbox for normal row
                            if (checkboxContainer) {
                                const checkboxDiv = document.createElement('div');
                                checkboxDiv.innerHTML = `<input type="checkbox" class="row-checkbox" data-row-id="${row.id}" onclick="toggleRowSelection(this, ${row.id})">`;
                                checkboxContainer.appendChild(checkboxDiv);
                            }
                            
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
                            // Add empty div for spacing in read-only mode
                            document.getElementById('dragHandleContainer').appendChild(document.createElement('div'));
                            document.getElementById('actionContainer').appendChild(document.createElement('div'));
                        }
                    }
                    scheduleBody.appendChild(tr);
                });
                
                // Update selection count
                updateSelectionCount();
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
                        alert('Errore durante la duplicazione: ' + data.message);
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
                            alert('Errore durante l\'eliminazione: ' + data.message);
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
                                const [editTurnoNum, editTurnoDef] = row.turn.split('° ');
document.getElementById('edit_turno_numero').value = editTurnoNum;
document.getElementById('edit_turno_definition').value = editTurnoDef;
                                document.getElementById('editStartNumber').value = row.da || '';
                                document.getElementById('editEndNumber').value = row.a || '';
                                document.getElementById('editDances').value = row.balli || '';
                                document.getElementById('editHeats').value = row.batterie || '';
                                document.getElementById('editPannello').value = row.pannello || '';
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
                    const editTurnoNum = document.getElementById('edit_turno_numero').value;
const editTurnoDef = document.getElementById('edit_turno_definition').value;
formData.turn = `${editTurnoNum}° ${editTurnoDef}`;
                    formData.da = document.getElementById('editStartNumber').value;
                    formData.a = document.getElementById('editEndNumber').value;
                    formData.balli = document.getElementById('editDances').value;
                    formData.batterie = document.getElementById('editHeats').value;
                    formData.pannello = document.getElementById('editPannello').value;
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
                        alert('Errore durante il salvataggio: ' + data.message);
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

            // Toggle row selection
            window.toggleRowSelection = function(checkbox, rowId) {
                const tr = document.querySelector(`tr[data-row-id="${rowId}"]`);
                if (checkbox.checked) {
                    tr.classList.add('selected-row');
                } else {
                    tr.classList.remove('selected-row');
                }
                updateSelectionCount();
                updateBatchButtons();
            };
            
            // Update selection count
            function updateSelectionCount() {
                const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
                const selectionInfo = document.querySelector('.selection-info');
                if (selectionInfo) {
                    selectionInfo.textContent = `${selectedCount} righe selezionate`;
                }
            }
            
            // Update batch buttons state
            function updateBatchButtons() {
                const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
                const batchPannelloBtn = document.getElementById('batchPannelloBtn');
                const batchEditBtn = document.getElementById('batchEditBtn');
                
                if (batchPannelloBtn) batchPannelloBtn.disabled = selectedCount === 0;
                if (batchEditBtn) batchEditBtn.disabled = selectedCount === 0;
            }
            
            // Select all rows
            if (document.getElementById('selectAllBtn')) {
                document.getElementById('selectAllBtn').addEventListener('click', function() {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = true;
                        const rowId = checkbox.dataset.rowId;
                        const tr = document.querySelector(`tr[data-row-id="${rowId}"]`);
                        if (tr) tr.classList.add('selected-row');
                    });
                    updateSelectionCount();
                    updateBatchButtons();
                });
            }
            
            // Deselect all rows
            if (document.getElementById('deselectAllBtn')) {
                document.getElementById('deselectAllBtn').addEventListener('click', function() {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = false;
                        const rowId = checkbox.dataset.rowId;
                        const tr = document.querySelector(`tr[data-row-id="${rowId}"]`);
                        if (tr) tr.classList.remove('selected-row');
                    });
                    updateSelectionCount();
                    updateBatchButtons();
                });
            }
            
            // Batch pannello button
            if (document.getElementById('batchPannelloBtn')) {
                document.getElementById('batchPannelloBtn').addEventListener('click', function() {
                    new bootstrap.Modal(document.getElementById('pannelloBatchModal')).show();
                });
            }
            
            // Batch edit button
            if (document.getElementById('batchEditBtn')) {
                document.getElementById('batchEditBtn').addEventListener('click', function() {
                    new bootstrap.Modal(document.getElementById('batchEditModal')).show();
                });
            }
            
            // Gestione attivazione/disattivazione campi nella modale di modifica batch
            const batchCheckboxes = [
                { checkbox: 'editBatchDisciplineCheck', field: 'editBatchDiscipline' },
                { checkbox: 'editBatchCategoryCheck', field: 'editBatchCategory' },
                { checkbox: 'editBatchClassCheck', field: 'editBatchClass' },
                { checkbox: 'editBatchTypeCheck', field: 'editBatchType' },
                { checkbox: 'editBatchTurnoCheck', fields: ['editBatchTurnoNumero', 'editBatchTurnoDefinition'] },
                { checkbox: 'editBatchBalliCheck', field: 'editBatchBalli' },
                { checkbox: 'editBatchBatterieCheck', field: 'editBatchBatterie' },
                { checkbox: 'editBatchPannelloCheck', field: 'editBatchPannello' }
            ];

            batchCheckboxes.forEach(item => {
                const checkbox = document.getElementById(item.checkbox);
                if (checkbox) {
                    checkbox.addEventListener('change', function() {
                        if (Array.isArray(item.fields)) {
                            item.fields.forEach(fieldId => {
                                const field = document.getElementById(fieldId);
                                if (field) field.disabled = !this.checked;
                            });
                        } else {
                            const field = document.getElementById(item.field);
                            if (field) field.disabled = !this.checked;
                        }
                    });
                }
            });

            // Save batch pannello
            if (document.getElementById('saveBatchPannelloBtn')) {
                document.getElementById('saveBatchPannelloBtn').addEventListener('click', function() {
                    const pannello = document.getElementById('batchPannelloValue').value;
                    const selectedRows = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(checkbox => parseInt(checkbox.dataset.rowId));
                    
                    if (selectedRows.length === 0) {
                        alert('Nessuna riga selezionata');
                        return;
                    }
                    
                    fetch('/api/batch_update_timetable_details.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            timetable_id: timetableId,
                            operation: 'update_pannello',
                            row_ids: selectedRows,
                            pannello: pannello
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('pannelloBatchModal')).hide();
                            updateTimetableDisplay(data.rows);
                            document.getElementById('batchPannelloValue').value = '';
                        } else {
                            alert('Errore durante l\'aggiornamento: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Errore durante l\'aggiornamento');
                    });
                });
            }

            // Save batch edit
            if (document.getElementById('saveBatchEditBtn')) {
                document.getElementById('saveBatchEditBtn').addEventListener('click', function() {
                    const selectedRows = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(checkbox => parseInt(checkbox.dataset.rowId));
                    
                    if (selectedRows.length === 0) {
                        alert('Nessuna riga selezionata');
                        return;
                    }
                    
                    // Collect fields data
                    const fields = {};
                    
                    if (document.getElementById('editBatchDisciplineCheck').checked) {
                        fields.discipline = document.getElementById('editBatchDiscipline').value;
                    }
                    if (document.getElementById('editBatchCategoryCheck').checked) {
                        fields.category = document.getElementById('editBatchCategory').value;
                    }
                    if (document.getElementById('editBatchClassCheck').checked) {
                        fields.class_name = document.getElementById('editBatchClass').value;
                    }
                    if (document.getElementById('editBatchTypeCheck').checked) {
                        fields.type = document.getElementById('editBatchType').value;
                    }
                    if (document.getElementById('editBatchTurnoCheck').checked) {
                        const turnoNum = document.getElementById('editBatchTurnoNumero').value;
                        const turnoDef = document.getElementById('editBatchTurnoDefinition').value;
                        fields.turn = `${turnoNum}° ${turnoDef}`;
                    }
                    if (document.getElementById('editBatchBalliCheck').checked) {
                        fields.balli = document.getElementById('editBatchBalli').value;
                    }
                    if (document.getElementById('editBatchBatterieCheck').checked) {
                        fields.batterie = document.getElementById('editBatchBatterie').value;
                    }
                    if (document.getElementById('editBatchPannelloCheck').checked) {
                        fields.pannello = document.getElementById('editBatchPannello').value;
                    }
                    
                    fetch('/api/batch_update_timetable_details.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            timetable_id: timetableId,
                            operation: 'batch_edit',
                            row_ids: selectedRows,
                            fields: fields
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('batchEditModal')).hide();
                            updateTimetableDisplay(data.rows);
                            // Reset form
                            document.getElementById('batchEditForm').reset();
                            // Disable all fields
                            batchCheckboxes.forEach(item => {
                                if (Array.isArray(item.fields)) {
                                    item.fields.forEach(fieldId => {
                                        const field = document.getElementById(fieldId);
                                        if (field) field.disabled = true;
                                    });
                                } else {
                                    const field = document.getElementById(item.field);
                                    if (field) field.disabled = true;
                                }
                            });
                        } else {
                            alert('Errore durante l\'aggiornamento: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Errore durante l\'aggiornamento');
                    });
                });
            }

            // Load initial data
            loadTimetableDetails();

            // Drag and drop functionality
            const scheduleBody = document.getElementById('scheduleBody');
            let draggedRow = null;
            const canEdit = <?php echo $can_edit ? 'true' : 'false'; ?>;

            if (canEdit) {
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
                            console.error('Reorder failed:', data.message);
                            loadTimetableDetails(); // Reload original order if failed
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loadTimetableDetails(); // Reload original order if failed
                    });
                });
            }

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
                    const class_name = document.getElementById('className').value;
                    const type = document.getElementById('type').value;
                    const turnoNum = document.getElementById('turno_numero').value;
const turnoDef = document.getElementById('turno_definition').value;
const turn = `${turnoNum}° ${turnoDef}`;
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
                        batterie,
                        pannello: document.getElementById('pannello').value
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
                        console.error('Save failed:', data.message);
                        alert('Errore durante il salvataggio: ' + data.message);
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
                document.getElementById('className').value = '';
                document.getElementById('type').value = 'Solo';
                document.getElementById('turno_numero').value = '';
                document.getElementById('turno_definition').selectedIndex = 0;
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
                                    console.error('Update failed:', data.message);
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        }, 500);
                    });
                }
            });

            // Handle collapse toggle icons
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
                // Add console logging to debug collapse functionality
                console.log('Setting up collapse listener for:', button.getAttribute('data-bs-target'));
                
                // Get the target element and icon element
                const target = button.getAttribute('data-bs-target');
                const iconElement = button.querySelector('i');
                const targetElement = document.querySelector(target);
                
                // Set initial icon state based on collapse state
                if (targetElement && !targetElement.classList.contains('show')) {
                    iconElement.classList.remove('bi-chevron-up');
                    iconElement.classList.add('bi-chevron-down');
                }
                
                // Listen for Bootstrap collapse events
                targetElement.addEventListener('hide.bs.collapse', function () {
                    console.log('Collapse hiding:', target);
                    iconElement.classList.remove('bi-chevron-up');
                    iconElement.classList.add('bi-chevron-down');
                });
                
                targetElement.addEventListener('show.bs.collapse', function () {
                    console.log('Collapse showing:', target);
                    iconElement.classList.remove('bi-chevron-down');
                    iconElement.classList.add('bi-chevron-up');
                });
            });
        });
    </script>
