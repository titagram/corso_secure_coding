<?php
/**
 * Script di processamento del login
 * Portale Dipendenti - Employee Portal
 *
 * VULNERABILITÀ PRESENTI:
 * 1. User Enumeration: messaggi di errore specifici
 * 2. Assenza di protezione Brute-Force
 */

session_start();
require_once 'db_connect.php';

// Log richiesta di login
error_log("Tentativo di accesso ricevuto");

// Verifica metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// Recupera credenziali dal form
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validazione input base
if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Tutti i campi sono obbligatori.";
    header("Location: login.php");
    exit();
}

// Log tentativo con username
error_log("Tentativo di login per utente: " . $username);

// VULNERABILITÀ 1: USER ENUMERATION
// Query per cercare l'utente nel database
$stmt = $pdo->prepare("SELECT * FROM employees WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

// Verifica se l'utente esiste
if (!$user) {
    // VULNERABILITÀ: Messaggio specifico che rivela che l'utente non esiste
    error_log("Utente non trovato: " . $username);
    $_SESSION['error'] = "Errore: Utente non trovato.";
    header("Location: login.php");
    exit();
}

// L'utente esiste, verifica la password
if (!password_verify($password, $user['password_hash'])) {
    // VULNERABILITÀ: Messaggio specifico che rivela che l'utente esiste ma la password è errata
    error_log("Password errata per utente: " . $username);
    $_SESSION['error'] = "Errore: Password errata.";
    header("Location: login.php");
    exit();
}

// VULNERABILITÀ 2: ASSENZA DI PROTEZIONE BRUTE-FORCE
// Non viene implementato:
// - Rate limiting (limitazione numero tentativi per IP/utente)
// - Account lockout (blocco account dopo X tentativi falliti)
// - CAPTCHA (verifica che non sia un bot)
// - Delay progressivo tra tentativi
// - Notifica all'utente di tentativi di accesso sospetti

// Login riuscito
error_log("Login riuscito per utente: " . $username);

// Imposta variabili di sessione
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['full_name'] = $user['full_name'];

// Log accesso completato
error_log("Sessione creata per: " . $user['full_name']);

// Reindirizza alla dashboard
header("Location: dashboard.php");
exit();
?>
