<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'header.php';

$page_title = 'Admin Panel';
?>

<h1>Pannello Amministrazione</h1>

<div class="admin-stats">
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
            <h3>URL Processati</h3>
            <?php
            $stmt = $conn->query("SELECT COUNT(*) as count FROM url_history");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<p class="stat-number">' . $result['count'] . '</p>';
            ?>
        </div>
        <div class="stat-card">
            <h3>Preview Riuscite</h3>
            <?php
            $stmt = $conn->query("SELECT COUNT(*) as count FROM url_previews WHERE status = 'success'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<p class="stat-number">' . $result['count'] . '</p>';
            ?>
        </div>
    </div>
</div>

<div class="admin-warning" style="margin-top: 30px;">
    <h2>⚠️ VULNERABILITÀ SSRF - Informazioni</h2>
    <p>Questo sistema è vulnerabile a Server-Side Request Forgery (SSRF). Un attaccante può:</p>
    <ul>
        <li>Accedere a risorse interne (localhost, IP privati)</li>
        <li>Scansionare porte interne</li>
        <li>Accedere a metadata services (AWS, GCP, Azure)</li>
        <li>Bypassare filtri con encoding e tecniche avanzate</li>
        <li>Accedere a servizi interni (database, Redis, API)</li>
    </ul>
</div>

<div class="recent-urls" style="margin-top: 30px;">
    <h2>URL Recenti Processati</h2>
    <?php
    $stmt = $conn->query("SELECT * FROM url_history ORDER BY created_at DESC LIMIT 20");
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>URL</th>
                <th>Metodo</th>
                <th>Response Code</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent as $item): ?>
                <tr>
                    <td><?php echo $item['user_id']; ?></td>
                    <td><code><?php echo htmlspecialchars($item['url']); ?></code></td>
                    <td><?php echo htmlspecialchars($item['method']); ?></td>
                    <td><?php echo $item['response_code'] ?? 'N/A'; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>

