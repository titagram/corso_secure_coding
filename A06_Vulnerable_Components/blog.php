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

<h1>📝 Tutti i Post del Blog</h1>

<?php
$result = $conn->query("SELECT p.*, u.username, u.full_name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.status = 'published' ORDER BY p.created_at DESC");
if ($result->num_rows > 0):
    while ($post = $result->fetch_assoc()):
?>
    <div class="post-card">
        <h2 class="post-title">
            <a href="post.php?id=<?php echo $post['id']; ?>" style="color: #667eea; text-decoration: none;">
                <?php echo htmlspecialchars($post['title']); ?>
            </a>
        </h2>
        <div class="post-meta">
            Di <strong><?php echo htmlspecialchars($post['full_name']); ?></strong> 
            il <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
        </div>
        <?php if ($post['excerpt']): ?>
            <div class="post-content">
                <?php echo htmlspecialchars($post['excerpt']); ?>
            </div>
        <?php endif; ?>
        <a href="post.php?id=<?php echo $post['id']; ?>" class="btn">Leggi tutto</a>
    </div>
<?php
    endwhile;
else:
?>
    <div class="alert alert-info">
        <p>Nessun post pubblicato ancora.</p>
    </div>
<?php endif; ?>

<?php
require_once 'footer.php';
?>

