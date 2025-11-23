<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'header.php';

$page_title = 'Plugin';
?>

<h1>Gestione Plugin</h1>

<?php
$stmt = $conn->query("SELECT * FROM plugins ORDER BY name");
$plugins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="plugins-grid">
    <?php foreach ($plugins as $plugin): ?>
        <div class="plugin-card">
            <div class="plugin-header">
                <h3><?php echo htmlspecialchars($plugin['name']); ?></h3>
                <span class="plugin-version">v<?php echo htmlspecialchars($plugin['version']); ?></span>
            </div>
            <p class="plugin-description"><?php echo htmlspecialchars($plugin['description']); ?></p>
            <p class="plugin-author">Autore: <?php echo htmlspecialchars($plugin['author']); ?></p>
            <div class="plugin-status">
                <?php if ($plugin['is_active']): ?>
                    <span class="badge badge-success">Attivo</span>
                <?php else: ?>
                    <span class="badge badge-secondary">Inattivo</span>
                <?php endif; ?>
            </div>
            <div class="plugin-actions">
                <a href="install_plugin.php?id=<?php echo $plugin['id']; ?>" class="btn btn-sm">Installa</a>
                <a href="plugin_config.php?id=<?php echo $plugin['id']; ?>" class="btn btn-sm btn-secondary">Configura</a>
                <?php
                // Verifica se ci sono aggiornamenti disponibili
                $update_stmt = $conn->prepare("SELECT * FROM plugin_updates WHERE plugin_id = ? ORDER BY created_at DESC LIMIT 1");
                $update_stmt->execute([$plugin['id']]);
                $update = $update_stmt->fetch(PDO::FETCH_ASSOC);
                if ($update): ?>
                    <a href="update_plugin.php?plugin_id=<?php echo $plugin['id']; ?>&update_id=<?php echo $update['id']; ?>" class="btn btn-sm btn-warning">Aggiorna</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'footer.php'; ?>

