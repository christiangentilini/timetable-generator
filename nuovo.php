<?php
require_once 'config/database.php';
require_once 'config/session_check.php';
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
            background-color: #e9ecef;
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
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #e9ecef;
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
                                <img src="<?php echo htmlspecialchars($_SESSION['profile_path']); ?>" alt="Profile" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
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
                                <input type="text" class="form-control form-control-lg mb-2" id="competitionTitle" placeholder="Titolo della Competizione">
                                <input type="text" class="form-control mb-2" id="competitionSubtitle" placeholder="Sottotitolo">
                                <textarea class="form-control mb-2" id="competitionDescription1" rows="1" placeholder="Prima riga di descrizione"></textarea>
                                <textarea class="form-control mb-2" id="competitionDescription2" rows="1" placeholder="Seconda riga di descrizione"></textarea>
                                <textarea class="form-control mb-2" id="competitionDisclaimer" rows="2" placeholder="Disclaimer"></textarea>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="logo-upload-box" id="logoUploadBox">
                                    <i class="bi bi-cloud-upload"></i>
                                    <p class="mb-0" id="uploadText">Carica il logo qui</p>
                                    <img id="logoPreview" class="competition-logo mb-2 d-none" alt="Logo">
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
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="btn-group btn-group-toggle w-100" role="group" aria-label="Tipo riga">
                                        <input type="radio" class="btn-check" name="rowType" id="normalRow" value="normal" checked>
                                        <label class="btn btn-outline-primary" for="normalRow">Normale</label>
                                        <input type="radio" class="btn-check" name="rowType" id="descriptiveRow" value="descriptive">
                                        <label class="btn btn-outline-primary" for="descriptiveRow">Descrittiva</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="time" class="form-label small mb-1">Orario</label>
                                    <input type="time" class="form-control form-control-sm" id="time" required>
                                </div>
                                <div class="descriptive-fields hidden">
                                    <div class="col-md-10">
                                        <label for="description" class="form-label small mb-1">Descrizione</label>
                                        <input type="text" class="form-control form-control-sm" id="description">
                                    </div>
                                </div>
                                <div class="normal-fields row g-3">
                                    <div class="col-md-2">
                                        <label for="discipline" class="form-label small mb-1">Disciplina</label>
                                        <input type="text" class="form-control form-control-sm" id="discipline" required>
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
                                        <input type="number" class="form-control form-control-sm" id="startNumber" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="endNumber" class="form-label small mb-1">A</label>
                                        <input type="number" class="form-control form-control-sm" id="endNumber" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="dances" class="form-label small mb-1">Balli</label>
                                        <input type="number" class="form-control form-control-sm" id="dances" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="heats" class="form-label small mb-1">Batterie</label>
                                        <input type="number" class="form-control form-control-sm" id="heats" min="1" required>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-primary btn-sm">Aggiungi</button>
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
        document.getElementById('logoUploadBox').addEventListener('click', function() {
            document.getElementById('logoUpload').click();
        });
        document.getElementById('logoUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const logoPreview = document.getElementById('logoPreview');
                    const uploadText = document.getElementById('uploadText');
                    logoPreview.src = e.target.result;
                    logoPreview.classList.remove('d-none');
                    uploadText.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('competitionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isDescriptive = document.getElementById('descriptiveRow').checked;
            const time = document.getElementById('time').value;

            const row = document.createElement('tr');
            if (isDescriptive) {
                row.classList.add('descriptive-row');
                const description = document.getElementById('description').value;
                row.innerHTML = `
                    <td>${time}</td>
                    <td colspan="9">${description}</td>
                `;
            } else {
                const discipline = document.getElementById('discipline').value;
                const category = document.getElementById('category').value;
                const classValue = document.getElementById('class').value;
                const type = document.getElementById('type').value;
                const round = document.getElementById('round').value;
                const startNumber = document.getElementById('startNumber').value;
                const endNumber = document.getElementById('endNumber').value;
                const dances = document.getElementById('dances').value;
                const heats = document.getElementById('heats').value;

                row.innerHTML = `
                    <td>${time}</td>
                    <td>${discipline}</td>
                    <td>${category}</td>
                    <td>${classValue}</td>
                    <td>${type}</td>
                    <td>${round}</td>
                    <td>${startNumber}</td>
                    <td>${endNumber}</td>
                    <td>${dances}</td>
                    <td>${heats}</td>
                `;
            }

            document.getElementById('scheduleBody').appendChild(row);
            this.reset();
            document.getElementById('round').value = '1° Turno Finale';
            document.getElementById('normalRow').checked = true;
            updateFormFields();
        });

        function updateFormFields() {
            const isDescriptive = document.getElementById('descriptiveRow').checked;
            const normalFields = document.querySelector('.normal-fields');
            const descriptiveFields = document.querySelector('.descriptive-fields');

            if (isDescriptive) {
                normalFields.classList.add('hidden');
                descriptiveFields.classList.remove('hidden');
            } else {
                normalFields.classList.remove('hidden');
                descriptiveFields.classList.add('hidden');
            }
        }

        // Initialize form fields on page load
        document.querySelectorAll('input[name="rowType"]').forEach(radio => {
            radio.addEventListener('change', updateFormFields);
        });
        updateFormFields();
    </script>
</body>
</html>