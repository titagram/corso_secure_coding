<?php
session_start();
require_once 'db_connect.php';
require_once 'header.php';
?>

<div class="hero">
    <h1>Benvenuto nel Plugin System</h1>
    <p>Sistema di gestione plugin con vulnerabilità intenzionali per scopi didattici</p>
    <div class="hero-buttons">
        <a href="login.php" class="btn">Accedi</a>
        <a href="register.php" class="btn btn-secondary">Registrati</a>
    </div>
</div>

<div class="features">
    <div class="feature-card">
        <h3>📦 Gestione Plugin</h3>
        <p>Carica, installa e gestisci plugin per estendere le funzionalità del sistema</p>
    </div>
    <div class="feature-card">
        <h3>🔄 Aggiornamenti Automatici</h3>
        <p>Sistema di aggiornamento automatico per mantenere i plugin aggiornati</p>
    </div>
    <div class="feature-card">
        <h3>⚙️ Configurazione Avanzata</h3>
        <p>Configura i plugin tramite file di configurazione serializzati</p>
    </div>
</div>

<?php require_once 'footer.php'; ?>

