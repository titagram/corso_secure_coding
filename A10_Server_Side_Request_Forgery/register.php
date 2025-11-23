<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'db_connect.php';
require_once 'header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Registrati</h2>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <form method="POST" action="register_process.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Registrati</button>
        </form>
        <p style="margin-top: 20px;">Hai già un account? <a href="login.php">Accedi</a></p>
    </div>
</div>

<?php require_once 'footer.php'; ?>

