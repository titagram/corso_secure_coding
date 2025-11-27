<?php
/**
 * Pannello Amministrazione
 * OWASP A05: Security Misconfiguration
 * 
 * VULNERABILITÀ CRITICA: Pagina admin accessibile pubblicamente!
 * Nessun controllo di autenticazione o autorizzazione
 * Nessuna restrizione IP
 */

session_start();
require_once '../db_connect.php';

// VULNERABILITÀ: Nessun controllo se l'utente è admin!
// VULNERABILITÀ: Nessun controllo se l'utente è loggato!
// In un'app reale, dovrebbe verificare:
// - if (!isset($_SESSION['user_id'])) { die('Accesso negato'); }
// - if ($_SESSION['role'] !== 'admin') { die('Accesso negato'); }

// Recupera tutti gli utenti
$users = $conn->query("SELECT * FROM users ORDER BY id");

// Recupera tutti i documenti
$documents = $conn->query("SELECT d.*, u.username, u.email FROM documents d JOIN users u ON d.user_id = u.id ORDER BY d.uploaded_at DESC");

require_once '../header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="../index.php">🏠 Home</a>
        <a href="../dashboard.php">📊 Dashboard</a>
    </div>
    <div class="user-info">
        <a href="../logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>⚙️ Pannello di Amministrazione</h1>

<div class="alert alert-danger">
    <strong>🚨 VULNERABILITÀ CRITICA:</strong> Questa pagina è accessibile pubblicamente senza autenticazione!
    Chiunque può accedere a dati sensibili di tutti gli utenti.
</div>

<div class="card">
    <h2>👥 Utenti Registrati</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Nome Completo</th>
                <th>Ruolo</th>
                <th>Data Registrazione</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><strong><?php echo htmlspecialchars($user['role']); ?></strong></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>📁 Tutti i Documenti</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome File</th>
                <th>Utente</th>
                <th>Email Utente</th>
                <th>Dimensione</th>
                <th>Privato</th>
                <th>Data Upload</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($doc = $documents->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $doc['id']; ?></td>
                    <td><?php echo htmlspecialchars($doc['original_filename']); ?></td>
                    <td><?php echo htmlspecialchars($doc['username']); ?></td>
                    <td><?php echo htmlspecialchars($doc['email']); ?></td>
                    <td><?php echo number_format($doc['file_size'] / 1024, 2); ?> KB</td>
                    <td><?php echo $doc['is_private'] ? '🔒 Sì' : '🌐 No'; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="vulnerability-warning">
    <strong>⚠️ VULNERABILITÀ:</strong> Questa pagina espone:
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>Dati personali di tutti gli utenti (email, nomi)</li>
        <li>Lista completa dei documenti caricati</li>
        <li>Informazioni su documenti privati</li>
        <li>Nessuna protezione contro accesso non autorizzato</li>
    </ul>
</div>

<?php
require_once '../footer.php';
?>

