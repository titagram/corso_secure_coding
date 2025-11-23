<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'PluginLoader.php';
require_once 'header.php';

$page_title = 'Aggiorna Plugin';

// VULNERABILITÀ CRITICA: Aggiornamento senza verifica di integrità
// Il sistema scarica e installa aggiornamenti senza verificare:
// 1. Autenticità della sorgente
// 2. Integrità del file (hash/SHA256)
// 3. Firma digitale
// 4. Contenuto del file

$plugin_id = $_GET['plugin_id'] ?? null;
$update_id = $_GET['update_id'] ?? null;

if (!$plugin_id || !$update_id) {
    header('Location: plugins.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM plugins WHERE id = ?");
$stmt->execute([$plugin_id]);
$plugin = $stmt->fetch(PDO::FETCH_ASSOC);

$update_stmt = $conn->prepare("SELECT * FROM plugin_updates WHERE id = ? AND plugin_id = ?");
$update_stmt->execute([$update_id, $plugin_id]);
$update = $update_stmt->fetch(PDO::FETCH_ASSOC);

if (!$plugin || !$update) {
    die("Plugin o aggiornamento non trovato");
}

if (isset($_POST['update'])) {
    // VULNERABILITÀ CRITICA: Download da URL non verificato
    $update_url = $update['update_url'];
    
    // VULNERABILITÀ: Scarica file senza verificare:
    // 1. Autenticità della sorgente (HTTPS, certificato)
    // 2. Integrità del file (hash)
    // 3. Firma digitale
    // 4. Contenuto del file
    
    $update_file = file_get_contents($update_url);  // VULNERABILITÀ: Download senza verifica
    
    if ($update_file !== false) {
        // VULNERABILITÀ: Salva file senza verificare hash
        $new_file_path = '/var/www/html/uploads/' . $plugin['name'] . '_v' . $update['version'] . '.php';
        file_put_contents($new_file_path, $update_file);
        
        // VULNERABILITÀ: Hash presente ma non verificato
        if (!empty($update['hash'])) {
            $calculated_hash = md5_file($new_file_path);
            // VULNERABILITÀ: Confronta hash ma non blocca se diverso
            if ($calculated_hash !== $update['hash']) {
                // VULNERABILITÀ: Avvisa ma continua comunque
                $warning = "⚠️ Hash non corrispondente, ma l'aggiornamento continua...";
            }
        }
        
        // VULNERABILITÀ: Aggiorna plugin senza verificare integrità
        $update_stmt = $conn->prepare("UPDATE plugins SET version = ?, file_path = ? WHERE id = ?");
        $update_stmt->execute([$update['version'], $new_file_path, $plugin_id]);
        
        $success = "Plugin aggiornato con successo!";
    } else {
        $error = "Errore durante il download dell'aggiornamento";
    }
}
?>

<h1>Aggiorna Plugin: <?php echo htmlspecialchars($plugin['name']); ?></h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if (isset($warning)): ?>
    <div class="alert alert-warning"><?php echo htmlspecialchars($warning); ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="update-details">
    <h3>Dettagli Aggiornamento</h3>
    <p><strong>Versione Corrente:</strong> <?php echo htmlspecialchars($plugin['version']); ?></p>
    <p><strong>Nuova Versione:</strong> <?php echo htmlspecialchars($update['version']); ?></p>
    <p><strong>URL Aggiornamento:</strong> <code><?php echo htmlspecialchars($update['update_url']); ?></code></p>
    <p><strong>Changelog:</strong> <?php echo htmlspecialchars($update['changelog']); ?></p>
    
    <!-- VULNERABILITÀ: Espone hash (non verificato) -->
    <?php if (!empty($update['hash'])): ?>
        <p><strong>Hash (non verificato):</strong> <code><?php echo htmlspecialchars($update['hash']); ?></code></p>
    <?php endif; ?>
</div>

<form method="POST" action="">
    <input type="hidden" name="update" value="1">
    <button type="submit" class="btn btn-warning">Aggiorna Plugin</button>
    <a href="plugins.php" class="btn btn-secondary">Annulla</a>
</form>

<div class="vulnerability-warning" style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
    <h4>⚠️ VULNERABILITÀ CRITICA:</h4>
    <ul>
        <li><strong>Download da URL non verificato:</strong> Il sistema scarica file da URL senza verificare autenticità</li>
        <li><strong>Verifica hash mancante:</strong> Hash presente ma non verificato prima dell'installazione</li>
        <li><strong>Verifica firma mancante:</strong> Nessuna verifica di firma digitale</li>
        <li><strong>Possibile Man-in-the-Middle:</strong> Un attaccante potrebbe intercettare e modificare il file durante il download</li>
        <li><strong>Possibile Supply Chain Attack:</strong> Se l'URL è compromesso, verrà scaricato codice malevolo</li>
    </ul>
</div>

<?php require_once 'footer.php'; ?>

