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
                        <a class="nav-link" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="profile-image">
                                <i class="bi bi-person"></i>
                            </div>
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
        <div class="row justify-content-center g-4">
            <div class="col-md-4">
                <a href="nuovo.php" class="text-decoration-none text-dark">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>