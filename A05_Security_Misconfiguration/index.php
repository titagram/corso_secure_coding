<?php
session_start();
require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="files.php">📁 Documenti</a>
    </div>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn">Dashboard</a>
        <?php else: ?>
            <a href="login.php" class="btn">Accedi</a>
            <a href="register.php" class="btn btn-success">Registrati</a>
        <?php endif; ?>
    </div>
</div>

<h1>Benvenuto nel Sistema di Gestione Documentale</h1>

<div class="alert alert-info">
    <strong>ℹ️ Sistema di Gestione Documenti</strong><br>
    Carica, gestisci e condividi i tuoi documenti in modo sicuro.
</div>

<div class="alert alert-warning">
    <strong>⚠️ VULNERABILITÀ:</strong> Questo sistema presenta configurazioni di sicurezza errate.
    Prova ad accedere a directory e file sensibili!
</div>

<div class="grid">
    <div class="card">
        <h2>📁 Gestione Documenti</h2>
        <p>Carica e gestisci i tuoi documenti in modo semplice e veloce.</p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="files.php" class="btn">Vai ai Documenti</a>
        <?php else: ?>
            <a href="login.php" class="btn">Accedi per iniziare</a>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h2>🔍 Esplora Directory</h2>
        <p>VULNERABILITÀ: Directory listing abilitato!</p>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li><a href="uploads/" target="_blank">📂 /uploads/</a></li>
            <li><a href="backups/" target="_blank">📂 /backups/</a></li>
            <li><a href="logs/" target="_blank">📂 /logs/</a></li>
            <li><a href="admin/" target="_blank">📂 /admin/</a></li>
        </ul>
    </div>
    
    <div class="card">
        <h2>📄 File Sensibili</h2>
        <p>VULNERABILITÀ: File di configurazione esposti!</p>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li><a href="environment.txt" target="_blank">🔐 environment.txt</a> (equivalente a .env)</li>
            <li><a href="config.php" target="_blank">⚙️ config.php</a></li>
            <li><a href="backup.sql" target="_blank">💾 backup.sql</a></li>
            <li><a href="backup_2024-11-20.sql.bak" target="_blank">💾 backup_2024-11-20.sql.bak</a></li>
            <li><a href="logs/app.log" target="_blank">📋 logs/app.log</a></li>
        </ul>
    </div>
</div>

<?php
require_once 'footer.php';
?>

