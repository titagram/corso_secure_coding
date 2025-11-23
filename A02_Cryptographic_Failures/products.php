<?php
require_once 'db_connect.php';
require_once 'header.php';

// Recupera tutti i prodotti
$result = $conn->query("SELECT * FROM products ORDER BY name");
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="products.php">🛍️ Prodotti</a>
    </div>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn">Dashboard</a>
        <?php else: ?>
            <a href="login.php" class="btn">Accedi</a>
        <?php endif; ?>
    </div>
</div>

<h1>🛍️ Catalogo Prodotti</h1>

<div class="product-grid">
    <?php while ($product = $result->fetch_assoc()): ?>
        <div class="product-card">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <div class="product-price">€<?php echo number_format($product['price'], 2); ?></div>
            <p><small>Disponibili: <?php echo $product['stock']; ?> pezzi</small></p>
            <p><small>Categoria: <?php echo htmlspecialchars($product['category']); ?></small></p>
        </div>
    <?php endwhile; ?>
</div>

<?php
require_once 'footer.php';
?>

