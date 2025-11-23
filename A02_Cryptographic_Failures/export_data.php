<?php
/**
 * Esportazione dati personali
 * OWASP A02: Cryptographic Failures
 * 
 * VULNERABILITÀ CRITICA: Esporta tutti i dati sensibili in CHIARO
 * Nessuna cifratura del file esportato
 */

session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// VULNERABILITÀ: Recupera tutti i dati sensibili in CHIARO
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Recupera carte di credito (IN CHIARO!)
$stmt = $conn->prepare("SELECT * FROM credit_cards WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cards = $stmt->get_result();

// Recupera ordini
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

// VULNERABILITÀ: Esporta tutto in formato testo in CHIARO
// Nessuna cifratura, nessuna protezione

if (isset($_GET['download'])) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="dati_personali_' . $user['username'] . '.txt"');
    
    echo "========================================\n";
    echo "ESPORTAZIONE DATI PERSONALI\n";
    echo "========================================\n\n";
    
    echo "DATI UTENTE:\n";
    echo "-------------\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Nome Completo: " . $user['full_name'] . "\n";
    echo "Telefono: " . ($user['phone'] ?? 'N/A') . "\n";
    echo "Indirizzo: " . ($user['address'] ?? 'N/A') . "\n";
    echo "Città: " . ($user['city'] ?? 'N/A') . "\n";
    echo "CAP: " . ($user['postal_code'] ?? 'N/A') . "\n";
    echo "Codice Fiscale: " . ($user['tax_code'] ?? 'N/A') . "\n";
    echo "Data di Nascita: " . ($user['date_of_birth'] ?? 'N/A') . "\n";
    echo "Password Hash: " . $user['password_hash'] . "\n";
    echo "⚠️ Hash debole facilmente rompibile con hashcat\n\n";
    
    echo "CARTE DI CREDITO:\n";
    echo "-----------------\n";
    if ($cards->num_rows > 0) {
        while ($card = $cards->fetch_assoc()) {
            echo "Tipo: " . $card['card_type'] . "\n";
            echo "Numero: " . $card['card_number'] . " ⚠️ IN CHIARO!\n";
            echo "Intestatario: " . $card['cardholder_name'] . "\n";
            echo "Scadenza: " . str_pad($card['expiry_month'], 2, '0', STR_PAD_LEFT) . "/" . $card['expiry_year'] . "\n";
            echo "CVV: " . $card['cvv'] . " 🚨 IN CHIARO! (Violazione PCI-DSS)\n";
            echo "---\n";
        }
    } else {
        echo "Nessuna carta di credito salvata.\n";
    }
    
    echo "\nORDINI:\n";
    echo "-------\n";
    if ($orders->num_rows > 0) {
        while ($order = $orders->fetch_assoc()) {
            echo "ID Ordine: " . $order['id'] . "\n";
            echo "Data: " . $order['order_date'] . "\n";
            echo "Importo: €" . number_format($order['total_amount'], 2) . "\n";
            echo "Stato: " . $order['status'] . "\n";
            echo "Indirizzo Spedizione: " . $order['shipping_address'] . "\n";
            echo "---\n";
        }
    } else {
        echo "Nessun ordine trovato.\n";
    }
    
    echo "\n========================================\n";
    echo "⚠️ VULNERABILITÀ: Questo file contiene dati sensibili in CHIARO\n";
    echo "Nessuna cifratura applicata!\n";
    echo "========================================\n";
    
    exit;
}

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="export_data.php">📥 Esporta Dati</a>
    </div>
    <div class="user-info">
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>📥 Esporta Dati Personali</h1>

<div class="alert alert-danger">
    <strong>🚨 VULNERABILITÀ CRITICA:</strong> Questa funzione esporta tutti i dati sensibili in formato testo in CHIARO.
    Il file scaricato conterrà:
    <ul style="margin-top: 10px;">
        <li>Dati personali completi (email, telefono, indirizzo, CF)</li>
        <li>Password hash (facilmente rompibile)</li>
        <li>Carte di credito complete con CVV (in chiaro!)</li>
        <li>Storico ordini e indirizzi di spedizione</li>
    </ul>
    <strong>Nessuna cifratura viene applicata al file esportato!</strong>
</div>

<div class="card">
    <h3>⚠️ Avviso di Sicurezza</h3>
    <p>Il file che verrà scaricato conterrà tutti i tuoi dati sensibili in formato testo non cifrato.</p>
    <p>Assicurati di:</p>
    <ul>
        <li>Non condividere questo file con nessuno</li>
        <li>Eliminarlo dopo l'uso</li>
        <li>Non inviarlo via email o messaggi non cifrati</li>
    </ul>
    
    <a href="export_data.php?download=1" class="btn btn-danger">
        ⬇️ Scarica Dati Personali (IN CHIARO)
    </a>
    <a href="dashboard.php" class="btn">Annulla</a>
</div>

<div class="card">
    <h3>📚 Note Didattiche</h3>
    <p><strong>Come dovrebbe essere implementato:</strong></p>
    <ul>
        <li>Cifrare il file con AES-256 prima del download</li>
        <li>Richiedere una password aggiuntiva per decifrare</li>
        <li>Usare formati sicuri (es. PGP/GPG)</li>
        <li>Non includere dati che non devono essere memorizzati (CVV)</li>
        <li>Tokenizzare i dati delle carte invece di esportarli</li>
    </ul>
</div>

<?php
require_once 'footer.php';
?>

