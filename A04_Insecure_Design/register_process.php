<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$full_name = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
    $_SESSION['error'] = "I campi obbligatori devono essere compilati.";
    header("Location: register.php");
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "Username o email già esistenti.";
    header("Location: register.php");
    exit();
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, full_name, phone) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $password_hash, $email, $full_name, $phone);

if ($stmt->execute()) {
    $_SESSION['success'] = "Registrazione completata con successo! Ora puoi accedere.";
    header("Location: login.php");
} else {
    $_SESSION['error'] = "Errore durante la registrazione: " . $conn->error;
    header("Location: register.php");
}

exit();
?>

