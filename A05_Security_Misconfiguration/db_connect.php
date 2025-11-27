<?php
// Connessione al database MySQL
// OWASP A05: Security Misconfiguration - Sistema di Gestione Documentale Vulnerabile

// VULNERABILITÀ: Legge credenziali da file .env esposto pubblicamente
// In produzione, questo file dovrebbe essere fuori dalla document root!

$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Supporto per Docker (variabili d'ambiente) e ambiente locale
$host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
$db_name = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'document_system');
$username = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
$password = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? '');

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    // VULNERABILITÀ: Mostra errori dettagliati in produzione!
    die("Errore di connessione: " . $conn->connect_error . "<br>Host: " . $host . "<br>Database: " . $db_name);
}

$conn->set_charset("utf8");

