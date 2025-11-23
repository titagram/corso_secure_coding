<?php
session_start();
require_once 'db_connect.php';

// Se già loggato, reindirizza alla dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // Preparazione query per evitare SQL injection
        $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifica password con password_verify
            if (password_verify($password, $user['password_hash'])) {
                // Login riuscito - salva dati in sessione
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Username o password errati';
            }
        } else {
            $error = 'Username o password errati';
        }

        $stmt->close();
    } else {
        $error = 'Compila tutti i campi';
    }
}

require_once 'header.php';
?>

<h1>Login - Sistema Gestione Fatture</h1>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div>
        <label>Username:</label>
        <input type="text" name="username" required>
    </div>
    <div>
        <label>Password:</label>
        <input type="password" name="password" required>
    </div>
    <div style="margin-top: 20px;">
        <button type="submit" class="btn">Accedi</button>
    </div>
</form>

<div class="alert alert-info" style="margin-top: 30px;">
    <strong>Utenti di test:</strong><br>
    admin / admin123<br>
    manager / manager123<br>
    alice / alice123<br>
    bob / bob123
</div>

<?php require_once 'footer.php'; ?>
