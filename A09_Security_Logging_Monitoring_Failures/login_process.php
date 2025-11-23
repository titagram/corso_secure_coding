<?php
session_start();
require_once 'db_connect.php';
require_once 'logger.php';

// VULNERABILITÀ CRITICA: Non logga tentativi di login falliti!
// Un attaccante può fare brute-force senza essere rilevato

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // VULNERABILITÀ: Include password nel log (informazioni sensibili!)
    $logger->log('DEBUG', "Login attempt - username: $username, password: $password");
    
    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // VULNERABILITÀ: Logga solo login riusciti, ignora falliti!
        $logger->logLogin($username, true);
        
        // VULNERABILITÀ: Include informazioni sensibili nel log
        $logger->log('INFO', "User logged in", [
            'user_id' => $user['id'],
            'role' => $user['role'],
            'session_id' => session_id()
        ]);
        
        header('Location: dashboard.php');
        exit;
    } else {
        // VULNERABILITÀ CRITICA: Non logga tentativi falliti!
        // Nessun audit trail per brute-force attacks!
        // Nessun alerting per tentativi sospetti!
        
        header('Location: login.php?error=Credenziali non valide');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}

