<?php
/**
 * Script di processamento della registrazione
 * OWASP A02: Cryptographic Failures
 * 
 * VULNERABILITÀ CRITICHE:
 * 1. Password hashate con MD5 (algoritmo debole e deprecato)
 * 2. Dati personali memorizzati in CHIARO (PII, CF, indirizzi)
 * 3. Nessuna validazione crittografica
 */

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
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$postal_code = $_POST['postal_code'] ?? '';
$tax_code = $_POST['tax_code'] ?? '';
$date_of_birth = $_POST['date_of_birth'] ?? '';

// Validazione base
if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
    $_SESSION['error'] = "I campi obbligatori devono essere compilati.";
    header("Location: register.php");
    exit();
}

// Verifica se username esiste già
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "Username già esistente.";
    header("Location: register.php");
    exit();
}

// VULNERABILITÀ CRITICA: Password hashata con MD5 (algoritmo debole!)
// MD5 è facilmente rompibile con hashcat (mode 0)
// Non usa salt, quindi hash identici per password identiche
$password_hash = md5($password);

// VULNERABILITÀ: Dati personali memorizzati in CHIARO
// Nessuna cifratura per:
// - Email
// - Telefono
// - Indirizzo
// - Codice Fiscale (dato sensibile!)
// - Data di nascita

// Inserimento utente nel database
$stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, full_name, phone, address, city, postal_code, tax_code, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssss", $username, $password_hash, $email, $full_name, $phone, $address, $city, $postal_code, $tax_code, $date_of_birth);

if ($stmt->execute()) {
    $_SESSION['success'] = "Registrazione completata con successo! Ora puoi accedere.";
    header("Location: login.php");
} else {
    $_SESSION['error'] = "Errore durante la registrazione: " . $conn->error;
    header("Location: register.php");
}

$stmt->close();
$conn->close();
exit();
?>

