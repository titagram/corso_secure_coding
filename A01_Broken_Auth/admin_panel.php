<?php
session_start();
require_once 'db_connect.php';

// VULNERABILITÀ: Controllo debole - verifica solo che l'utente sia loggato
// NON verifica che sia un admin!
if (!isset($_SESSION['user_id'])) {
    die('Devi fare il login');
}

// Query per recuperare tutti gli utenti
$result = $conn->query("SELECT id, username, role FROM users ORDER BY id");

require_once 'header.php';
?>

<div class="nav">
    <a href="dashboard.php">Home</a>
    <a href="my_invoices.php">Le mie fatture</a>
    <?php if ($_SESSION['role'] === 'manager' || $_SESSION['role'] === 'admin'): ?>
        <a href="reports.php">Report Totali</a>
    <?php endif; ?>
    <a href="admin_panel.php">Pannello Admin</a>
    <a href="logout.php">Logout</a>
</div>

<h1>⚙️ Pannello di Amministrazione</h1>

<div class="alert alert-danger">
    <strong>Area Riservata Amministratori</strong><br>
    Questa pagina contiene funzionalità sensibili per la gestione del sistema.
</div>

<h2>Elenco Utenti Registrati</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Ruolo</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><strong><?php echo htmlspecialchars($user['role']); ?></strong></td>
                <td>
                    <a href="delete_user.php?id=<?php echo $user['id']; ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Sei sicuro di voler eliminare questo utente?')">
                        Elimina
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div style="margin-top: 30px;">
    <h3>Funzionalità Admin Disponibili:</h3>
    <ul style="line-height: 2;">
        <li>✅ Visualizzazione di tutti gli utenti del sistema</li>
        <li>✅ Eliminazione utenti (funzione attiva)</li>
        <li>✅ Accesso ai dati sensibili di sistema</li>
    </ul>
</div>

<?php require_once 'footer.php'; ?>
