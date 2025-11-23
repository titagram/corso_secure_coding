<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
require_once 'db_connect.php';
require_once 'header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Accedi</h2>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <form method="POST" action="login_process.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Accedi</button>
        </form>
        <p style="margin-top: 20px;">Non hai un account? <a href="register.php">Registrati</a></p>
    </div>
</div>

<?php require_once 'footer.php'; ?>

