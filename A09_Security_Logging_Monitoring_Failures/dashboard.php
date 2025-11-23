<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'logger.php';
require_once 'header.php';

$page_title = 'Dashboard';

// VULNERABILITÀ: Non logga accesso alla dashboard
?>

<div class="dashboard-header">
    <h1>Dashboard</h1>
    <p>Benvenuto, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Risorse Totali</h3>
        <?php
        $stmt = $conn->query("SELECT COUNT(*) as count FROM resources");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo '<p class="stat-number">' . $result['count'] . '</p>';
        ?>
    </div>
    <div class="stat-card">
        <h3>Risorse Accessibili</h3>
        <?php
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        if ($role === 'admin') {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM resources");
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM resources WHERE access_level IN ('public', 'restricted') OR owner_id = ?");
            $stmt->execute([$user_id]);
        }
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
</div>

<div class="dashboard-actions">
    <a href="vault.php" class="btn">Accedi al Vault</a>
    <a href="logs.php" class="btn btn-secondary">Visualizza Logs</a>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="admin.php" class="btn btn-warning">Pannello Admin</a>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>

