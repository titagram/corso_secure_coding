<?php
/**
 * API per recuperare commenti (VULNERABILE)
 * OWASP A06: Vulnerable and Outdated Components
 * 
 * VULNERABILITÀ CRITICA: Restituisce commenti in formato JSON
 * che vengono poi processati con jQuery .html() senza sanitizzazione
 */

header('Content-Type: application/json');
require_once 'db_connect.php';

$post_id = intval($_GET['post_id'] ?? 0);

if ($post_id === 0) {
    echo json_encode(['error' => 'Post ID richiesto']);
    exit;
}

// Recupera commenti
$stmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments = $stmt->get_result();

$comments_array = [];
while ($comment = $comments->fetch_assoc()) {
    // VULNERABILITÀ: Restituisce il contenuto SENZA sanitizzazione!
    // Questo permette a jQuery di processarlo con .html() ed eseguire XSS
    $comments_array[] = [
        'id' => $comment['id'],
        'author_name' => $comment['author_name'],
        'author_email' => $comment['author_email'],
        'content' => $comment['content'], // ⚠️ NON sanitizzato!
        'created_at' => $comment['created_at']
    ];
}

echo json_encode($comments_array);
?>

