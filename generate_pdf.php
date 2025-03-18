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

// Fetch timetable details
$details = [];
if ($timetable_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM timetable_details WHERE timetable_id = ? ORDER BY order_number ASC");
    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $details[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genera PDF - <?php echo htmlspecialchars($timetable['titolo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
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
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
            overflow: hidden;
        }
        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .pdf-container {
            width: 210mm; /* A4 width in portrait mode */
            height: 297mm; /* A4 height in portrait mode */
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }
        .header {
            display: flex;
            margin-bottom: 20px;
        }
        .logo-container {
            width: 120px;
            height: 120px;
            padding-right: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .competition-logo {
            max-width: 100%;
            max-height: 120px;
            object-fit: contain;
        }
        .competition-info {
            width: 70%;
        }
        .competition-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .competition-subtitle {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .competition-description {
            font-size: 14px;
            margin-bottom: 3px;
        }
        .disclaimer {
            font-size: 12px;
            font-style: italic;
            margin: 15px 0 5px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            background-color: #ffffff;
        }
        th {
            font-weight: bold;
            color: #000000;
            background-color: #ffffff;
        }
        .descriptive-row {
            font-weight: bold;
        }
        .hidden-on-pdf {
            display: none;
        }
        @media print {
            body {
                padding: 0;
            }
            .pdf-container {
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top no-print">
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
                            <li><a class="dropdown-item" href="crono-view.php">Nuovo Cronologico</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="definizioni.php">Definizioni</a></li>
                            <li><a class="dropdown-item" href="profilo.php">Profilo</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container no-print mb-3">
        <div class="row">
            <div class="col-12">
                <h1>Anteprima PDF</h1>
                <p>Questa è un'anteprima del PDF che verrà generato. Clicca su "Genera PDF" per scaricare il file.</p>
                <button id="generatePdfBtn" class="btn btn-primary">Genera PDF</button>
                <a onclick="window.close();" class="btn btn-secondary">Torna al Cronologico</a>
            </div>
        </div>
    </div>

    <div id="pdfContent" class="pdf-container">
        <div class="header">
            <div class="logo-container">
                <?php if (!empty($timetable['logo'])): ?>
                    <img src="<?php echo htmlspecialchars($timetable['logo']); ?>" class="competition-logo" alt="Logo">
                <?php endif; ?>
            </div>
            <div class="competition-info">
                <div class="competition-title"><?php echo htmlspecialchars($timetable['titolo']); ?></div>
                <div class="competition-subtitle"><?php echo htmlspecialchars($timetable['sottotitolo']); ?></div>
                <div class="competition-description"><?php echo htmlspecialchars($timetable['desc1']); ?></div>
                <div class="competition-description"><?php echo htmlspecialchars($timetable['desc2']); ?></div>
            </div>
        </div>

        <?php if (!empty($timetable['disclaimer'])): ?>
        <div class="disclaimer">
            <?php echo nl2br(htmlspecialchars($timetable['disclaimer'])); ?>
        </div>
        <?php endif; ?>

        <table>
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
            <tbody>
                <?php foreach ($details as $row): ?>
                    <?php if ($row['entry_type'] === 'descriptive'): ?>
                        <tr class="descriptive-row">
                            <td><?php echo htmlspecialchars($row['time_slot']); ?></td>
                            <td colspan="10"><?php echo htmlspecialchars($row['description']); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['time_slot']); ?></td>
                            <td><?php echo htmlspecialchars($row['discipline'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['category'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['class_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['type'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['turn'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['da'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['a'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['balli'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['batterie'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['pannello'] ?? ''); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('generatePdfBtn').addEventListener('click', function() {
            // Initialize jsPDF
            const { jsPDF } = window.jspdf;
            
            // Create a new PDF document with A4 format
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });
            
            // Imposta i margini
            const margin = 10;
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const contentWidth = pageWidth - (margin * 2);
            
            // Cattura l'header
            html2canvas(document.querySelector('.header'), {
                scale: 2,
                useCORS: true,
                logging: false
            }).then(headerCanvas => {
                const headerImgData = headerCanvas.toDataURL('image/jpeg', 1.0);
                const headerImgWidth = headerCanvas.width;
                const headerImgHeight = headerCanvas.height;
                const headerRatio = contentWidth / headerImgWidth;
                const headerImgHeightMM = headerImgHeight * headerRatio;
                
                // Aggiungi l'header alla prima pagina
                doc.addImage(headerImgData, 'JPEG', margin, margin, contentWidth, headerImgHeightMM);
                
                let currentY = margin + headerImgHeightMM + 10;
                
                // Se c'è un disclaimer, aggiungilo
                const disclaimer = document.querySelector('.disclaimer');
                if (disclaimer) {
                    html2canvas(disclaimer, {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        width: document.querySelector('table').offsetWidth // Imposta la larghezza uguale alla tabella
                    }).then(disclaimerCanvas => {
                        const disclaimerImgData = disclaimerCanvas.toDataURL('image/jpeg', 1.0);
                        const disclaimerImgWidth = disclaimerCanvas.width;
                        const disclaimerImgHeight = disclaimerCanvas.height;
                        const disclaimerRatio = contentWidth / disclaimerImgWidth;
                        const disclaimerImgHeightMM = disclaimerImgHeight * disclaimerRatio;
                        
                        doc.addImage(disclaimerImgData, 'JPEG', margin, currentY, contentWidth, disclaimerImgHeightMM);
                        currentY += disclaimerImgHeightMM + 5;
                        
                        // Aggiungi la tabella
                        addTable(currentY);
                    });
                } else {
                    // Se non c'è disclaimer, aggiungi direttamente la tabella
                    addTable(currentY);
                }
            });
            
            // Funzione per aggiungere la tabella
            function addTable(startY) {
                // Crea una tabella temporanea pulita
                const tempTable = document.createElement('table');
                tempTable.style.width = '100%';
                tempTable.style.borderCollapse = 'collapse';
                tempTable.style.fontSize = '12px';
                
                // Copia l'header
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');
                const originalHeader = document.querySelector('table thead tr');
                originalHeader.querySelectorAll('th').forEach(th => {
                    const newTh = document.createElement('th');
                    newTh.textContent = th.textContent;
                    newTh.style.border = '1px solid #ddd';
                    newTh.style.padding = '8px';
                    newTh.style.textAlign = 'center';
                    newTh.style.backgroundColor = '#ffffff';
                    headerRow.appendChild(newTh);
                });
                thead.appendChild(headerRow);
                tempTable.appendChild(thead);
                
                // Copia il body
                const tbody = document.createElement('tbody');
                document.querySelectorAll('table tbody tr').forEach(tr => {
                    const newTr = document.createElement('tr');
                    if (tr.classList.contains('descriptive-row')) {
                        newTr.classList.add('descriptive-row');
                    }
                    tr.querySelectorAll('td').forEach(td => {
                        const newTd = document.createElement('td');
                        newTd.textContent = td.textContent;
                        newTd.style.border = '1px solid #ddd';
                        newTd.style.padding = '8px';
                        newTd.style.textAlign = 'center';
                        newTd.style.backgroundColor = '#ffffff';
                        newTr.appendChild(newTd);
                    });
                    tbody.appendChild(newTr);
                });
                tempTable.appendChild(tbody);
                
                // Nascondi la tabella temporanea ma mantienila nel DOM
                tempTable.style.position = 'absolute';
                tempTable.style.left = '-9999px';
                document.body.appendChild(tempTable);
                
                const table = tempTable;
                const header = table.querySelector('thead tr');
                const rows = table.querySelectorAll('tbody tr');
                
                // Imposta le larghezze delle colonne
                const columnWidths = [
                    contentWidth * 0.08, // Orario
                    contentWidth * 0.15, // Disciplina
                    contentWidth * 0.15, // Categoria
                    contentWidth * 0.10, // Classe
                    contentWidth * 0.10, // Tipo
                    contentWidth * 0.15, // Turno
                    contentWidth * 0.05, // Da
                    contentWidth * 0.05, // A
                    contentWidth * 0.05, // Balli
                    contentWidth * 0.05, // Batterie
                    contentWidth * 0.05  // Pannello
                ];
                
                let currentY = startY;
                const rowHeight = 7;
                
                // Aggiungi l'header della tabella
                doc.setFillColor(0, 0, 0); // Nero
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(9);
                doc.setTextColor(255, 255, 255); // Bianco
                
                // Disegna prima tutti i rettangoli neri dell'header
                let x = margin;
                header.querySelectorAll('th').forEach((th, i) => {
                    doc.rect(x, currentY, columnWidths[i], rowHeight, 'F');
                    x += columnWidths[i];
                });
                
                // Poi aggiungi il testo bianco
                x = margin;
                header.querySelectorAll('th').forEach((th, i) => {
                    const text = th.textContent.trim();
                    const textWidth = doc.getTextWidth(text);
                    const textX = x + (columnWidths[i] - textWidth) / 2;
                    doc.text(text, textX, currentY + 4);
                    x += columnWidths[i];
                });
                
                currentY += rowHeight;
                
                // Aggiungi le righe
                doc.setFont('helvetica', 'normal');
                doc.setTextColor(0, 0, 0); // Torna al nero per il contenuto
                rows.forEach(row => {
                    // Controlla se c'è spazio per una nuova riga
                    if (currentY + rowHeight > pageHeight - margin) {
                        doc.addPage();
                        currentY = margin;
                        
                        // Ripeti l'header su ogni nuova pagina
                        doc.setFillColor(0, 0, 0); // Nero
                        doc.setFont('helvetica', 'bold');
                        doc.setFontSize(9);
                        doc.setTextColor(255, 255, 255); // Bianco
                        
                        let x = margin;
                        header.querySelectorAll('th').forEach((th, i) => {
                            doc.rect(x, currentY, columnWidths[i], rowHeight, 'F');
                            x += columnWidths[i];
                        });
                        
                        // Poi aggiungi il testo bianco
                        x = margin;
                        header.querySelectorAll('th').forEach((th, i) => {
                            const text = th.textContent.trim();
                            const textWidth = doc.getTextWidth(text);
                            const textX = x + (columnWidths[i] - textWidth) / 2;
                            doc.text(text, textX, currentY + 4);
                            x += columnWidths[i];
                        });
                        
                        currentY += rowHeight;
                        doc.setTextColor(0, 0, 0); // Torna al nero per il contenuto
                    }
                    
                    // Disegna le celle
                    let x = margin;
                    row.querySelectorAll('td').forEach((td, i) => {
                        const text = td.textContent.trim();
                        // Gestione speciale per le righe descrittive
                        if (row.classList.contains('descriptive-row')) {
                            if (i === 0) {
                                // Prima cella (orario)
                                doc.rect(x, currentY, columnWidths[i], rowHeight);
                                doc.setFont('helvetica', 'bold');
                                const textWidth = doc.getTextWidth(text);
                                const textX = x + (columnWidths[i] - textWidth) / 2;
                                doc.text(text, textX, currentY + 4);
                                x += columnWidths[i];
                            } else if (i === 1) {
                                // Seconda cella (descrizione che occupa le colonne rimanenti)
                                const remainingWidth = columnWidths.slice(1).reduce((a, b) => a + b, 0);
                                doc.rect(x, currentY, remainingWidth, rowHeight);
                                doc.setFont('helvetica', 'bold');
                                const textWidth = doc.getTextWidth(text);
                                const textX = x + (remainingWidth - textWidth) / 2;
                                doc.text(text, textX, currentY + 4);
                            }
                        } else {
                            doc.rect(x, currentY, columnWidths[i], rowHeight);
                            doc.setFont('helvetica', 'normal');
                            const textWidth = doc.getTextWidth(text);
                            const textX = x + (columnWidths[i] - textWidth) / 2;
                            doc.text(text, textX, currentY + 4);
                            x += columnWidths[i];
                        }
                    });
                    
                    currentY += rowHeight;
                });
                
                // Rimuovi la tabella temporanea
                document.body.removeChild(tempTable);
                
                // Salva il PDF
                doc.save('<?php echo preg_replace("/[^a-zA-Z0-9]/", "_", $timetable['titolo']); ?>_timetable.pdf');
            }
        });
    </script>
</body>
</html>