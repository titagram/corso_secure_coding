<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'logger.php';
require_once 'header.php';

$page_title = 'Admin Panel';

// VULNERABILITÀ: Non logga accesso al pannello admin!

$action = $_GET['action'] ?? 'dashboard';

if ($action === 'create_transaction' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? $_SESSION['user_id'];
    $resource_id = $_POST['resource_id'] ?? null;
    $transaction_type = $_POST['transaction_type'] ?? 'access';
    $amount = floatval($_POST['amount'] ?? 0);
    $description = $_POST['description'] ?? '';
    
    // VULNERABILITÀ: Crea transazioni senza alerting per importi sospetti!
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, resource_id, transaction_type, amount, description, status) VALUES (?, ?, ?, ?, ?, 'completed')");
    $stmt->execute([$user_id, $resource_id, $transaction_type, $amount, $description]);
    
    // VULNERABILITÀ: Logga transazione ma non genera alert per importi alti!
    $logger->logTransaction($transaction_type, $amount, [
        'user_id' => $user_id,
        'resource_id' => $resource_id,
        'description' => $description
    ]);
    
    // VULNERABILITÀ: Nessun alerting per transazioni sospette (es. importi > €10,000)
    // Nessun monitoraggio in tempo reale!
    
    $success = "Transazione creata con successo!";
}
?>

<h1>Pannello Amministrazione</h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="admin-tabs">
    <a href="admin.php?action=dashboard" class="tab <?php echo $action === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
    <a href="admin.php?action=transactions" class="tab <?php echo $action === 'transactions' ? 'active' : ''; ?>">Transazioni</a>
    <a href="admin.php?action=create_transaction" class="tab <?php echo $action === 'create_transaction' ? 'active' : ''; ?>">Crea Transazione</a>
    <a href="admin.php?action=monitoring" class="tab <?php echo $action === 'monitoring' ? 'active' : ''; ?>">Monitoring</a>
</div>

<?php if ($action === 'dashboard'): ?>
    <div class="admin-dashboard">
        <h2>Statistiche Sistema</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Utenti Totali</h3>
                <?php
                $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo '<p class="stat-number">' . $result['count'] . '</p>';
                ?>
            </div>
            <div class="stat-card">
                <h3>Risorse Totali</h3>
                <?php
                $stmt = $conn->query("SELECT COUNT(*) as count FROM resources");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo '<p class="stat-number">' . $result['count'] . '</p>';
                ?>
            </div>
            <div class="stat-card">
                <h3>Transazioni Oggi</h3>
                <?php
                $stmt = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE()");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo '<p class="stat-number">' . $result['count'] . '</p>';
                ?>
            </div>
            <div class="stat-card">
                <h3>Valore Transazioni Oggi</h3>
                <?php
                $stmt = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE DATE(created_at) = CURDATE()");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo '<p class="stat-number">€' . number_format($result['total'] ?? 0, 2) . '</p>';
                ?>
            </div>
        </div>
        
        <div class="vulnerability-warning" style="margin-top: 30px;">
            <h3>⚠️ VULNERABILITÀ PRESENTI:</h3>
            <ul>
                <li><strong>Nessun alerting:</strong> Non ci sono notifiche per attività sospette</li>
                <li><strong>Nessun monitoraggio:</strong> Non c'è analisi in tempo reale dei log</li>
                <li><strong>Log incompleti:</strong> Molti eventi critici non sono loggati</li>
                <li><strong>Nessuna correlazione:</strong> Non vengono correlati eventi sospetti</li>
            </ul>
        </div>
    </div>

<?php elseif ($action === 'transactions'): ?>
    <div class="transactions-list">
        <h2>Transazioni</h2>
        <?php
        $stmt = $conn->query("SELECT t.*, u.username, r.name as resource_name FROM transactions t LEFT JOIN users u ON t.user_id = u.id LEFT JOIN resources r ON t.resource_id = r.id ORDER BY t.created_at DESC LIMIT 50");
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Utente</th>
                    <th>Risorsa</th>
                    <th>Tipo</th>
                    <th>Importo</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td><?php echo $tx['id']; ?></td>
                        <td><?php echo htmlspecialchars($tx['username']); ?></td>
                        <td><?php echo htmlspecialchars($tx['resource_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($tx['transaction_type']); ?></td>
                        <td>€<?php echo number_format($tx['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($tx['status']); ?></td>
                        <td><?php echo htmlspecialchars($tx['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($action === 'create_transaction'): ?>
    <div class="create-transaction">
        <h2>Crea Nuova Transazione</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="user_id">User ID</label>
                <input type="number" id="user_id" name="user_id" value="<?php echo $_SESSION['user_id']; ?>" required>
            </div>
            <div class="form-group">
                <label for="resource_id">Resource ID (opzionale)</label>
                <input type="number" id="resource_id" name="resource_id">
            </div>
            <div class="form-group">
                <label for="transaction_type">Tipo Transazione</label>
                <select id="transaction_type" name="transaction_type" required>
                    <option value="access">Access</option>
                    <option value="modify">Modify</option>
                    <option value="delete">Delete</option>
                    <option value="transfer">Transfer</option>
                    <option value="export">Export</option>
                </select>
            </div>
            <div class="form-group">
                <label for="amount">Importo</label>
                <input type="number" id="amount" name="amount" step="0.01" value="0.00" required>
                <small>⚠️ VULNERABILITÀ: Importi alti non generano alert!</small>
            </div>
            <div class="form-group">
                <label for="description">Descrizione</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <button type="submit" class="btn">Crea Transazione</button>
        </form>
    </div>

<?php elseif ($action === 'monitoring'): ?>
    <div class="monitoring-panel">
        <h2>Monitoring (Non Implementato)</h2>
        <div class="vulnerability-warning">
            <h3>⚠️ VULNERABILITÀ CRITICA:</h3>
            <ul>
                <li><strong>Nessun monitoraggio in tempo reale:</strong> Non c'è sistema di monitoraggio attivo</li>
                <li><strong>Nessun alerting:</strong> Non vengono generati alert per attività sospette</li>
                <li><strong>Nessuna analisi pattern:</strong> Non vengono rilevati pattern sospetti</li>
                <li><strong>Nessuna correlazione eventi:</strong> Non vengono correlati eventi multipli</li>
                <li><strong>Nessun SIEM:</strong> Non c'è integrazione con sistemi SIEM</li>
            </ul>
        </div>
        <p>In un sistema sicuro, qui dovrebbero essere mostrati:</p>
        <ul>
            <li>Alert in tempo reale per attività sospette</li>
            <li>Correlazione di eventi multipli</li>
            <li>Analisi pattern di accesso</li>
            <li>Notifiche per transazioni ad alto valore</li>
            <li>Rilevamento di anomalie</li>
        </ul>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

