<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'PluginLoader.php';
require_once 'header.php';

$page_title = 'Installa Plugin';

// VULNERABILITÀ CRITICA: Deserializzazione non sicura
// I dati di configurazione vengono deserializzati senza verifica di integrità

$plugin_id = $_GET['id'] ?? null;

if (!$plugin_id) {
    header('Location: plugins.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM plugins WHERE id = ?");
$stmt->execute([$plugin_id]);
$plugin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plugin) {
    die("Plugin non trovato");
}

// VULNERABILITÀ CRITICA: Deserializzazione non sicura
// Il sistema deserializza i dati di configurazione senza verificare:
// 1. Integrità dei dati (hash/SHA256)
// 2. Firma digitale
// 3. Tipo di oggetto deserializzato
// 4. Contenuto dei dati

if (isset($_POST['install'])) {
    // VULNERABILITÀ: Deserializza dati senza validazione
    if (!empty($plugin['config_data'])) {
        // VULNERABILITÀ CRITICA: unserialize() può eseguire codice arbitrario
        // Se config_data contiene un oggetto PluginLoader o PluginConfig serializzato
        // con comandi arbitrari, verranno eseguiti durante __wakeup() o __destruct()
        $config = unserialize($plugin['config_data']);
        
        // VULNERABILITÀ: Non verifica se la deserializzazione è andata a buon fine
        // o se contiene oggetti pericolosi
    }
    
    // VULNERABILITÀ: Verifica hash presente ma non utilizzata
    if (!empty($plugin['hash'])) {
        // Hash presente ma NON verificato prima della deserializzazione
        // Un attaccante potrebbe modificare config_data e mantenere lo stesso hash
    }
    
    // VULNERABILITÀ: Verifica firma presente ma non utilizzata
    if (!empty($plugin['signature'])) {
        // Firma presente ma NON verificata
    }
    
    // VULNERABILITÀ: Carica file senza verifica di integrità
    if (!empty($plugin['file_path']) && file_exists($plugin['file_path'])) {
        // VULNERABILITÀ: Include file senza verificare hash o firma
        include $plugin['file_path'];
    }
    
    // Attiva plugin
    $update_stmt = $conn->prepare("UPDATE plugins SET is_active = 1 WHERE id = ?");
    $update_stmt->execute([$plugin_id]);
    
    $success = "Plugin installato con successo!";
}

// VULNERABILITÀ: Mostra informazioni sensibili
?>

<h1>Installa Plugin: <?php echo htmlspecialchars($plugin['name']); ?></h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="plugin-details">
    <h3>Dettagli Plugin</h3>
    <p><strong>Versione:</strong> <?php echo htmlspecialchars($plugin['version']); ?></p>
    <p><strong>Autore:</strong> <?php echo htmlspecialchars($plugin['author']); ?></p>
    <p><strong>Descrizione:</strong> <?php echo htmlspecialchars($plugin['description']); ?></p>
    
    <!-- VULNERABILITÀ: Espone dati di configurazione serializzati -->
    <?php if (!empty($plugin['config_data'])): ?>
        <p><strong>Configurazione (serializzata):</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;"><?php echo htmlspecialchars($plugin['config_data']); ?></pre>
    <?php endif; ?>
    
    <!-- VULNERABILITÀ: Espone hash (non utilizzato per verifica) -->
    <?php if (!empty($plugin['hash'])): ?>
        <p><strong>Hash (non verificato):</strong> <code><?php echo htmlspecialchars($plugin['hash']); ?></code></p>
    <?php endif; ?>
    
    <!-- VULNERABILITÀ: Espone firma (non verificata) -->
    <?php if (!empty($plugin['signature'])): ?>
        <p><strong>Firma (non verificata):</strong> <code><?php echo htmlspecialchars($plugin['signature']); ?></code></p>
    <?php endif; ?>
</div>

<form method="POST" action="">
    <input type="hidden" name="install" value="1">
    <button type="submit" class="btn">Installa Plugin</button>
    <a href="plugins.php" class="btn btn-secondary">Annulla</a>
</form>

<div class="vulnerability-warning" style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
    <h4>⚠️ VULNERABILITÀ PRESENTE:</h4>
    <ul>
        <li><strong>Deserializzazione non sicura:</strong> I dati di configurazione vengono deserializzati senza verifica di integrità</li>
        <li><strong>Verifica hash mancante:</strong> L'hash è presente ma non viene verificato prima della deserializzazione</li>
        <li><strong>Verifica firma mancante:</strong> La firma è presente ma non viene verificata</li>
        <li><strong>Caricamento file non verificato:</strong> I file plugin vengono caricati senza verificare hash o firma</li>
    </ul>
</div>

<?php require_once 'footer.php'; ?>

