<?php
require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="services.php">📋 Servizi</a>
    </div>
    <div>
        <a href="login.php" class="btn">Accedi</a>
        <a href="register.php" class="btn btn-success">Registrati</a>
    </div>
</div>

<h1>Benvenuto nel Sistema di Prenotazioni</h1>

<div class="alert alert-info">
    <strong>ℹ️ Sistema di Prenotazioni</strong><br>
    Prenota tavoli, stanze d'hotel, eventi e molto altro. 
    Gestisci le tue prenotazioni in modo semplice e veloce.
</div>

<div class="grid">
    <div class="card">
        <h2>🍽️ Ristorante</h2>
        <p>Prenota il tuo tavolo preferito per una cena indimenticabile.</p>
        <a href="services.php?type=restaurant" class="btn">Vedi Disponibilità</a>
    </div>
    
    <div class="card">
        <h2>🏨 Hotel</h2>
        <p>Prenota la tua camera per un soggiorno confortevole.</p>
        <a href="services.php?type=hotel" class="btn">Vedi Disponibilità</a>
    </div>
    
    <div class="card">
        <h2>🎉 Eventi</h2>
        <p>Prenota il tuo posto per eventi esclusivi e workshop.</p>
        <a href="services.php?type=event" class="btn">Vedi Disponibilità</a>
    </div>
</div>

<?php
require_once 'footer.php';
?>

