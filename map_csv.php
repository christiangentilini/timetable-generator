<?php
require_once 'config/database.php';
require_once 'config/session_check.php';

// Verifica che ci siano i dati CSV nella sessione
if (!isset($_SESSION['csv_data'])) {
    header('Location: index.php');
    exit;
}

// Verifica che ci sia l'ID del cronologico
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$timetable_id = intval($_GET['id']);

// Verifica che il cronologico esista e che l'utente abbia i permessi
$stmt = $conn->prepare("SELECT user_created FROM timetables WHERE id = ?");
$stmt->bind_param("i", $timetable_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$timetable = $result->fetch_assoc();

// Verifica i permessi
if ($timetable['user_created'] !== $_SESSION['user_id']) {
    $stmt = $conn->prepare("SELECT 1 FROM timetable_shares WHERE timetable_id = ? AND user_id = ? AND can_edit = 1");
    $stmt->bind_param("ii", $timetable_id, $_SESSION['user_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        header('Location: index.php');
        exit;
    }
}

$csv_data = $_SESSION['csv_data'];

// Pulisci i dati CSV rimuovendo le virgolette
$csv_data['headers'] = array_map(function($header) {
    return trim($header, '"');
}, $csv_data['headers']);

$csv_data['data'] = array_map(function($row) {
    return array_map(function($cell) {
        return trim($cell, '"');
    }, $row);
}, $csv_data['data']);

$headers = $csv_data['headers'];
$preview_data = array_slice($csv_data['data'], 0, 3); // Mostra solo le prime 3 righe per l'anteprima

// Campi del cronologico che possono essere mappati
$timetable_fields = [
    'discipline' => 'Disciplina',
    'category' => 'Categoria',
    'class_name' => 'Classe',
    'type' => 'Tipo',
    'da' => 'Da',
    'balli' => 'Balli (facoltativo)'
];

// Prepara i dati CSV per il form
$csv_data_json = json_encode($csv_data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mappa Colonne CSV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .mapping-container {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }
        .mapping-form {
            flex: 2;
        }
        .preview-section {
            flex: 1;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            max-width: 400px;
        }
        .preview-table {
            width: 100%;
            font-size: 0.8rem;
        }
        .preview-table th {
            background: #e9ecef;
            font-size: 0.8rem;
            padding: 0.3rem;
        }
        .preview-table td {
            padding: 0.3rem;
        }
        .required-field {
            color: #dc3545;
        }
        .form-label {
            font-weight: 500;
        }
        .form-select {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Mappa Colonne CSV</h1>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Seleziona le colonne del CSV che corrispondono ai campi del cronologico.
            I campi contrassegnati con <span class="required-field">*</span> sono obbligatori.
        </div>

        <div class="mapping-container">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Mappatura Colonne</h5>
                    </div>
                    <div class="card-body">
                        <form id="mappingForm">
                            <div class="mb-3">
                                <label class="form-label">Disciplina</label>
                                <select class="form-select" name="mapping[disciplina]" required>
                                    <option value="">Seleziona colonna</option>
                                    <?php foreach ($csv_data['headers'] as $index => $header): ?>
                                        <option value="<?php echo $index; ?>"><?php echo htmlspecialchars($header); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Categoria</label>
                                <select class="form-select" name="mapping[categoria]" required>
                                    <option value="">Seleziona colonna</option>
                                    <?php foreach ($csv_data['headers'] as $index => $header): ?>
                                        <option value="<?php echo $index; ?>"><?php echo htmlspecialchars($header); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Classe</label>
                                <select class="form-select" name="mapping[classe]" required>
                                    <option value="">Seleziona colonna</option>
                                    <?php foreach ($csv_data['headers'] as $index => $header): ?>
                                        <option value="<?php echo $index; ?>"><?php echo htmlspecialchars($header); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="mapping[tipo]" required>
                                    <option value="">Seleziona colonna</option>
                                    <?php foreach ($csv_data['headers'] as $index => $header): ?>
                                        <option value="<?php echo $index; ?>"><?php echo htmlspecialchars($header); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Da</label>
                                <select class="form-select" name="mapping[da]" required>
                                    <option value="">Seleziona colonna</option>
                                    <?php foreach ($csv_data['headers'] as $index => $header): ?>
                                        <option value="<?php echo $index; ?>"><?php echo htmlspecialchars($header); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Balli</label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="balli_mode" id="balli_numeric" value="numeric" checked>
                                        <label class="form-check-label" for="balli_numeric">Valore numerico</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="balli_mode" id="balli_text" value="text">
                                        <label class="form-check-label" for="balli_text">Testo da contare</label>
                                    </div>
                                </div>
                                <select class="form-select" name="mapping[balli]" required>
                                    <option value="">Seleziona colonna</option>
                                    <?php foreach ($csv_data['headers'] as $index => $header): ?>
                                        <option value="<?php echo $index; ?>"><?php echo htmlspecialchars($header); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">
                                    <span id="balli_numeric_help" class="balli-help">Seleziona la colonna che contiene il numero di balli</span>
                                    <span id="balli_text_help" class="balli-help" style="display: none;">Seleziona la colonna che contiene i nomi dei balli (es. "Waltz, Tango, Viennese Waltz")</span>
                                </small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ignoreEmptyDa" name="ignore_empty_da">
                                    <label class="form-check-label" for="ignoreEmptyDa">
                                        Ignora righe con campo 'Da' vuoto
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Mostra Anteprima Importazione</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="preview-section">
                <h5 class="mb-3">Anteprima CSV</h5>
                <div class="table-responsive">
                    <table class="table table-sm preview-table">
                        <thead>
                            <tr>
                                <?php foreach ($headers as $header): ?>
                                    <th><?php echo htmlspecialchars($header); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($preview_data as $row): ?>
                                <tr>
                                    <?php foreach ($row as $cell): ?>
                                        <td><?php echo htmlspecialchars($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Anteprima -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Anteprima Importazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Riepilogo:</strong>
                        <ul>
                            <li>Totale righe nel CSV: <span id="totalRows">0</span></li>
                            <li>Righe da importare: <span id="rowsToImport">0</span></li>
                            <li>Righe che verranno saltate: <span id="rowsToSkip">0</span></li>
                        </ul>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="previewTable">
                            <thead>
                                <tr>
                                    <th>Disciplina</th>
                                    <th>Categoria</th>
                                    <th>Classe</th>
                                    <th>Tipo</th>
                                    <th>Da</th>
                                    <th>Balli</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-primary" id="confirmImport">Procedi con l'Importazione</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const csvData = <?php echo json_encode($csv_data, JSON_PRETTY_PRINT); ?>;
        let previewData = [];

        function updatePreview() {
            const mapping = {};
            const form = document.getElementById('mappingForm');
            const formData = new FormData(form);
            
            // Raccogli le mappature
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('mapping[')) {
                    const field = key.match(/\[(.*?)\]/)[1];
                    mapping[field] = value;
                }
            }
            
            // Ottieni la modalità balli
            const balliMode = formData.get('balli_mode');
            const ignoreEmptyDa = formData.get('ignore_empty_da') === 'on';
            
            // Crea l'anteprima
            const previewData = csvData.data.slice(0, 5).map(row => {
                const mappedRow = {};
                for (const [field, colIndex] of Object.entries(mapping)) {
                    if (colIndex === '') continue;
                    
                    let value = row[colIndex];
                    
                    // Gestione speciale per il campo balli
                    if (field === 'balli' && balliMode === 'text') {
                        // Conta i balli nel testo
                        const balli = value.split(',').map(b => b.trim()).filter(b => b !== '');
                        value = balli.length.toString();
                    }
                    
                    mappedRow[field] = value;
                }
                return mappedRow;
            });

            // Aggiorna i contatori
            const totalRows = csvData.data.length;
            let rowsToImport = 0;
            let rowsToSkip = 0;

            csvData.data.forEach(row => {
                const da = row[mapping['da']] || '';
                if (ignoreEmptyDa && !da.trim()) {
                    rowsToSkip++;
                } else {
                    rowsToImport++;
                }
            });

            document.getElementById('totalRows').textContent = totalRows;
            document.getElementById('rowsToImport').textContent = rowsToImport;
            document.getElementById('rowsToSkip').textContent = rowsToSkip;
            
            // Aggiorna la tabella di anteprima
            const previewTable = document.getElementById('previewTable');
            const tbody = previewTable.querySelector('tbody');
            tbody.innerHTML = '';
            
            previewData.forEach(row => {
                const tr = document.createElement('tr');
                ['disciplina', 'categoria', 'classe', 'tipo', 'da', 'balli'].forEach(field => {
                    const td = document.createElement('td');
                    td.textContent = row[field] || '';
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }

        document.getElementById('mappingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const mapping = {};
            
            // Raccogli le mappature
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('mapping[')) {
                    const field = key.match(/\[(.*?)\]/)[1];
                    mapping[field] = value;
                }
            }
            
            // Ottieni la modalità balli
            const balliMode = formData.get('balli_mode');
            
            // Mostra il modal di anteprima
            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            previewModal.show();
            
            // Aggiorna l'anteprima
            updatePreview();
            
            // Gestisci il click sul pulsante di conferma
            document.getElementById('confirmImport').onclick = function() {
                // Invia i dati all'API
                fetch('api/import_csv.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        timetable_id: <?php echo $timetable_id; ?>,
                        csv_data: csvData,
                        column_mapping: mapping,
                        balli_mode: balliMode
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        window.location.href = 'crono-view.php?id=<?php echo $timetable_id; ?>';
                    } else {
                        alert(result.error || 'Errore durante l\'importazione');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Errore durante l\'importazione');
                });
            };
        });

        // Aggiungi gestione del cambio modalità balli
        document.querySelectorAll('input[name="balli_mode"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const isNumeric = this.value === 'numeric';
                document.getElementById('balli_numeric_help').style.display = isNumeric ? 'inline' : 'none';
                document.getElementById('balli_text_help').style.display = isNumeric ? 'none' : 'inline';
                updatePreview();
            });
        });
    </script>
</body>
</html> 