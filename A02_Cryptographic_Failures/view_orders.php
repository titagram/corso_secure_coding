<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Recupera tutti gli ordini dell'utente
$stmt = $conn->prepare("SELECT o.*, 
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="view_orders.php">📦 Ordini</a>
    </div>
    <div class="user-info">
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>📦 I Tuoi Ordini</h1>

<?php if ($orders->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID Ordine</th>
                <th>Data</th>
                <th>Importo Totale</th>
                <th>Stato</th>
                <th>Metodo Pagamento</th>
                <th>Indirizzo Spedizione</th>
                <th>Prodotti</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                    <td><strong>€<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                    <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                    <td class="sensitive-data"><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                    <td><?php echo $order['item_count']; ?> prodotto/i</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">
        <p>Nessun ordine trovato.</p>
    </div>
<?php endif; ?>

<?php
require_once 'footer.php';
?>

