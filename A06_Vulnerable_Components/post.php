<?php
/**
 * Visualizza un singolo post del blog
 * OWASP A06: Vulnerable and Outdated Components
 * 
 * VULNERABILITÀ: XSS con jQuery 1.7.2 (CVE-2011-4969)
 * I commenti vengono visualizzati senza sanitizzazione adeguata
 */

session_start();
require_once 'db_connect.php';

$post_id = intval($_GET['id'] ?? 0);

// Recupera il post
$stmt = $conn->prepare("SELECT p.*, u.username, u.full_name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.status = 'published'");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    die("Post non trovato.");
}

// Recupera commenti
$stmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments = $stmt->get_result();

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="blog.php">📝 Blog</a>
    </div>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn">Dashboard</a>
        <?php else: ?>
            <a href="login.php" class="btn">Accedi</a>
        <?php endif; ?>
    </div>
</div>

<div class="post-card">
    <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="post-meta">
        Di <strong><?php echo htmlspecialchars($post['full_name']); ?></strong> 
        il <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
    </div>
    <div class="post-content">
        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
    </div>
</div>

<div class="card">
    <h2>💬 Commenti</h2>
    
    <?php if ($comments->num_rows > 0): ?>
        <?php while ($comment = $comments->fetch_assoc()): ?>
            <div class="comment" data-comment-id="<?php echo $comment['id']; ?>">
                <div class="comment-author">
                    <?php echo htmlspecialchars($comment['author_name'] ?? 'Anonimo'); ?>
                    <?php if ($comment['author_email']): ?>
                        <small>(<?php echo htmlspecialchars($comment['author_email']); ?>)</small>
                    <?php endif; ?>
                    <small style="float: right;"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></small>
                </div>
                <div class="comment-content-safe">
                    <?php 
                    // Versione sicura (sanitizzata)
                    echo nl2br(htmlspecialchars($comment['content'])); 
                    ?>
                </div>
                <!-- VULNERABILITÀ: Area processata con jQuery .html() (VULNERABILE!) -->
                <!-- Questa area mostra i commenti processati con jQuery .html() senza sanitizzazione -->
                <div class="comment-vulnerable-<?php echo $comment['id']; ?>" style="margin-top: 10px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107;">
                    <small style="color: red;">⚠️ VULNERABILITÀ: Area processata con jQuery .html() - XSS possibile!</small>
                    <div class="vulnerable-content-<?php echo $comment['id']; ?>"></div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nessun commento ancora. Sii il primo a commentare!</p>
    <?php endif; ?>
    
    <h3 style="margin-top: 30px;">Aggiungi un Commento</h3>
    <form method="POST" action="add_comment.php" id="commentForm">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <div class="form-group">
            <label for="author_name">Nome *</label>
            <input type="text" id="author_name" name="author_name" required>
        </div>
        <div class="form-group">
            <label for="author_email">Email</label>
            <input type="email" id="author_email" name="author_email">
        </div>
        <div class="form-group">
            <label for="content">Commento *</label>
            <textarea id="content" name="content" rows="5" required></textarea>
            <small style="color: red;">⚠️ VULNERABILITÀ: jQuery 1.7.2 può eseguire XSS se il contenuto viene processato con .html()</small>
        </div>
        <div class="form-group" style="margin-top: 20px;">
            <button type="submit" class="btn" style="width: 100%; max-width: 300px; display: block; margin: 20px auto 0 auto; padding: 15px 30px; font-size: 16px; color: black;">Invia Commento</button>
        </div>
    </form>
</div>

<!-- VULNERABILITÀ: jQuery 1.7.2 vulnerabile a XSS (CVE-2011-4969) -->
<script>
// VULNERABILITÀ CRITICA: jQuery 1.7.2 processa i commenti con .html()
// Questo permette l'esecuzione di codice JavaScript arbitrario
// CVE-2011-4969: jQuery 1.7.2 non sanitizza correttamente l'input in .html()

$(document).ready(function() {
    // VULNERABILITÀ: Carica commenti da API e processa con jQuery .html() (VULNERABILE!)
    // L'API restituisce commenti NON sanitizzati
    $.getJSON('comments_api.php?post_id=<?php echo $post_id; ?>', function(comments) {
        comments.forEach(function(comment) {
            // VULNERABILITÀ CRITICA: Processa il contenuto con .html() invece di .text()
            // jQuery 1.7.2 eseguirà qualsiasi codice JavaScript nel contenuto!
            var vulnerableDiv = $('.vulnerable-content-' + comment.id);
            if (vulnerableDiv.length) {
                // VULNERABILITÀ: .html() eseguirà il codice JavaScript nel commento
                // Questo è il punto vulnerabile che permette XSS con jQuery 1.7.2!
                vulnerableDiv.html(comment.content); // VULNERABILE a XSS!
                // Se il commento contiene <img src=x onerror="alert('XSS')">, verrà eseguito!
            }
        });
    });
});

// VULNERABILITÀ: Funzione che processa commenti con jQuery .html() (VULNERABILE!)
// Questa funzione può essere chiamata da altri script o eventi
function displayCommentVulnerable(commentId, commentText) {
    // VULNERABILITÀ CRITICA: Usa .html() invece di .text()
    // jQuery 1.7.2 eseguirà qualsiasi codice JavaScript nel commentText
    $('.comment-vulnerable-' + commentId).html(commentText).show(); // VULNERABILE a XSS!
}

// Per sfruttare: inserire nel commento: <img src=x onerror="alert('XSS')">
// Il commento verrà processato da comments_api.php e poi da jQuery .html()
</script>

<?php
require_once 'footer.php';
?>

