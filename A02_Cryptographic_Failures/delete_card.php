<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$card_id = intval($_GET['id'] ?? 0);

// Verifica che la carta appartenga all'utente
$stmt = $conn->prepare("SELECT id FROM credit_cards WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $card_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Carta non trovata o non autorizzata.";
    header("Location: payment.php");
    exit();
}

// Elimina la carta
$stmt = $conn->prepare("DELETE FROM credit_cards WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $card_id, $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Carta di credito eliminata con successo.";
} else {
    $_SESSION['error'] = "Errore durante l'eliminazione.";
}

header("Location: payment.php");
exit();
?>

