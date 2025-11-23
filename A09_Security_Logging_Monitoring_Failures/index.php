<?php
session_start();
require_once 'db_connect.php';
require_once 'header.php';
?>

<div class="hero">
    <h1>Benvenuto nel Vault System</h1>
    <p>Sistema di gestione risorse sensibili con vulnerabilità intenzionali per scopi didattici</p>
    <div class="hero-buttons">
        <a href="login.php" class="btn">Accedi</a>
        <a href="register.php" class="btn btn-secondary">Registrati</a>
    </div>
</div>

<div class="features">
    <div class="feature-card">
        <h3>🔐 Risorse Sensibili</h3>
        <p>Gestisci e accedi a risorse sensibili come chiavi API, credenziali, documenti riservati</p>
    </div>
    <div class="feature-card">
        <h3>📊 Audit Trail</h3>
        <p>Sistema di logging per tracciare accessi e operazioni (vulnerabile intenzionalmente)</p>
    </div>
    <div class="feature-card">
        <h3>⚙️ Controllo Accessi</h3>
        <p>Gestione dei livelli di accesso per risorse confidenziali e top secret</p>
    </div>
</div>

<?php require_once 'footer.php'; ?>

