<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Recupera documenti dell'utente
$stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$documents = $stmt->get_result();

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="files.php">📁 Documenti</a>
        <a href="upload.php">📤 Carica</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin/panel.php">⚙️ Admin</a>
        <?php endif; ?>
    </div>
    <div class="user-info">
        Ciao, <?php echo htmlspecialchars($_SESSION['full_name']); ?> | 
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>📊 Dashboard</h1>

<div class="card">
    <h2>Le Tue Statistiche</h2>
    <p><strong>Documenti caricati:</strong> <?php echo $documents->num_rows; ?></p>
    <p><strong>Ruolo:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></p>
</div>

<div class="card">
    <h2>📁 I Tuoi Documenti Recenti</h2>
    <?php if ($documents->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nome File</th>
                    <th>Tipo</th>
                    <th>Dimensione</th>
                    <th>Data Upload</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($doc = $documents->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doc['original_filename']); ?></td>
                        <td><?php echo htmlspecialchars($doc['file_type']); ?></td>
                        <td><?php echo number_format($doc['file_size'] / 1024, 2); ?> KB</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                        <td>
                            <a href="download.php?id=<?php echo $doc['id']; ?>" class="btn">Scarica</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nessun documento caricato. <a href="upload.php">Carica il tuo primo documento</a></p>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>

