<?php
/**
 * Cancella una prenotazione
 * OWASP A04: Insecure Design
 * 
 * VULNERABILITÀ CRITICA: Permette di cancellare prenotazioni già completate
 * VULNERABILITÀ: Nessun controllo sullo stato della prenotazione
 * VULNERABILITÀ: Nessun controllo se il servizio è già stato erogato
 */

session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$booking_id = intval($_GET['id'] ?? 0);

// VULNERABILITÀ: Verifica solo che la prenotazione appartenga all'utente
// Non controlla lo stato (completed, confirmed, etc.)
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    $_SESSION['error'] = "Prenotazione non trovata o non autorizzata.";
    header("Location: dashboard.php");
    exit();
}

// VULNERABILITÀ CRITICA: Cancella senza controllare lo stato
// Dovrebbe impedire la cancellazione se:
// - status = 'completed' (servizio già erogato)
// - booking_date è nel passato
// - payment_status = 'paid' (già pagato, serve rimborso)
// - booking_date è troppo vicina (es. meno di 24h)

$stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Prenotazione cancellata con successo!";
} else {
    $_SESSION['error'] = "Errore durante la cancellazione: " . $conn->error;
}

header("Location: dashboard.php");
exit();
?>

