<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Policy - Timetable Generator</title>
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
        <h1 class="mb-4">Cookie Policy</h1>
        
        <div class="card">
            <div class="card-body">
                <h2>1. Cosa sono i cookie</h2>
                <p>I cookie sono piccoli file di testo che i siti visitati inviano al browser dell'utente, dove vengono memorizzati per essere ritrasmessi agli stessi siti alla visita successiva.</p>
                
                <h2>2. Tipologie di cookie utilizzati</h2>
                <h3>Cookie tecnici</h3>
                <p>Sono necessari per il corretto funzionamento del sito e per:</p>
                <ul>
                    <li>Mantenere attiva la sessione dell'utente</li>
                    <li>Ricordare le preferenze di visualizzazione</li>
                    <li>Gestire funzionalità essenziali del sito</li>
                </ul>
                
                <h3>Cookie analitici</h3>
                <p>Utilizziamo cookie analitici per:</p>
                <ul>
                    <li>Analizzare il traffico del sito</li>
                    <li>Comprendere come gli utenti utilizzano il servizio</li>
                    <li>Migliorare l'esperienza utente</li>
                </ul>
                
                <h2>3. Gestione dei cookie</h2>
                <p>L'utente può gestire le preferenze relative ai cookie attraverso le impostazioni del proprio browser:</p>
                <ul>
                    <li>Chrome</li>
                    <li>Firefox</li>
                    <li>Safari</li>
                    <li>Edge</li>
                </ul>
                
                <h2>4. Cookie di terze parti</h2>
                <p>Il sito utilizza alcuni servizi che potrebbero impostare cookie di terze parti, come:</p>
                <ul>
                    <li>Google Analytics per l'analisi del traffico</li>
                    <li>Bootstrap per il framework CSS</li>
                </ul>
                
                <h2>5. Durata dei cookie</h2>
                <p>I cookie hanno diverse durate:</p>
                <ul>
                    <li>Cookie di sessione: vengono eliminati alla chiusura del browser</li>
                    <li>Cookie persistenti: rimangono fino alla loro scadenza o cancellazione</li>
                </ul>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 