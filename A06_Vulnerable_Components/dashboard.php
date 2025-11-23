<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Recupera post dell'utente
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$posts = $stmt->get_result();

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="blog.php">📝 Blog</a>
        <a href="info.php">ℹ️ Info Sistema</a>
    </div>
    <div class="user-info">
        Ciao, <?php echo htmlspecialchars($_SESSION['full_name']); ?> | 
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>📊 Dashboard</h1>

<div class="card">
    <h2>I Tuoi Post</h2>
    <?php if ($posts->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Titolo</th>
                    <th>Stato</th>
                    <th>Data Creazione</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($post = $posts->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo htmlspecialchars($post['status']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></td>
                        <td>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="btn">Visualizza</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nessun post creato ancora.</p>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>

