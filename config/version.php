<?php
function getLatestVersion($conn) {
    $query = "SELECT version FROM changelog ORDER BY date DESC LIMIT 1";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['version'];
    }
    return '1.0'; // Versione di default se non ci sono changelog
} 