<?php
session_start();
require_once 'db_connect.php';

// Controllo se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Query per recuperare solo le fatture dell'utente corrente
$stmt = $conn->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

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

<h1>Le Mie Fatture</h1>

<p>Benvenuto, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>. Ecco le tue fatture:</p>

<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Importo</th>
                <th>Dettagli</th>
                <th>Stato</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($invoice = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $invoice['id']; ?></td>
                    <td>€ <?php echo number_format($invoice['amount'], 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($invoice['details']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['status']); ?></td>
                    <td>
                        <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" class="btn">Visualizza</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">Non hai ancora fatture registrate.</div>
<?php endif; ?>

<?php
$stmt->close();
require_once 'footer.php';
?>
