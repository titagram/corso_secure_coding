<?php
session_start();

// Controllo se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'header.php';
?>

<div class="nav">
    <a href="dashboard.php">Home</a>
    <?php if ($_SESSION['role'] === 'user' || $_SESSION['role'] === 'manager' || $_SESSION['role'] === 'admin'): ?>
        <a href="my_invoices.php">Le mie fatture</a>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'manager' || $_SESSION['role'] === 'admin'): ?>
        <a href="reports.php">Report Totali</a>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin_panel.php">Pannello Admin</a>
    <?php endif; ?>

    <a href="logout.php">Logout</a>
</div>

<h1>Dashboard</h1>

<div class="alert alert-success">
    <strong>Ciao, <?php echo htmlspecialchars($_SESSION['username']); ?>!</strong><br>
    Sei un utente con ruolo: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong>
</div>

<h2>Benvenuto nel Sistema di Gestione Fatture</h2>

<div style="margin-top: 30px;">
    <h3>Le tue funzionalità:</h3>
    <ul style="line-height: 2;">
        <?php if ($_SESSION['role'] === 'user'): ?>
            <li>📄 <a href="my_invoices.php">Visualizza le tue fatture</a></li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'manager'): ?>
            <li>📄 <a href="my_invoices.php">Visualizza le tue fatture</a></li>
            <li>📊 <a href="reports.php">Visualizza report totali</a></li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li>📄 <a href="my_invoices.php">Visualizza le tue fatture</a></li>
            <li>📊 <a href="reports.php">Visualizza report totali</a></li>
            <li>⚙️ <a href="admin_panel.php">Accedi al pannello di amministrazione</a></li>
        <?php endif; ?>
    </ul>
</div>

<?php require_once 'footer.php'; ?>
