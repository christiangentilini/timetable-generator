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
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
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

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>