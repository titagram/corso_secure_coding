<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'header.php';

$page_title = 'Storia URL';
?>

<h1>Storia URL Processati</h1>

<div class="history-stats">
    <?php
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM url_history WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as success FROM url_previews WHERE user_id = ? AND status = 'success'");
    $stmt->execute([$user_id]);
    $success = $stmt->fetch(PDO::FETCH_ASSOC)['success'];
    ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Totale URL</h3>
            <p class="stat-number"><?php echo $total; ?></p>
        </div>
        <div class="stat-card">
            <h3>Preview Riuscite</h3>
            <p class="stat-number"><?php echo $success; ?></p>
        </div>
    </div>
</div>

<div class="history-list">
    <h2>Ultimi URL Processati</h2>
    <?php
    $stmt = $conn->prepare("SELECT * FROM url_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$user_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <?php if (empty($history)): ?>
        <p>Nessun URL processato ancora.</p>
    <?php else: ?>
        <table class="history-table">
            <thead>
                <tr>
                    <th>URL</th>
                    <th>Metodo</th>
                    <th>Response Code</th>
                    <th>Response Time</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $item): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($item['url']); ?></code></td>
                        <td><?php echo htmlspecialchars($item['method']); ?></td>
                        <td><?php echo $item['response_code'] ?? 'N/A'; ?></td>
                        <td><?php echo $item['response_time']; ?>ms</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="previews-list" style="margin-top: 40px;">
    <h2>Preview Salvate</h2>
    <?php
    $stmt = $conn->prepare("SELECT * FROM url_previews WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$user_id]);
    $previews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <?php if (empty($previews)): ?>
        <p>Nessuna preview salvata.</p>
    <?php else: ?>
        <div class="previews-grid">
            <?php foreach ($previews as $preview): ?>
                <div class="preview-item">
                    <h4><?php echo htmlspecialchars($preview['title'] ?: 'Nessun titolo'); ?></h4>
                    <p class="preview-url"><code><?php echo htmlspecialchars($preview['url']); ?></code></p>
                    <p class="preview-description"><?php echo htmlspecialchars(substr($preview['description'] ?: 'Nessuna descrizione', 0, 100)); ?></p>
                    <span class="badge badge-<?php echo $preview['status'] === 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($preview['status']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>

