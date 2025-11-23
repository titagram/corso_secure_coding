<?php
/**
 * VULNERABILITÀ: Directory listing esposto!
 * Questo file mostra il contenuto della directory uploads
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Directory Listing - uploads</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #d32f2f; }
        ul { list-style: none; padding: 0; }
        li { padding: 5px; }
        a { text-decoration: none; color: #1976d2; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>🚨 VULNERABILITÀ: Directory Listing Abilitato</h1>
    <p><strong>Directory:</strong> /uploads/</p>
    <p style="color: red;">⚠️ Questa directory non dovrebbe essere accessibile pubblicamente!</p>
    <h2>File nella directory:</h2>
    <ul>
        <?php
        $files = scandir(__DIR__);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != 'index.php') {
                $filepath = __DIR__ . '/' . $file;
                $size = is_file($filepath) ? filesize($filepath) : 0;
                $type = is_dir($filepath) ? '📁 Directory' : '📄 File';
                echo "<li>{$type}: <a href='{$file}'>{$file}</a> (" . number_format($size) . " bytes)</li>";
            }
        }
        ?>
    </ul>
</body>
</html>

