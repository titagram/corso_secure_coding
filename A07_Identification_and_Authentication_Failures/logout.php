<?php
/**
 * Script di Logout
 * Portale Dipendenti - Employee Portal
 */

session_start();

// Log logout
if (isset($_SESSION['username'])) {
    error_log("Logout utente: " . $_SESSION['username']);
}

// Distruggi tutte le variabili di sessione
$_SESSION = array();

// Distruggi il cookie di sessione se esiste
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Distruggi la sessione
session_destroy();

// Log logout completato
error_log("Sessione terminata correttamente");

// Reindirizza alla pagina di login
header("Location: login.php");
exit();
?>
