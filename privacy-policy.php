<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Timetable Generator</title>
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
        <h1 class="mb-4">Privacy Policy</h1>
        
        <div class="card">
            <div class="card-body">
                <h2>1. Informazioni sulla raccolta dei dati personali</h2>
                <p>La presente privacy policy fornisce informazioni sulla raccolta dei dati personali quando utilizzi il nostro servizio Timetable Generator.</p>
                
                <h2>2. Responsabile del trattamento</h2>
                <p>Il responsabile del trattamento dei dati è Christian Gentilini.</p>
                
                <h2>3. Dati raccolti</h2>
                <p>Raccogliamo i seguenti dati:</p>
                <ul>
                    <li>Nome utente</li>
                    <li>Indirizzo email</li>
                    <li>Dati di accesso</li>
                    <li>Contenuti generati dall'utente</li>
                </ul>
                
                <h2>4. Finalità del trattamento</h2>
                <p>I dati vengono trattati per:</p>
                <ul>
                    <li>Fornire il servizio di generazione cronologici</li>
                    <li>Gestire gli account utente</li>
                    <li>Migliorare il servizio</li>
                </ul>
                
                <h2>5. Base giuridica</h2>
                <p>Il trattamento dei dati si basa sul consenso dell'utente e sulla necessità di eseguire il contratto di servizio.</p>
                
                <h2>6. Periodo di conservazione</h2>
                <p>I dati vengono conservati per il tempo necessario a fornire il servizio e secondo gli obblighi di legge.</p>
                
                <h2>7. Diritti dell'utente</h2>
                <p>L'utente ha diritto a:</p>
                <ul>
                    <li>Accesso ai propri dati</li>
                    <li>Rettifica dei dati</li>
                    <li>Cancellazione dei dati</li>
                    <li>Limitazione del trattamento</li>
                    <li>Portabilità dei dati</li>
                </ul>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 