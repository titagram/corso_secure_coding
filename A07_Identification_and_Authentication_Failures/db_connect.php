<?php
/**
 * Script di connessione al database
 * Portale Dipendenti - Employee Portal
 */

// Configurazione database (usa variabili d'ambiente se disponibili, altrimenti default)
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'employee_portal';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';

try {
    // Connessione PDO
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Log connessione avvenuta
    error_log("Connessione al database stabilita");

} catch (PDOException $e) {
    // Log errore connessione
    error_log("Errore di connessione al database: " . $e->getMessage());
    die("Errore di connessione al database. Contattare l'amministratore di sistema.");
}
?>
