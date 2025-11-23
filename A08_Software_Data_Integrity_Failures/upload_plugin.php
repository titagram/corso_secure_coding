<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'header.php';

$page_title = 'Carica Plugin';

// VULNERABILITÀ: Upload senza controlli adeguati
// 1. Verifica tipo file mancante
// 2. Verifica dimensione file mancante
// 3. Verifica integrità mancante (hash/firma)
// 4. Nome file non sanitizzato

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['plugin_file'])) {
    $name = $_POST['name'] ?? '';
    $version = $_POST['version'] ?? '';
    $description = $_POST['description'] ?? '';
    $author = $_POST['author'] ?? '';
    $download_url = $_POST['download_url'] ?? '';
    $config_data = $_POST['config_data'] ?? '';
    
    // VULNERABILITÀ: Upload senza validazione tipo file
    $upload_dir = '/var/www/html/uploads/';
    $file = $_FILES['plugin_file'];
    
    // VULNERABILITÀ: Nome file non sanitizzato (possibile path traversal)
    $file_name = $file['name'];
    $file_path = $upload_dir . $file_name;
    
    // VULNERABILITÀ: Sposta file senza verificare tipo, dimensione o integrità
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // VULNERABILITÀ: Genera hash ma non lo verifica mai
        $hash = md5_file($file_path);  // VULNERABILITÀ: MD5 è debole, ma comunque non verificato
        
        // VULNERABILITÀ: Firma fittizia (non verificata)
        $signature = 'signature_' . $name . '_' . $version;
        
        // VULNERABILITÀ: Salva configurazione serializzata senza validazione
        // Un attaccante potrebbe inserire dati serializzati malevoli
        $stmt = $conn->prepare("INSERT INTO plugins (name, version, description, author, file_path, config_data, hash, signature, download_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $version, $description, $author, $file_path, $config_data, $hash, $signature, $download_url]);
        
        $success = "Plugin caricato con successo!";
    } else {
        $error = "Errore durante il caricamento del file";
    }
}
?>

<h1>Carica Nuovo Plugin</h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="plugin-form">
    <div class="form-group">
        <label for="name">Nome Plugin *</label>
        <input type="text" id="name" name="name" required>
        <small>⚠️ VULNERABILITÀ: Dependency confusion possibile con nomi simili a plugin legittimi</small>
    </div>
    
    <div class="form-group">
        <label for="version">Versione *</label>
        <input type="text" id="version" name="version" required placeholder="es. 1.0.0">
    </div>
    
    <div class="form-group">
        <label for="description">Descrizione</label>
        <textarea id="description" name="description" rows="3"></textarea>
    </div>
    
    <div class="form-group">
        <label for="author">Autore</label>
        <input type="text" id="author" name="author">
    </div>
    
    <div class="form-group">
        <label for="plugin_file">File Plugin *</label>
        <input type="file" id="plugin_file" name="plugin_file" required>
        <small>⚠️ VULNERABILITÀ: Upload senza verifica tipo file, dimensione o integrità</small>
    </div>
    
    <div class="form-group">
        <label for="download_url">URL Download (opzionale)</label>
        <input type="url" id="download_url" name="download_url" placeholder="http://example.com/plugin.php">
        <small>⚠️ VULNERABILITÀ: URL non verificato, possibile download da sorgenti non attendibili</small>
    </div>
    
    <div class="form-group">
        <label for="config_data">Configurazione (serializzata)</label>
        <textarea id="config_data" name="config_data" rows="5" placeholder='a:2:{s:4:"key";s:5:"value";}'></textarea>
        <small>⚠️ VULNERABILITÀ CRITICA: Dati serializzati senza validazione - possibile deserializzazione non sicura</small>
    </div>
    
    <button type="submit" class="btn">Carica Plugin</button>
    <a href="plugins.php" class="btn btn-secondary">Annulla</a>
</form>

<div class="vulnerability-warning" style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
    <h4>⚠️ VULNERABILITÀ PRESENTI:</h4>
    <ul>
        <li><strong>Upload senza controlli:</strong> Nessuna verifica di tipo file, dimensione o contenuto</li>
        <li><strong>Verifica integrità mancante:</strong> Hash generato ma mai verificato</li>
        <li><strong>Firma non verificata:</strong> Firma fittizia, mai verificata</li>
        <li><strong>Dependency confusion:</strong> Possibile caricare plugin con nomi simili a quelli legittimi</li>
        <li><strong>Configurazione serializzata non validata:</strong> Possibile inserire dati serializzati malevoli</li>
    </ul>
</div>

<?php require_once 'footer.php'; ?>

