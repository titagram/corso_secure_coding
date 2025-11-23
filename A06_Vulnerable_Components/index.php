<?php
session_start();
require_once 'db_connect.php';
require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="blog.php">📝 Blog</a>
        <a href="info.php">ℹ️ Info Sistema</a>
    </div>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn">Dashboard</a>
        <?php else: ?>
            <a href="login.php" class="btn">Accedi</a>
        <?php endif; ?>
    </div>
</div>

<h1>Benvenuto nel Blog</h1>

<div class="alert alert-warning">
    <strong>⚠️ VULNERABILITÀ:</strong> Questo sistema utilizza componenti obsoleti e vulnerabili:
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>PHP 7.4 (EOL, vulnerabilità note)</li>
        <li>jQuery 1.7.2 (CVE-2011-4969 - XSS)</li>
        <li>MySQL 5.7 (non più supportato)</li>
        <li>Bootstrap 3.4.1 (obsoleto)</li>
    </ul>
</div>

<div class="card">
    <h2>📝 Ultimi Post</h2>
    <?php
    $result = $conn->query("SELECT p.*, u.username, u.full_name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.status = 'published' ORDER BY p.created_at DESC LIMIT 3");
    if ($result->num_rows > 0):
        while ($post = $result->fetch_assoc()):
    ?>
        <div class="post-card">
            <h3 class="post-title">
                <a href="post.php?id=<?php echo $post['id']; ?>" style="color: #667eea; text-decoration: none;">
                    <?php echo htmlspecialchars($post['title']); ?>
                </a>
            </h3>
            <div class="post-meta">
                Di <strong><?php echo htmlspecialchars($post['full_name']); ?></strong> 
                il <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
            </div>
            <div class="post-content">
                <?php echo htmlspecialchars(substr($post['content'], 0, 200)); ?>...
            </div>
            <a href="post.php?id=<?php echo $post['id']; ?>" class="btn">Leggi tutto</a>
        </div>
    <?php
        endwhile;
    else:
    ?>
        <p>Nessun post pubblicato ancora.</p>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>

