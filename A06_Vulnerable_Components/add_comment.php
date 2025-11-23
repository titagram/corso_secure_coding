<?php
/**
 * Aggiunge un commento a un post
 * OWASP A06: Vulnerable and Outdated Components
 * 
 * VULNERABILITÀ: Input non sanitizzato completamente
 * jQuery 1.7.2 può eseguire XSS se il contenuto viene processato con .html()
 */

session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$post_id = intval($_POST['post_id'] ?? 0);
$author_name = $_POST['author_name'] ?? '';
$author_email = $_POST['author_email'] ?? '';
$content = $_POST['content'] ?? '';

if (empty($author_name) || empty($content)) {
    $_SESSION['error'] = "Nome e commento sono obbligatori.";
    header("Location: post.php?id=" . $post_id);
    exit();
}

// VULNERABILITÀ CRITICA: I commenti vengono salvati SENZA sanitizzazione!
// Questo permette a jQuery 1.7.2 di processarli con .html() ed eseguire XSS
// In produzione, il contenuto dovrebbe essere sanitizzato con htmlspecialchars()

// VULNERABILITÀ: Non sanitizziamo il contenuto per permettere XSS con jQuery
// jQuery 1.7.2 processerà questo contenuto con .html() nella pagina post.php
// Se il contenuto contiene <img src=x onerror="alert('XSS')">, verrà eseguito!

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

// VULNERABILITÀ: Inserisce il contenuto direttamente senza sanitizzazione
// Questo è intenzionale per dimostrare la vulnerabilità jQuery XSS
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, author_name, author_email, content) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $post_id, $user_id, $author_name, $author_email, $content);

if ($stmt->execute()) {
    $_SESSION['success'] = "Commento aggiunto con successo!";
} else {
    $_SESSION['error'] = "Errore durante l'aggiunta del commento: " . $conn->error;
}

header("Location: post.php?id=" . $post_id);
exit();
?>

