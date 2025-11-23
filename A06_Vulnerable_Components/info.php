<?php
/**
 * Pagina informazioni sistema
 * OWASP A06: Vulnerable and Outdated Components
 * 
 * VULNERABILITÀ CRITICA: Espone tutte le versioni dei componenti
 * Questo permette agli attaccanti di identificare vulnerabilità note
 */

session_start();
require_once 'db_connect.php';
require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="blog.php">📝 Blog</a>
        <a href="info.php">ℹ️ Info Sistema</a>
    </div>
</div>

<h1>ℹ️ Informazioni Sistema</h1>

<div class="alert alert-danger">
    <strong>🚨 VULNERABILITÀ CRITICA:</strong> Questa pagina espone informazioni dettagliate sui componenti del sistema.
    Gli attaccanti possono usare queste informazioni per identificare vulnerabilità note e sfruttarle.
</div>

<div class="card">
    <h2>📦 Componenti Installati</h2>
    
    <table>
        <thead>
            <tr>
                <th>Componente</th>
                <th>Versione</th>
                <th>Stato</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>PHP</strong></td>
                <td><?php echo phpversion(); ?></td>
                <td><span style="color: red;">⚠️ OBSOLETO</span></td>
                <td>PHP 7.4 è EOL dal 28 Nov 2022. Contiene vulnerabilità note non patchate.</td>
            </tr>
            <tr>
                <td><strong>MySQL</strong></td>
                <td><?php echo isset($mysql_version) ? $mysql_version : 'Unknown'; ?></td>
                <td><span style="color: red;">⚠️ OBSOLETO</span></td>
                <td>MySQL 5.7 non è più supportato. Contiene vulnerabilità note.</td>
            </tr>
            <tr>
                <td><strong>Apache</strong></td>
                <td><?php echo apache_get_version(); ?></td>
                <td><span style="color: orange;">⚠️ DA VERIFICARE</span></td>
                <td>Verificare se la versione contiene vulnerabilità note.</td>
            </tr>
            <tr>
                <td><strong>jQuery</strong></td>
                <td>1.7.2</td>
                <td><span style="color: red;">🚨 VULNERABILE</span></td>
                <td><strong>CVE-2011-4969</strong> - Vulnerabile a XSS. Versione obsoleta.</td>
            </tr>
            <tr>
                <td><strong>Bootstrap</strong></td>
                <td>3.4.1</td>
                <td><span style="color: orange;">⚠️ OBSOLETO</span></td>
                <td>Bootstrap 3.x non è più supportato. Usare Bootstrap 5.x.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>🔍 Informazioni PHP Dettagliate</h2>
    <div class="vulnerability-warning">
        <strong>⚠️ VULNERABILITÀ:</strong> Le seguenti informazioni non dovrebbero essere esposte in produzione!
    </div>
    
    <table>
        <tr>
            <th style="width: 200px;">Informazione</th>
            <th>Valore</th>
        </tr>
        <tr>
            <td><strong>Versione PHP</strong></td>
            <td><?php echo phpversion(); ?></td>
        </tr>
        <tr>
            <td><strong>SAPI</strong></td>
            <td><?php echo php_sapi_name(); ?></td>
        </tr>
        <tr>
            <td><strong>Server Software</strong></td>
            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
        </tr>
        <tr>
            <td><strong>Server Signature</strong></td>
            <td><?php echo apache_get_version(); ?></td>
        </tr>
        <tr>
            <td><strong>Loaded Extensions</strong></td>
            <td><?php echo implode(', ', get_loaded_extensions()); ?></td>
        </tr>
        <tr>
            <td><strong>PHP Configuration (parziale)</strong></td>
            <td>
                <pre style="font-size: 0.8em; max-height: 200px; overflow: auto;"><?php 
                // VULNERABILITÀ: Espone informazioni di configurazione PHP
                echo "PHP Version: " . phpversion() . "\n";
                echo "SAPI: " . php_sapi_name() . "\n";
                echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
                echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
                echo "Loaded Extensions: " . count(get_loaded_extensions()) . "\n";
                echo "\nEstensioni caricate:\n";
                foreach (get_loaded_extensions() as $ext) {
                    echo "- " . $ext . "\n";
                }
                ?></pre>
            </td>
        </tr>
    </table>
</div>

<div class="card">
    <h2>📋 CVE e Vulnerabilità Note</h2>
    <div class="vulnerability-warning">
        <strong>⚠️ VULNERABILITÀ IDENTIFICATE:</strong>
    </div>
    <ul style="line-height: 2;">
        <li><strong>CVE-2011-4969</strong> - jQuery 1.7.2: Vulnerabile a XSS quando usa .html() o .append() con input non sanitizzato</li>
        <li><strong>PHP 7.4</strong> - End of Life: Non riceve più aggiornamenti di sicurezza</li>
        <li><strong>MySQL 5.7</strong> - End of Life: Non riceve più aggiornamenti di sicurezza</li>
    </ul>
</div>

<div class="card">
    <h2>🛠️ Come Sfruttare le Vulnerabilità</h2>
    <p><strong>1. XSS con jQuery 1.7.2:</strong></p>
    <ul style="margin-left: 20px; margin-bottom: 20px;">
        <li>Aggiungi un commento con: <code>&lt;img src=x onerror="alert('XSS')"&gt;</code></li>
        <li>Se il sito usa jQuery .html() per visualizzare i commenti, il codice verrà eseguito</li>
        <li>jQuery 1.7.2 non sanitizza correttamente l'input in alcuni casi</li>
    </ul>
    
    <p><strong>2. Identificare Vulnerabilità:</strong></p>
    <ul style="margin-left: 20px; margin-bottom: 20px;">
        <li>Usa le informazioni di versione esposte per cercare CVE specifici</li>
        <li>Cerca su <a href="https://cve.mitre.org" target="_blank">CVE Database</a> le vulnerabilità note</li>
        <li>Usa strumenti come OWASP Dependency-Check</li>
    </ul>
    
    <p><strong>3. Sfruttare PHP 7.4 Obsoleto:</strong></p>
    <ul style="margin-left: 20px;">
        <li>Cerca vulnerabilità note per PHP 7.4</li>
        <li>Verifica funzioni deprecate che potrebbero essere vulnerabili</li>
        <li>Usa exploit noti per versioni specifiche</li>
    </ul>
</div>

<?php
require_once 'footer.php';
?>

