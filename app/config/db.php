<?php
/**
 * Datenbank-Konfigurationsdatei für das WebTech-Projekt
 * 
 * Diese Datei stellt eine PDO-Verbindung zur MySQL/MariaDB-Datenbank her.
 * Die Verbindung läuft in einer Docker-Umgebung mit dem Container 'db'.
 */

// Datenbank-Konfigurationsparameter
$host = 'db';              // Docker-Container Hostname
$dbname = 'webtech';       // Datenbankname
$username = 'webtech';     // Datenbankbenutzer
$password = 'webtech_pw';  // Datenbankpasswort

try {
    // Erstelle PDO-Verbindung mit utf8mb4 Charset
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    
    // Aktiviere Exception-Modus für Fehlerbehandlung
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Setze Default Fetch Mode auf Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Bei Verbindungsfehler: Ausgabe einer verständlichen Fehlermeldung
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}

// Die Variable $pdo ist nun verfügbar für andere Dateien
?>
