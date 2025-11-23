<?php
require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="products.php">🛍️ Prodotti</a>
    </div>
    <div>
        <a href="login.php" class="btn">Accedi</a>
        <a href="register.php" class="btn btn-success">Registrati</a>
    </div>
</div>

<h1>Benvenuto nel nostro E-Commerce</h1>

<div class="alert alert-info">
    <strong>⚠️ Nota di Sicurezza:</strong> Questo sito utilizza HTTP invece di HTTPS. 
    I dati sensibili trasmessi potrebbero essere intercettati da attaccanti sulla stessa rete.
</div>

<div class="grid">
    <div class="card">
        <h2>🛍️ Catalogo Prodotti</h2>
        <p>Esplora la nostra vasta gamma di prodotti tecnologici e accessori.</p>
        <a href="products.php" class="btn">Vedi Prodotti</a>
    </div>
    
    <div class="card">
        <h2>👤 Area Utente</h2>
        <p>Accedi al tuo account per gestire ordini, pagamenti e profilo.</p>
        <a href="login.php" class="btn">Accedi</a>
    </div>
    
    <div class="card">
        <h2>📦 I Tuoi Ordini</h2>
        <p>Visualizza lo stato dei tuoi ordini e la cronologia acquisti.</p>
        <a href="login.php" class="btn">Accedi per vedere</a>
    </div>
</div>

<?php
require_once 'footer.php';
?>

