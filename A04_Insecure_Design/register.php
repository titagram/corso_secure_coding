<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
    </div>
</div>

<div class="login-container">
    <h1>Registra un Nuovo Account</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo htmlspecialchars($_SESSION['error']); 
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register_process.php">
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="full_name">Nome Completo *</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>

        <div class="form-group">
            <label for="phone">Telefono</label>
            <input type="tel" id="phone" name="phone">
        </div>

        <button type="submit" class="btn" style="width: 100%;">Registrati</button>
    </form>

    <p style="text-align: center; margin-top: 20px;">
        Hai già un account? <a href="login.php">Accedi qui</a>
    </p>
</div>

<?php
require_once 'footer.php';
?>

