<?php
session_start();
require_once 'db_connect.php';

// VULNERABILITÀ: Controllo debole (come in admin_panel.php)
if (!isset($_SESSION['user_id'])) {
    die('Devi fare il login');
}

$user_id = $_GET['id'] ?? 0;

if ($user_id > 0) {
    // Impedisci di eliminare se stesso
    if ($user_id == $_SESSION['user_id']) {
        header('Location: admin_panel.php?error=cannot_delete_self');
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header('Location: admin_panel.php?success=user_deleted');
    } else {
        header('Location: admin_panel.php?error=deletion_failed');
    }

    $stmt->close();
} else {
    header('Location: admin_panel.php?error=invalid_id');
}

exit;
?>
