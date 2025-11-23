<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'header.php';

$page_title = 'Dashboard';
?>

<div class="dashboard-header">
    <h1>Dashboard</h1>
    <p>Benvenuto, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Plugin Installati</h3>
        <?php
        $stmt = $conn->query("SELECT COUNT(*) as count FROM plugins");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo '<p class="stat-number">' . $result['count'] . '</p>';
        ?>
    </div>
    <div class="stat-card">
        <h3>Plugin Attivi</h3>
        <?php
        $stmt = $conn->query("SELECT COUNT(*) as count FROM plugins WHERE is_active = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo '<p class="stat-number">' . $result['count'] . '</p>';
        ?>
    </div>
    <div class="stat-card">
        <h3>Aggiornamenti Disponibili</h3>
        <?php
        $stmt = $conn->query("SELECT COUNT(*) as count FROM plugin_updates");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo '<p class="stat-number">' . $result['count'] . '</p>';
        ?>
    </div>
</div>

<div class="dashboard-actions">
    <a href="plugins.php" class="btn">Gestisci Plugin</a>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="upload_plugin.php" class="btn btn-secondary">Carica Nuovo Plugin</a>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>

