<?php
// Connessione al database MySQL

// Supporto per Docker (variabili d'ambiente) e ambiente locale
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'invoicing_app';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
