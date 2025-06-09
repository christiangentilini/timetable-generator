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
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Generator</title>
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
        .action-box {
            text-align: center;
            padding: 2rem;
            transition: transform 0.2s;
            cursor: pointer;
            height: 100%;
        }
        .action-box:hover {
            transform: translateY(-5px);
        }
        .action-box i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }
        .profile-box {
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
            transition: transform 0.2s;
            cursor: pointer;
        }
        .profile-box:hover {
            transform: translateY(-3px);
        }
        .profile-box i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #6c757d;
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
        <div class="row justify-content-center g-4">
            <div class="col-md-4">
                <a href="#" class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#newTimetableModal">
                    <div class="card action-box">
                        <i class="bi bi-plus-circle"></i>
                        <h4>Nuovo Cronologico</h4>
                        <p class="mb-0">Crea un nuovo cronologico</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="cronologici.php" class="text-decoration-none text-dark">
                    <div class="card action-box">
                        <i class="bi bi-clock-history"></i>
                        <h4>Cronologici</h4>
                        <p class="mb-0">Visualizza i tuoi cronologici</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="definizioni.php" class="text-decoration-none text-dark">
                    <div class="card action-box">
                        <i class="bi bi-gear"></i>
                        <h4>Definizioni</h4>
                        <p class="mb-0">Gestisci le definizioni</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-4">
                <a href="profilo.php" class="text-decoration-none text-dark">
                    <div class="card profile-box">
                        <i class="bi bi-person-circle"></i>
                        <h5 class="mb-0">Profilo</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="changelog.php" class="text-decoration-none text-dark">
                    <div class="card profile-box">
                        <i class="bi bi-megaphone"></i>
                        <h5 class="mb-0">Changelog</h5>
                    </div>
                </a>
            </div>
            <?php if ($user['type'] === 'admin'): ?>
            <div class="col-md-4">
                <a href="gestione-utenti.php" class="text-decoration-none text-dark">
                    <div class="card profile-box">
                        <i class="bi bi-people"></i>
                        <h5 class="mb-0">Gestione Utenti</h5>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Selection Modal -->
    <div class="modal fade" id="selectionModal" tabindex="-1" aria-labelledby="selectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectionModalLabel">Nuovo Cronologico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-primary btn-lg" id="manualEntryBtn">
                            <i class="bi bi-pencil-square me-2"></i>Inserimento Manuale
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" id="csvImportBtn">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Importa da CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Entry Modal -->
    <div class="modal fade" id="manualEntryModal" tabindex="-1" aria-labelledby="manualEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manualEntryModalLabel">Nuovo Cronologico - Inserimento Manuale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newTimetableForm">
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
                            <label for="logo" class="form-label">Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Crea Cronologico</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- CSV Import Modal -->
    <div class="modal fade" id="csvImportModal" tabindex="-1" aria-labelledby="csvImportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="csvImportModalLabel">Nuovo Cronologico - Importa da CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small><i class="bi bi-info-circle me-2"></i>Carica un file CSV. Nella schermata successiva potrai mappare le colonne del CSV ai campi del sistema.</small>
                    </div>
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">File CSV</label>
                        <input type="file" class="form-control" id="csvFile" accept=".csv">
                    </div>
                    <div id="csvPreview" class="d-none">
                        <h6 class="mb-3">Anteprima CSV (prime 5 righe)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead id="csvPreviewHeader"></thead>
                                <tbody id="csvPreviewBody"></tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="proceedToMapping">Procedi con la Mappatura</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer with Error Messages -->
    <div class="container fixed-bottom mb-4">
        <?php if (isset($_SESSION['error']) && $_SESSION['error']): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once 'includes/footer.php'; ?>

    <script>
        // Show selection modal when clicking "Nuovo Cronologico"
        document.querySelectorAll('[data-bs-target="#newTimetableModal"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const selectionModal = new bootstrap.Modal(document.getElementById('selectionModal'));
                selectionModal.show();
            });
        });

        // Handle manual entry button
        document.getElementById('manualEntryBtn').addEventListener('click', function() {
            const selectionModal = bootstrap.Modal.getInstance(document.getElementById('selectionModal'));
            selectionModal.hide();
            const manualEntryModal = new bootstrap.Modal(document.getElementById('manualEntryModal'));
            manualEntryModal.show();
        });

        // Handle CSV import button
        document.getElementById('csvImportBtn').addEventListener('click', function() {
            const selectionModal = bootstrap.Modal.getInstance(document.getElementById('selectionModal'));
            selectionModal.hide();
            const csvImportModal = new bootstrap.Modal(document.getElementById('csvImportModal'));
            csvImportModal.show();
        });

        // Handle CSV file upload
        document.getElementById('csvFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const text = e.target.result;
                    
                    // Funzione per parsare correttamente il CSV considerando le virgolette
                    function parseCSV(text) {
                        const lines = text.split('\n');
                        const headers = [];
                        const data = [];
                        
                        // Funzione per pulire un valore
                        function cleanValue(value) {
                            // Rimuovi le virgolette iniziali e finali se presenti
                            value = value.replace(/^["']|["']$/g, '');
                            // Rimuovi gli spazi iniziali e finali
                            value = value.trim();
                            return value;
                        }
                        
                        // Funzione per parsare una riga CSV
                        function parseCSVLine(line) {
                            const values = [];
                            let currentValue = '';
                            let inQuotes = false;
                            let escapeNext = false;
                            
                            for (let i = 0; i < line.length; i++) {
                                const char = line[i];
                                
                                if (escapeNext) {
                                    currentValue += char;
                                    escapeNext = false;
                                    continue;
                                }
                                
                                if (char === '\\') {
                                    escapeNext = true;
                                    continue;
                                }
                                
                                if (char === '"') {
                                    if (inQuotes && line[i + 1] === '"') {
                                        // Doppie virgolette all'interno di un campo tra virgolette
                                        currentValue += '"';
                                        i++; // Salta la prossima virgoletta
                                    } else {
                                        inQuotes = !inQuotes;
                                    }
                                } else if (char === ',' && !inQuotes) {
                                    values.push(cleanValue(currentValue));
                                    currentValue = '';
                                } else {
                                    currentValue += char;
                                }
                            }
                            
                            // Aggiungi l'ultimo valore
                            values.push(cleanValue(currentValue));
                            
                            return values;
                        }
                        
                        // Parsa l'header
                        if (lines.length > 0) {
                            headers.push(...parseCSVLine(lines[0]));
                        }
                        
                        // Parsa le righe di dati
                        for (let i = 1; i < lines.length; i++) {
                            const line = lines[i].trim();
                            if (!line) continue;
                            
                            const row = parseCSVLine(line);
                            
                            // Assicurati che la riga abbia lo stesso numero di colonne dell'header
                            while (row.length < headers.length) {
                                row.push('');
                            }
                            
                            // Tronca la riga se ha più colonne dell'header
                            if (row.length > headers.length) {
                                row.length = headers.length;
                            }
                            
                            data.push(row);
                        }
                        
                        return { headers, data };
                    }
                    
                    const { headers, data } = parseCSV(text);
                    
                    // Debug: stampa i dati parsati
                    console.log('Headers:', headers);
                    console.log('First row:', data[0]);
                    
                    // Salva i dati CSV nella sessione
                    fetch('store_csv_data.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            headers: headers,
                            data: data
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Crea un nuovo cronologico vuoto
                            const formData = new FormData();
                            formData.append('titolo', 'Nuovo Cronologico');
                            formData.append('sottotitolo', 'Importato da CSV');
                            formData.append('desc1', 'Importato da CSV');
                            formData.append('desc2', 'Importato da CSV');
                            formData.append('disclaimer', 'Importato da CSV');
                            formData.append('is_csv_import', '1');

                            fetch('save_timetable.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    // Reindirizza alla pagina di mapping
                                    window.location.href = 'map_csv.php?id=' + result.timetable_id;
                                } else {
                                    alert(result.error || 'Errore durante la creazione del cronologico');
                                }
                            })
                            .catch(error => {
                                console.error('Errore:', error);
                                alert('Errore durante la creazione del cronologico');
                            });
                        } else {
                            alert(result.error || 'Errore durante il salvataggio dei dati CSV');
                        }
                    })
                    .catch(error => {
                        console.error('Errore:', error);
                        alert('Errore durante il salvataggio dei dati CSV');
                    });
                };
                reader.readAsText(file);
            }
        });

        // Handle proceed to mapping button
        document.getElementById('proceedToMapping').addEventListener('click', function() {
            if (!window.csvData) {
                alert('Per favore carica un file CSV');
                return;
            }
            
            // Store CSV data in session
            fetch('store_csv_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(window.csvData)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Redirect to mapping page
                    window.location.href = `map_csv.php?timetable_id=${result.timetable_id}`;
                } else {
                    alert(result.error || 'Errore durante il salvataggio dei dati CSV');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore durante il salvataggio dei dati CSV');
            });
        });

        // Handle manual entry form submission
        document.getElementById('newTimetableForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('save_timetable.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = `crono-view.php?id=${result.timetable_id}`;
                } else {
                    alert(result.error || 'Errore durante il salvataggio del cronologico');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Errore durante il salvataggio del cronologico');
            }
        });
    </script>
</body>
</html>