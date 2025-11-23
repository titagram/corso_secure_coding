<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'url_fetcher.php';
require_once 'header.php';

$page_title = 'Crea Preview URL';

$preview = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'] ?? '';
    $method = $_POST['method'] ?? 'file_get_contents';
    
    if (empty($url)) {
        $error = "Inserisci un URL";
    } else {
        // VULNERABILITÀ CRITICA: Nessuna validazione dell'URL!
        // L'URL viene passato direttamente alle funzioni di fetch
        
        // VULNERABILITÀ: Filtro debole che può essere bypassato
        if (!URLFetcher::isURLAllowed($url)) {
            $error = "URL non consentito (filtro base)";
        } else {
            // VULNERABILITÀ: Fetch senza validazione
            if ($method === 'curl') {
                $result = URLFetcher::fetchWithCurl($url);
            } else {
                $result = URLFetcher::fetch($url);
            }
            
            if ($result['success']) {
                // VULNERABILITÀ: Estrae metadati senza sanitizzazione
                $metadata = URLFetcher::extractMetadata($result['content'], $url);
                
                $preview = [
                    'url' => $url,
                    'title' => $metadata['title'] ?: 'Nessun titolo',
                    'description' => $metadata['description'] ?: 'Nessuna descrizione',
                    'image' => $metadata['image'],
                    'content_preview' => substr(strip_tags($result['content']), 0, 500),
                    'full_content' => $result['content'],
                    'response_time' => $result['response_time'],
                    'http_code' => $result['http_code'] ?? 200
                ];
                
                // Salva nel database
                $stmt = $conn->prepare("INSERT INTO url_previews (user_id, url, title, description, image_url, content_preview, status) VALUES (?, ?, ?, ?, ?, ?, 'success')");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $url,
                    $preview['title'],
                    $preview['description'],
                    $preview['image'],
                    $preview['content_preview']
                ]);
                
                // Salva nella storia
                $stmt = $conn->prepare("INSERT INTO url_history (user_id, url, method, response_code, response_time) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $url,
                    $method,
                    $preview['http_code'],
                    $preview['response_time']
                ]);
            } else {
                $error = $result['error'] ?? 'Errore durante il fetch dell\'URL';
                
                // Salva fallimento
                $stmt = $conn->prepare("INSERT INTO url_previews (user_id, url, status) VALUES (?, ?, 'failed')");
                $stmt->execute([$_SESSION['user_id'], $url]);
            }
        }
    }
}
?>

<h1>Crea Preview URL</h1>

<div class="vulnerability-warning">
    <h3>⚠️ VULNERABILITÀ SSRF PRESENTE:</h3>
    <ul>
        <li><strong>Nessuna validazione URL:</strong> Puoi inserire qualsiasi URL, inclusi localhost, IP privati, metadata services</li>
        <li><strong>Filtro debole:</strong> Il filtro può essere bypassato facilmente con encoding, IPv6, decimal IP, etc.</li>
        <li><strong>Accesso a risorse interne:</strong> Possibile accedere a servizi interni (database, Redis, API)</li>
        <li><strong>Port scanning:</strong> Possibile scansionare porte interne</li>
    </ul>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="" class="preview-form">
    <div class="form-group">
        <label for="url">URL da processare *</label>
        <input type="text" id="url" name="url" placeholder="http://example.com" required>
        <small>⚠️ VULNERABILITÀ: Puoi inserire qualsiasi URL, incluso localhost, IP privati, metadata services!</small>
    </div>
    
    <div class="form-group">
        <label for="method">Metodo di Fetch</label>
        <select id="method" name="method">
            <option value="file_get_contents">file_get_contents (default)</option>
            <option value="curl">cURL</option>
        </select>
        <small>⚠️ VULNERABILITÀ: Entrambi i metodi sono vulnerabili a SSRF!</small>
    </div>
    
    <button type="submit" class="btn">Crea Preview</button>
</form>

<?php if ($preview): ?>
    <div class="preview-result">
        <h2>Preview Generata</h2>
        <div class="preview-card">
            <?php if ($preview['image']): ?>
                <img src="<?php echo htmlspecialchars($preview['image']); ?>" alt="Preview" class="preview-image">
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($preview['title']); ?></h3>
            <p class="preview-url"><strong>URL:</strong> <code><?php echo htmlspecialchars($preview['url']); ?></code></p>
            <p class="preview-description"><?php echo htmlspecialchars($preview['description']); ?></p>
            <p class="preview-meta">
                <strong>Response Code:</strong> <?php echo $preview['http_code']; ?> | 
                <strong>Response Time:</strong> <?php echo $preview['response_time']; ?>ms
            </p>
        </div>
        
        <div class="preview-content">
            <h3>Contenuto Completo (primi 500 caratteri)</h3>
            <pre><?php echo htmlspecialchars($preview['content_preview']); ?></pre>
        </div>
        
        <div class="preview-full-content" style="margin-top: 20px;">
            <h3>Contenuto Completo (Raw)</h3>
            <details>
                <summary>Clicca per espandere (può contenere dati sensibili)</summary>
                <pre style="max-height: 400px; overflow-y: auto;"><?php echo htmlspecialchars($preview['full_content']); ?></pre>
            </details>
        </div>
    </div>
<?php endif; ?>

<div class="ssrf-examples" style="margin-top: 40px;">
    <h2>Esempi di Payload SSRF</h2>
    <div class="examples-grid">
        <div class="example-card">
            <h4>Accesso Localhost</h4>
            <code>http://localhost</code>
            <code>http://127.0.0.1</code>
            <code>http://127.1</code>
        </div>
        <div class="example-card">
            <h4>IP Privati</h4>
            <code>http://192.168.1.1</code>
            <code>http://10.0.0.1</code>
            <code>http://172.16.0.1</code>
        </div>
        <div class="example-card">
            <h4>Metadata Services</h4>
            <code>http://169.254.169.254/latest/meta-data/</code>
            <code>http://metadata.google.internal/</code>
        </div>
        <div class="example-card">
            <h4>Port Scanning</h4>
            <code>http://127.0.0.1:3306</code>
            <code>http://127.0.0.1:6379</code>
            <code>http://127.0.0.1:8080</code>
        </div>
        <div class="example-card">
            <h4>Bypass Filtri</h4>
            <code>http://127.0.0.%31</code>
            <code>http://[::1]</code>
            <code>http://2130706433</code>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

