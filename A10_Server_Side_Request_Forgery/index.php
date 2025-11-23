<?php
session_start();
require_once 'db_connect.php';
require_once 'header.php';
?>

<div class="hero">
    <h1>Benvenuto nel URL Preview System</h1>
    <p>Sistema per generare anteprime di link con vulnerabilità SSRF intenzionali per scopi didattici</p>
    <div class="hero-buttons">
        <a href="login.php" class="btn">Accedi</a>
        <a href="register.php" class="btn btn-secondary">Registrati</a>
    </div>
</div>

<div class="features">
    <div class="feature-card">
        <h3>🔗 Preview URL</h3>
        <p>Inserisci un URL e ottieni un'anteprima con titolo, descrizione e immagine</p>
    </div>
    <div class="feature-card">
        <h3>📊 Storia</h3>
        <p>Visualizza la storia di tutti gli URL che hai processato</p>
    </div>
    <div class="feature-card">
        <h3>⚡ Veloce</h3>
        <p>Sistema veloce per generare preview di link in tempo reale</p>
    </div>
</div>

<div class="vulnerability-warning" style="margin-top: 40px;">
    <h3>⚠️ VULNERABILITÀ PRESENTI:</h3>
    <ul>
        <li><strong>SSRF:</strong> Il sistema fetcha URL senza validazione</li>
        <li><strong>Accesso a risorse interne:</strong> Possibile accedere a localhost, IP privati, metadata services</li>
        <li><strong>Bypass filtri:</strong> Filtri deboli facilmente bypassabili</li>
        <li><strong>Port scanning:</strong> Possibile scansionare porte interne</li>
    </ul>
</div>

<?php require_once 'footer.php'; ?>

