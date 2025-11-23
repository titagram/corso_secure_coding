<?php
/**
 * Dashboard utente
 * OWASP A02: Cryptographic Failures
 * 
 * VULNERABILITÀ: Mostra dati sensibili in chiaro
 */

session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Recupera dati utente (in chiaro!)
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Recupera ordini
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="products.php">🛍️ Prodotti</a>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="payment.php">💳 Pagamenti</a>
        <a href="view_orders.php">📦 Ordini</a>
    </div>
    <div class="user-info">
        Ciao, <?php echo htmlspecialchars($user['full_name']); ?> | 
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>Dashboard Utente</h1>

<div class="stats">
    <div class="stat-card">
        <h3><?php echo $orders->num_rows; ?></h3>
        <p>Ordini Recenti</p>
    </div>
    <div class="stat-card">
        <h3>€<?php 
            $stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
            echo number_format($total, 2);
        ?></h3>
        <p>Totale Speso</p>
    </div>
</div>

<div class="card">
    <div class="card-header">📋 I Tuoi Dati Personali</div>
    
    <div class="vulnerability-warning">
        <strong>⚠️ VULNERABILITÀ:</strong> I seguenti dati sono memorizzati e visualizzati in CHIARO nel database.
        Dati sensibili come Codice Fiscale, indirizzi e numeri di telefono non sono cifrati.
    </div>

    <table>
        <tr>
            <th style="width: 200px;">Campo</th>
            <th>Valore</th>
        </tr>
        <tr>
            <td><strong>Username</strong></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td class="sensitive-data"><?php echo htmlspecialchars($user['email']); ?></td>
        </tr>
        <tr>
            <td><strong>Nome Completo</strong></td>
            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
        </tr>
        <tr>
            <td><strong>Telefono</strong></td>
            <td class="sensitive-data"><?php echo htmlspecialchars($user['phone'] ?? 'Non fornito'); ?></td>
        </tr>
        <tr>
            <td><strong>Indirizzo</strong></td>
            <td class="sensitive-data"><?php echo htmlspecialchars($user['address'] ?? 'Non fornito'); ?></td>
        </tr>
        <tr>
            <td><strong>Città</strong></td>
            <td><?php echo htmlspecialchars($user['city'] ?? 'Non fornita'); ?></td>
        </tr>
        <tr>
            <td><strong>CAP</strong></td>
            <td><?php echo htmlspecialchars($user['postal_code'] ?? 'Non fornito'); ?></td>
        </tr>
        <tr>
            <td><strong>Codice Fiscale</strong></td>
            <td class="sensitive-data"><?php echo htmlspecialchars($user['tax_code'] ?? 'Non fornito'); ?></td>
        </tr>
        <tr>
            <td><strong>Data di Nascita</strong></td>
            <td class="sensitive-data"><?php echo htmlspecialchars($user['date_of_birth'] ?? 'Non fornita'); ?></td>
        </tr>
        <tr>
            <td><strong>Password Hash</strong></td>
            <td class="sensitive-data" style="font-size: 0.8em; word-break: break-all;">
                <?php echo htmlspecialchars($user['password_hash']); ?>
                <br><small>⚠️ Hash debole (MD5/SHA1) facilmente rompibile con hashcat</small>
            </td>
        </tr>
    </table>
</div>

<div class="card">
    <div class="card-header">📦 Ultimi Ordini</div>
    <?php if ($orders->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID Ordine</th>
                    <th>Data</th>
                    <th>Importo</th>
                    <th>Stato</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                        <td>€<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nessun ordine trovato.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h3>🔧 Funzionalità Disponibili</h3>
    <ul style="line-height: 2;">
        <li><a href="payment.php">💳 Gestisci Carte di Credito</a> - ⚠️ Dati in chiaro!</li>
        <li><a href="view_orders.php">📦 Visualizza Tutti gli Ordini</a></li>
        <li><a href="export_data.php">📥 Esporta Dati Personali</a> - ⚠️ Vulnerabile!</li>
    </ul>
</div>

<?php
require_once 'footer.php';
?>

