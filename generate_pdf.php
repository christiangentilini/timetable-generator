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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .pdf-container {
            max-width: 210mm;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            margin-bottom: 20px;
        }
        .logo-container {
            width: 30%;
            padding-right: 20px;
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
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
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
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .descriptive-row {
            background-color: #f8f9fa;
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
    <div class="container no-print mb-3">
        <div class="row">
            <div class="col-12">
                <h1>Anteprima PDF</h1>
                <p>Questa è un'anteprima del PDF che verrà generato. Clicca su "Genera PDF" per scaricare il file.</p>
                <button id="generatePdfBtn" class="btn btn-primary">Genera PDF</button>
                <a href="crono-view.php?id=<?php echo $timetable_id; ?>" class="btn btn-secondary">Torna al Cronologico</a>
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
                    <th>Batterie</th>
                    <th>Pannello</th>
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

    <script>
        document.getElementById('generatePdfBtn').addEventListener('click', function() {
            // Initialize jsPDF
            const { jsPDF } = window.jspdf;
            
            // Create a new PDF document
            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: 'a4'
            });
            
            // Use html2canvas to capture the content
            html2canvas(document.getElementById('pdfContent'), {
                scale: 2, // Higher scale for better quality
                useCORS: true, // To handle images from different domains
                logging: false
            }).then(canvas => {
                // Add the captured content to the PDF
                const imgData = canvas.toDataURL('image/jpeg', 1.0);
                const pdfWidth = doc.internal.pageSize.getWidth();
                const pdfHeight = doc.internal.pageSize.getHeight();
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;
                const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
                const imgX = (pdfWidth - imgWidth * ratio) / 2;
                const imgY = 0;
                
                doc.addImage(imgData, 'JPEG', imgX, imgY, imgWidth * ratio, imgHeight * ratio);
                
                // Save the PDF
                doc.save('<?php echo preg_replace("/[^a-zA-Z0-9]/", "_", $timetable['titolo']); ?>_timetable.pdf');
            });
        });
    </script>
</body>
</html>