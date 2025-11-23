<?php
// Connessione al database MySQL
// OWASP A06: Vulnerable and Outdated Components - Sistema Blog Vulnerabile

// Supporto per Docker (variabili d'ambiente) e ambiente locale
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'blog_system';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// VULNERABILITÀ: Espone informazioni sulla versione MySQL
// In produzione, queste informazioni non dovrebbero essere esposte
$mysql_version = $conn->server_info;

