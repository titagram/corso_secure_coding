<?php
/**
 * Gestione pagamenti e carte di credito
 * OWASP A02: Cryptographic Failures
 * 
 * VULNERABILITÀ CRITICA: Carte di credito memorizzate in CHIARO
 * Include CVV (violazione PCI-DSS)
 */

session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Recupera tutte le carte di credito dell'utente (IN CHIARO!)
$stmt = $conn->prepare("SELECT * FROM credit_cards WHERE user_id = ? ORDER BY added_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cards = $stmt->get_result();

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="payment.php">💳 Pagamenti</a>
    </div>
    <div class="user-info">
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>💳 Gestione Carte di Credito</h1>

<div class="alert alert-danger">
    <strong>🚨 VULNERABILITÀ CRITICA:</strong> Le carte di credito sono memorizzate in CHIARO nel database.
    Questo include anche il CVV, violando completamente gli standard PCI-DSS.
    In un'applicazione reale, questi dati dovrebbero essere cifrati con AES-256 o tokenizzati.
</div>

<div class="card">
    <div class="card-header">Le Tue Carte di Credito</div>
    
    <?php if ($cards->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Tipo Carta</th>
                    <th>Numero Carta</th>
                    <th>Intestatario</th>
                    <th>Scadenza</th>
                    <th>CVV</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($card = $cards->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($card['card_type']); ?></td>
                        <td class="sensitive-data">
                            <?php echo htmlspecialchars($card['card_number']); ?>
                            <br><small>⚠️ Memorizzato in CHIARO!</small>
                        </td>
                        <td><?php echo htmlspecialchars($card['cardholder_name']); ?></td>
                        <td><?php echo str_pad($card['expiry_month'], 2, '0', STR_PAD_LEFT) . '/' . $card['expiry_year']; ?></td>
                        <td class="sensitive-data">
                            <?php echo htmlspecialchars($card['cvv']); ?>
                            <br><small>🚨 CVV in CHIARO! (Violazione PCI-DSS)</small>
                        </td>
                        <td>
                            <a href="delete_card.php?id=<?php echo $card['id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Sei sicuro di voler eliminare questa carta?')">
                                Elimina
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nessuna carta di credito salvata.</p>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <a href="add_card.php" class="btn">➕ Aggiungi Nuova Carta</a>
    </div>
</div>

<div class="card">
    <h3>📚 Note Didattiche</h3>
    <ul>
        <li><strong>PCI-DSS Standard:</strong> I dati delle carte di credito devono essere cifrati o tokenizzati</li>
        <li><strong>CVV:</strong> Non deve MAI essere memorizzato, nemmeno cifrato</li>
        <li><strong>Best Practice:</strong> Usare servizi di pagamento esterni (Stripe, PayPal) che gestiscono la tokenizzazione</li>
        <li><strong>Cifratura:</strong> Se necessario memorizzare, usare AES-256 con chiavi gestite in modo sicuro</li>
    </ul>
</div>

<?php
require_once 'footer.php';
?>

