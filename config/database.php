<?php
// Configurazione del database
$host = 'localhost';
$dbname = 'timetable-generator';
$username = 'root';
$password = 'root';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connessione fallita: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}

// Funzione per ottenere la connessione al database
function getDBConnection() {
    global $conn;
    return $conn;
}
?>