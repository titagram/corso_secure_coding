<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
require_once 'logger.php';
require_once 'header.php';

$page_title = 'Vault - Risorse Sensibili';

// VULNERABILITÀ: Non logga accesso al vault

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// VULNERABILITÀ: Accesso a risorse senza logging completo
$resource_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? 'view';

if ($resource_id && $action === 'access') {
    $stmt = $conn->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([$resource_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resource) {
        // VULNERABILITÀ: Verifica accesso ma logga solo se riuscito
        $has_access = false;
        
        if ($role === 'admin') {
            $has_access = true;
        } elseif ($resource['access_level'] === 'public') {
            $has_access = true;
        } elseif ($resource['owner_id'] == $user_id) {
            $has_access = true;
        } elseif ($resource['access_level'] === 'restricted' && $role !== 'guest') {
            $has_access = true;
        }
        
        if ($has_access) {
            // VULNERABILITÀ: Logga solo accessi riusciti, ignora tentativi non autorizzati!
            $logger->logResourceAccess($resource_id, 'view', true);
            
            // VULNERABILITÀ: Include contenuto sensibile nel log!
            $logger->log('INFO', "Resource accessed", [
                'resource_id' => $resource_id,
                'resource_name' => $resource['name'],
                'content_preview' => substr($resource['content'], 0, 50)  // VULNERABILITÀ: Espone contenuto!
            ]);
            
            $view_resource = $resource;
        } else {
            // VULNERABILITÀ CRITICA: Non logga tentativi di accesso non autorizzati!
            // Nessun audit trail per accessi negati!
            $error = "Accesso negato a questa risorsa";
        }
    }
}
?>

<h1>Vault - Risorse Sensibili</h1>

<?php if (isset($view_resource)): ?>
    <div class="resource-view">
        <h2><?php echo htmlspecialchars($view_resource['name']); ?></h2>
        <p><strong>Categoria:</strong> <?php echo htmlspecialchars($view_resource['category']); ?></p>
        <p><strong>Livello di Accesso:</strong> <?php echo htmlspecialchars($view_resource['access_level']); ?></p>
        <p><strong>Descrizione:</strong> <?php echo htmlspecialchars($view_resource['description']); ?></p>
        <div class="resource-content">
            <h3>Contenuto:</h3>
            <pre><?php echo htmlspecialchars($view_resource['content']); ?></pre>
        </div>
        <a href="vault.php" class="btn">Torna alla Lista</a>
    </div>
<?php else: ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="resources-list">
        <?php
        if ($role === 'admin') {
            $stmt = $conn->query("SELECT * FROM resources ORDER BY name");
        } else {
            $stmt = $conn->prepare("SELECT * FROM resources WHERE access_level IN ('public', 'restricted') OR owner_id = ? ORDER BY name");
            $stmt->execute([$user_id]);
        }
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <table class="resources-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Livello Accesso</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $resource): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($resource['name']); ?></td>
                        <td><?php echo htmlspecialchars($resource['category']); ?></td>
                        <td><span class="badge badge-<?php echo $resource['access_level'] === 'top_secret' ? 'danger' : ($resource['access_level'] === 'confidential' ? 'warning' : 'info'); ?>"><?php echo htmlspecialchars($resource['access_level']); ?></span></td>
                        <td>
                            <a href="vault.php?id=<?php echo $resource['id']; ?>&action=access" class="btn btn-sm">Accedi</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

