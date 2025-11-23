<?php
/**
 * Aggiungi carta di credito
 * OWASP A02: Cryptographic Failures
 * 
 * VULNERABILITÀ: Carta di credito salvata in CHIARO nel database
 */

session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = $_POST['card_number'] ?? '';
    $cardholder_name = $_POST['cardholder_name'] ?? '';
    $expiry_month = intval($_POST['expiry_month'] ?? 0);
    $expiry_year = intval($_POST['expiry_year'] ?? 0);
    $cvv = $_POST['cvv'] ?? '';
    
    // VULNERABILITÀ CRITICA: Dati salvati in CHIARO senza cifratura!
    // Nessuna validazione crittografica
    // CVV memorizzato (violazione PCI-DSS)
    
    // Determina tipo carta (semplificato)
    $card_type = 'Visa';
    if (strpos($card_number, '5') === 0) {
        $card_type = 'Mastercard';
    } elseif (strpos($card_number, '3') === 0) {
        $card_type = 'American Express';
    }
    
    // Inserimento in CHIARO nel database
    $stmt = $conn->prepare("INSERT INTO credit_cards (user_id, card_number, cardholder_name, expiry_month, expiry_year, cvv, card_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiiss", $user_id, $card_number, $cardholder_name, $expiry_month, $expiry_year, $cvv, $card_type);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Carta di credito aggiunta con successo.";
        header("Location: payment.php");
        exit();
    } else {
        $_SESSION['error'] = "Errore durante l'aggiunta della carta: " . $conn->error;
    }
}

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="payment.php">💳 Pagamenti</a>
    </div>
    <div class="user-info">
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>➕ Aggiungi Carta di Credito</h1>

<div class="alert alert-danger">
    <strong>⚠️ VULNERABILITÀ:</strong> I dati della carta di credito verranno memorizzati in CHIARO nel database.
    Questo include il CVV, violando completamente gli standard PCI-DSS.
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php 
        echo htmlspecialchars($_SESSION['error']); 
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="add_card.php">
        <div class="form-group">
            <label for="card_number">Numero Carta *</label>
            <input type="text" id="card_number" name="card_number" maxlength="19" required 
                   placeholder="1234 5678 9012 3456">
            <small>⚠️ Verrà memorizzato in CHIARO nel database</small>
        </div>

        <div class="form-group">
            <label for="cardholder_name">Intestatario *</label>
            <input type="text" id="cardholder_name" name="cardholder_name" required>
        </div>

        <div class="grid">
            <div class="form-group">
                <label for="expiry_month">Mese Scadenza *</label>
                <select id="expiry_month" name="expiry_month" required>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="expiry_year">Anno Scadenza *</label>
                <select id="expiry_year" name="expiry_year" required>
                    <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="cvv">CVV *</label>
            <input type="text" id="cvv" name="cvv" maxlength="4" required>
            <small>🚨 CVV verrà memorizzato in CHIARO (violazione PCI-DSS!)</small>
        </div>

        <button type="submit" class="btn">Salva Carta</button>
        <a href="payment.php" class="btn btn-danger">Annulla</a>
    </form>
</div>

<?php
require_once 'footer.php';
?>

