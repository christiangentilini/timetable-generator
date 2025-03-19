<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termini e Condizioni - Timetable Generator</title>
    <link rel="icon" type="image/png" href="assets/favicon/favicon.png">
    <link rel="apple-touch-icon" href="assets/favicon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            padding-top: 80px;
            padding-bottom: 100px;
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Timetable Generator <span class="version-text">v1.0</span></a>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4">Termini e Condizioni</h1>
        
        <div class="card">
            <div class="card-body">
                <h2>1. Accettazione dei termini</h2>
                <p>Utilizzando il servizio Timetable Generator, l'utente accetta i seguenti termini e condizioni di utilizzo.</p>
                
                <h2>2. Descrizione del servizio</h2>
                <p>Timetable Generator è un servizio web che permette di:</p>
                <ul>
                    <li>Creare cronologici per eventi</li>
                    <li>Gestire programmi e orari</li>
                    <li>Generare PDF dei cronologici</li>
                </ul>
                
                <h2>3. Registrazione e account</h2>
                <p>Per utilizzare il servizio è necessario:</p>
                <ul>
                    <li>Registrare un account</li>
                    <li>Fornire informazioni accurate</li>
                    <li>Mantenere sicure le credenziali di accesso</li>
                </ul>
                
                <h2>4. Proprietà intellettuale</h2>
                <p>Tutti i diritti sono riservati. L'utente non può:</p>
                <ul>
                    <li>Copiare o modificare il software</li>
                    <li>Utilizzare il servizio per scopi illegali</li>
                    <li>Distribuire contenuti protetti da copyright</li>
                </ul>
                
                <h2>5. Limitazioni di responsabilità</h2>
                <p>Il servizio viene fornito "così com'è". Non garantiamo:</p>
                <ul>
                    <li>Disponibilità continua del servizio</li>
                    <li>Assenza di errori o bug</li>
                    <li>Idoneità per scopi specifici</li>
                </ul>
                
                <h2>6. Modifiche ai termini</h2>
                <p>Ci riserviamo il diritto di modificare questi termini in qualsiasi momento. Le modifiche saranno effettive dopo la pubblicazione sul sito.</p>
                
                <h2>7. Legge applicabile</h2>
                <p>Questi termini sono regolati dalla legge italiana. Qualsiasi controversia sarà soggetta alla giurisdizione esclusiva del tribunale di competenza.</p>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 