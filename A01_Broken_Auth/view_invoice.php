<?php
session_start();
require_once 'db_connect.php';

// Controllo se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// VULNERABILITÀ: Query che recupera la fattura solo per ID, senza verificare user_id
$invoice_id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Fattura non trovata");
}

$invoice = $result->fetch_assoc();

require_once 'header.php';
?>

<div class="nav">
    <a href="dashboard.php">Home</a>
    <a href="my_invoices.php">Le mie fatture</a>
    <?php if ($_SESSION['role'] === 'manager' || $_SESSION['role'] === 'admin'): ?>
        <a href="reports.php">Report Totali</a>
    <?php endif; ?>
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin_panel.php">Pannello Admin</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</div>

<h1>Dettaglio Fattura #<?php echo $invoice['id']; ?></h1>

<table>
    <tr>
        <th style="width: 200px;">ID Fattura</th>
        <td><?php echo $invoice['id']; ?></td>
    </tr>
    <tr>
        <th>ID Utente Proprietario</th>
        <td><?php echo $invoice['user_id']; ?></td>
    </tr>
    <tr>
        <th>Importo</th>
        <td><strong>€ <?php echo number_format($invoice['amount'], 2, ',', '.'); ?></strong></td>
    </tr>
    <tr>
        <th>Dettagli</th>
        <td><?php echo htmlspecialchars($invoice['details']); ?></td>
    </tr>
    <tr>
        <th>Stato</th>
        <td><?php echo htmlspecialchars($invoice['status']); ?></td>
    </tr>
</table>

<div style="margin-top: 20px;">
    <a href="my_invoices.php" class="btn">← Torna alle mie fatture</a>
</div>

<?php
$stmt->close();
require_once 'footer.php';
?>
