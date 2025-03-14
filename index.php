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
        </div>
    </div>

    <!-- New Timetable Modal -->
    <div class="modal fade" id="newTimetableModal" tabindex="-1" aria-labelledby="newTimetableModalLabel" aria-hidden="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newTimetableModalLabel">Nuovo Cronologico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="save_timetable.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Titolo</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Sottotitolo</label>
                            <input type="text" class="form-control" id="subtitle" name="subtitle" required>
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
                            <textarea class="form-control" id="disclaimer" name="disclaimer" rows="3" required></textarea>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>