<?php
/**
 * Logout-Seite für den Sofa-Konfigurator
 * 
 * Beendet die Session des Benutzers und leitet zur Startseite weiter.
 */

// Starte die Session, falls sie noch nicht gestartet ist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lösche alle Session-Daten
$_SESSION = [];

// Zerstöre die Session vollständig
session_destroy();

// Leitet den Benutzer zur Startseite weiter
header('Location: index.php');
exit;
?>
