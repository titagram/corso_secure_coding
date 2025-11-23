<?php
session_start();
require_once 'db_connect.php';

function format_currency($value) {
    return number_format((float) ($value ?? 0), 2, ',', '.');
}

// Controllo se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Controllo ruolo (questa pagina è per manager e admin)
if ($_SESSION['role'] !== 'manager' && $_SESSION['role'] !== 'admin') {
    die('Accesso negato: questa pagina è riservata ai manager e agli amministratori');
}

// Query per statistiche
$total_result = $conn->query("SELECT COUNT(*) as total, SUM(amount) as total_amount FROM invoices");
$total_stats = $total_result->fetch_assoc();

$pending_result = $conn->query("SELECT COUNT(*) as pending_count, SUM(amount) as pending_amount FROM invoices WHERE status = 'pending'");
$pending_stats = $pending_result->fetch_assoc();

$paid_result = $conn->query("SELECT COUNT(*) as paid_count, SUM(amount) as paid_amount FROM invoices WHERE status = 'paid'");
$paid_stats = $paid_result->fetch_assoc();

// Fatture per utente
$user_stats = $conn->query("
    SELECT u.username, COUNT(i.id) as invoice_count, SUM(i.amount) as total_amount
    FROM users u
    LEFT JOIN invoices i ON u.id = i.user_id
    GROUP BY u.id
    ORDER BY total_amount DESC
");

require_once 'header.php';
?>

<div class="nav">
    <a href="dashboard.php">Home</a>
    <a href="my_invoices.php">Le mie fatture</a>
    <a href="reports.php">Report Totali</a>
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin_panel.php">Pannello Admin</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</div>

<h1>📊 Report Totali</h1>

<div class="alert alert-info">
    Benvenuto, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.
    Questa pagina è accessibile solo ai manager e agli amministratori.
</div>

<h2>Statistiche Generali</h2>

<table>
    <tr>
        <th style="width: 300px;">Totale Fatture</th>
        <td><strong><?php echo $total_stats['total']; ?></strong></td>
    </tr>
    <tr>
        <th>Importo Totale</th>
        <td><strong>€ <?php echo format_currency($total_stats['total_amount']); ?></strong></td>
    </tr>
    <tr>
        <th>Fatture in Sospeso</th>
        <td><?php echo $pending_stats['pending_count']; ?> (€ <?php echo format_currency($pending_stats['pending_amount']); ?>)</td>
    </tr>
    <tr>
        <th>Fatture Pagate</th>
        <td><?php echo $paid_stats['paid_count']; ?> (€ <?php echo format_currency($paid_stats['paid_amount']); ?>)</td>
    </tr>
</table>

<h2 style="margin-top: 30px;">Statistiche per Utente</h2>

<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Numero Fatture</th>
            <th>Importo Totale</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($stat = $user_stats->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($stat['username']); ?></td>
                <td><?php echo $stat['invoice_count']; ?></td>
                <td>€ <?php echo format_currency($stat['total_amount']); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>
