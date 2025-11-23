<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'URL Preview System'; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🔗 URL Preview System</h1>
            </div>
            <div class="nav-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php">Home</a>
                    <a href="preview.php">Crea Preview</a>
                    <a href="history.php">Storia</a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin.php">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                <?php else: ?>
                    <a href="index.php">Home</a>
                    <a href="login.php">Accedi</a>
                    <a href="register.php">Registrati</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container">

