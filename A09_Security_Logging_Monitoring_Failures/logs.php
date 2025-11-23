<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'logger.php';
require_once 'header.php';

$page_title = 'Logs';

// VULNERABILITÀ CRITICA: Log accessibili pubblicamente senza autenticazione/autorizzazione!
// VULNERABILITÀ: Log facilmente cancellabili!

$action = $_GET['action'] ?? 'view';

if ($action === 'clear' && isset($_POST['confirm'])) {
    // VULNERABILITÀ CRITICA: Permette cancellazione log senza controllo accessi!
    $logger->clearLogs();
    $success = "Logs cancellati con successo!";
}

if ($action === 'delete_line' && isset($_GET['line'])) {
    // VULNERABILITÀ: Permette cancellazione singole righe di log!
    $line_num = intval($_GET['line']);
    $logs = $logger->getLogs(1000);
    unset($logs[$line_num]);
    file_put_contents('/var/www/html/logs/app.log', implode("\n", array_reverse($logs)) . "\n");
    $success = "Riga di log cancellata!";
}

// VULNERABILITÀ: Mostra log senza controllo accessi
$logs = $logger->getLogs(100);
$db_logs = [];
$stmt = $conn->query("SELECT * FROM access_logs ORDER BY created_at DESC LIMIT 50");
$db_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Logs del Sistema</h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="logs-actions" style="margin-bottom: 20px;">
    <a href="logs.php?action=clear" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler cancellare tutti i log?');">Cancella Tutti i Log</a>
    <a href="logs.php" class="btn btn-secondary">Ricarica</a>
</div>

<div class="logs-container">
    <div class="log-section">
        <h2>File Log (app.log)</h2>
        <div class="vulnerability-warning">
            <strong>⚠️ VULNERABILITÀ:</strong> I log sono accessibili e modificabili senza controllo accessi!
        </div>
        <div class="log-content">
            <?php if (empty($logs)): ?>
                <p>Nessun log disponibile</p>
            <?php else: ?>
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Timestamp</th>
                            <th>Log Entry</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $index => $log): ?>
                            <?php if (!empty(trim($log))): ?>
                                <tr>
                                    <td><?php echo $index; ?></td>
                                    <td><?php echo htmlspecialchars(substr($log, 1, 19)); ?></td>
                                    <td><pre style="margin: 0; font-size: 0.9em;"><?php echo htmlspecialchars($log); ?></pre></td>
                                    <td>
                                        <a href="logs.php?action=delete_line&line=<?php echo $index; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancellare questa riga?');">Elimina</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="log-section" style="margin-top: 30px;">
        <h2>Database Logs (access_logs)</h2>
        <div class="vulnerability-warning">
            <strong>⚠️ VULNERABILITÀ:</strong> I log nel database sono incompleti - molti eventi critici non sono loggati!
        </div>
        <div class="log-content">
            <?php if (empty($db_logs)): ?>
                <p>Nessun log disponibile nel database</p>
            <?php else: ?>
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Resource</th>
                            <th>Action</th>
                            <th>IP</th>
                            <th>Success</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($db_logs as $log): ?>
                            <tr>
                                <td><?php echo $log['id']; ?></td>
                                <td><?php echo htmlspecialchars($log['user_id'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['resource_id'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
                                <td><?php echo $log['success'] ? '✅' : '❌'; ?></td>
                                <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($action === 'clear'): ?>
    <div class="modal" style="display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div class="modal-content" style="background: white; padding: 30px; border-radius: 8px; max-width: 500px; margin: 100px auto;">
            <h2>Conferma Cancellazione</h2>
            <p>Sei sicuro di voler cancellare tutti i log? Questa azione non può essere annullata.</p>
            <form method="POST" action="">
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn btn-danger">Conferma</button>
                <a href="logs.php" class="btn btn-secondary">Annulla</a>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

