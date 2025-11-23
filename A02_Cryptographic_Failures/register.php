<?php
/**
 * Pagina di registrazione
 * OWASP A02: Cryptographic Failures
 * 
 * VULNERABILITÀ: Password hashate con algoritmi deboli (MD5, SHA1, SHA256)
 * Dati personali memorizzati in chiaro
 */

session_start();

// Se già loggato, reindirizza alla dashboard (PRIMA di includere header.php)
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

<div class="register-container">
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
            <small>⚠️ Le password vengono hashate con algoritmi deboli (MD5/SHA1)</small>
        </div>

        <div class="form-group">
            <label for="full_name">Nome Completo *</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>

        <div class="form-group">
            <label for="phone">Telefono</label>
            <input type="tel" id="phone" name="phone">
        </div>

        <div class="form-group">
            <label for="address">Indirizzo</label>
            <textarea id="address" name="address" rows="3"></textarea>
        </div>

        <div class="grid">
            <div class="form-group">
                <label for="city">Città</label>
                <input type="text" id="city" name="city">
            </div>

            <div class="form-group">
                <label for="postal_code">CAP</label>
                <input type="text" id="postal_code" name="postal_code">
            </div>
        </div>

        <div class="form-group">
            <label for="tax_code">Codice Fiscale</label>
            <input type="text" id="tax_code" name="tax_code" maxlength="16">
            <small>⚠️ Il codice fiscale viene memorizzato in chiaro</small>
        </div>

        <div class="form-group">
            <label for="date_of_birth">Data di Nascita</label>
            <input type="date" id="date_of_birth" name="date_of_birth">
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

