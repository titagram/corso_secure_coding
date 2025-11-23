<?php
session_start();
require_once 'db_connect.php';
require_once 'header.php';

$type = $_GET['type'] ?? '';

$query = "SELECT * FROM services WHERE is_active = 1";
if ($type) {
    $query .= " AND service_type = ?";
}

$stmt = $conn->prepare($query);
if ($type) {
    $stmt->bind_param("s", $type);
}
$stmt->execute();
$services = $stmt->get_result();
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="services.php">📋 Servizi</a>
    </div>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn">Dashboard</a>
        <?php else: ?>
            <a href="login.php" class="btn">Accedi</a>
        <?php endif; ?>
    </div>
</div>

<h1>📋 Servizi Disponibili</h1>

<div class="grid">
    <?php while ($service = $services->fetch_assoc()): ?>
        <div class="service-card">
            <h3><?php echo htmlspecialchars($service['name']); ?></h3>
            <p><?php echo htmlspecialchars($service['description']); ?></p>
            <div class="service-price">€<?php echo number_format($service['base_price'], 2); ?></div>
            <p><small>Capacità massima: <?php echo $service['max_capacity']; ?> persone</small></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="create_booking.php?service_id=<?php echo $service['id']; ?>" class="btn">Prenota Ora</a>
            <?php else: ?>
                <a href="login.php" class="btn">Accedi per Prenotare</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<?php
require_once 'footer.php';
?>

