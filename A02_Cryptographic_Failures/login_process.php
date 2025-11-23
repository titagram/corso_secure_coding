<?php
/**
 * Script di processamento del login
 * OWASP A02: Cryptographic Failures
 * 
 * VULNERABILITÀ: Password verificate con hash deboli (MD5, SHA1, SHA256)
 * senza controllo dell'algoritmo utilizzato
 */

session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Tutti i campi sono obbligatori.";
    header("Location: login.php");
    exit();
}

// VULNERABILITÀ: Query per recuperare l'utente
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Credenziali non valide.";
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();
$stored_hash = $user['password_hash'];

// VULNERABILITÀ CRITICA: Verifica password con algoritmi DEBOLI
// Il sistema prova diversi algoritmi senza distinguere tra hash sicuri e insicuri
$login_success = false;

// Prova MD5 (hashcat mode 0) - VULNERABILE!
if (strlen($stored_hash) === 32 && ctype_xdigit($stored_hash)) {
    // Probabilmente MD5
    if (md5($password) === $stored_hash) {
        $login_success = true;
    }
}

// Prova MD5 con salt statico (ancora vulnerabile!)
// Salt hardcoded: "ecommerce2024"
if (!$login_success && strlen($stored_hash) === 32 && ctype_xdigit($stored_hash)) {
    if (md5($password . "ecommerce2024") === $stored_hash) {
        $login_success = true;
    }
}

// Prova SHA1 (hashcat mode 100) - VULNERABILE!
if (!$login_success && strlen($stored_hash) === 40 && ctype_xdigit($stored_hash)) {
    if (sha1($password) === $stored_hash) {
        $login_success = true;
    }
}

// Prova SHA256 (hashcat mode 1400) - VULNERABILE!
if (!$login_success && strlen($stored_hash) === 64 && ctype_xdigit($stored_hash)) {
    if (hash('sha256', $password) === $stored_hash) {
        $login_success = true;
    }
}

// Prova bcrypt (hashcat mode 3200) - Algoritmo sicuro ma password deboli sono comunque vulnerabili
if (!$login_success && strpos($stored_hash, '$2y$') === 0) {
    if (password_verify($password, $stored_hash)) {
        $login_success = true;
    }
}

if (!$login_success) {
    $_SESSION['error'] = "Credenziali non valide.";
    header("Location: login.php");
    exit();
}

// Login riuscito - VULNERABILITÀ: Session token prevedibile
// Token basato su username + timestamp (facilmente prevedibile)
$session_token = $username . '_' . date('YmdHis');
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['session_token'] = $session_token;

// Salva sessione nel database (token prevedibile!)
$expires_at = date('Y-m-d H:i:s', strtotime('+8 hours'));
$stmt = $conn->prepare("INSERT INTO sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user['id'], $session_token, $expires_at);
$stmt->execute();

header("Location: dashboard.php");
exit();
?>

