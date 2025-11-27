<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['document'];
        $original_filename = $file['name'];
        $file_size = $file['size'];
        $file_type = $file['type'];
        $description = $_POST['description'] ?? '';
        $is_private = isset($_POST['is_private']) ? 1 : 0;
        
        // VULNERABILITÀ: Validazione minima del file!
        // Dovrebbe verificare tipo, dimensione, contenuto
        
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // VULNERABILITÀ: Permessi troppo permissivi!
        }
        
        $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
        $filename = 'doc_' . uniqid() . '.' . $extension;
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $stmt = $conn->prepare("INSERT INTO documents (user_id, filename, original_filename, file_path, file_size, file_type, description, is_private) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $relative_path = '/uploads/' . $filename;
            $stmt->bind_param("isssissi", $user_id, $filename, $original_filename, $relative_path, $file_size, $file_type, $description, $is_private);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Documento caricato con successo!";
                header("Location: files.php");
                exit();
            } else {
                $_SESSION['error'] = "Errore durante il salvataggio: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Errore durante il caricamento del file.";
        }
    } else {
        $_SESSION['error'] = "Nessun file selezionato o errore nel caricamento.";
    }
}

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="files.php">📁 Documenti</a>
        <a href="upload.php">📤 Carica</a>
    </div>
    <div class="user-info">
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>📤 Carica Documento</h1>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php 
        echo htmlspecialchars($_SESSION['error']); 
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="upload.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="document">Seleziona Documento *</label>
            <input type="file" id="document" name="document" required>
            <small>⚠️ VULNERABILITÀ: Validazione minima del file!</small>
        </div>

        <div class="form-group">
            <label for="description">Descrizione</label>
            <textarea id="description" name="description" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_private" value="1">
                Documento privato
            </label>
        </div>

        <button type="submit" class="btn">Carica Documento</button>
        <a href="files.php" class="btn btn-danger">Annulla</a>
    </form>
</div>

<?php
require_once 'footer.php';
?>