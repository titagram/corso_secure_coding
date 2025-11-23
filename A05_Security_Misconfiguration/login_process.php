<?php
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

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // VULNERABILITÀ: Mostra errori dettagliati!
    $_SESSION['error'] = "Utente non trovato: " . htmlspecialchars($username);
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password_hash'])) {
    // VULNERABILITÀ: Mostra errori dettagliati!
    $_SESSION['error'] = "Password errata per l'utente: " . htmlspecialchars($username);
    header("Location: login.php");
    exit();
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];

header("Location: dashboard.php");
exit();
?>

