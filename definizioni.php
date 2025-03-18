<?php
require_once 'config/database.php';
require_once 'config/session_check.php';
require_once 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Definizioni - Timetable Generator</title>
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
        <h2 class="mb-4">Definizioni</h2>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Impostazioni Generali</h5>
                        <form>
                            <div class="mb-3">
                                <label for="defaultDuration" class="form-label">Durata Predefinita (minuti)</label>
                                <input type="number" class="form-control" id="defaultDuration" value="30">
                            </div>
                            <div class="mb-3">
                                <label for="timeFormat" class="form-label">Formato Orario</label>
                                <select class="form-select" id="timeFormat">
                                    <option value="24">24 ore</option>
                                    <option value="12">12 ore</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h5>Categorie</h5>
                        <div class="mb-3">
                            <button class="btn btn-outline-primary btn-sm">Aggiungi Categoria</button>
                        </div>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Categoria 1
                                <button class="btn btn-outline-danger btn-sm">Rimuovi</button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="floating-footer">
        <div class="container text-center">
            <span>© 2025 - Timetable Generator v1.0 by Christian Gentilini - All rights reserved</span>
            <span class="mx-2">|</span>
            <a href="privacy-policy.php" class="text-decoration-none">Privacy Policy</a>
            <span class="mx-2">|</span>
            <a href="cookie-policy.php" class="text-decoration-none">Cookie Policy</a>
            <span class="mx-2">|</span>
            <a href="terms.php" class="text-decoration-none">Termini e Condizioni</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>