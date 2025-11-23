<?php
/**
 * Script di connessione al database MySQL
 * Laboratorio OWASP A03: Injection
 */

// Configurazione della connessione al database
$db_host = 'db';           // Nome del servizio nel docker-compose
$db_name = 'a03_db';       // Nome del database
$db_user = 'root';         // Utente del database
$db_pass = 'root';         // Password del database

// Creazione della connessione PDO
try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass
    );

    // Imposta il modo di gestione degli errori
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // NOTA: Impostazione NON sicura per scopi didattici
    // In produzione, si dovrebbero sempre usare prepared statements!
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}

// Creazione anche di una connessione MySQLi per compatibilità
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die("Errore di connessione MySQLi: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");
?>
