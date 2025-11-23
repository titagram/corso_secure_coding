<?php
/**
 * Script di diagnostica di rete - VULNERABILE A COMMAND INJECTION
 * Laboratorio OWASP A03: Injection
 *
 * VULNERABILITA': Questo script implementa una blacklist incompleta
 * che non blocca tutti i caratteri pericolosi, permettendo Command Injection
 */
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostica di Rete - OWASP A03 Lab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🌐 Diagnostica di Rete</h1>
            <a href="index.php" class="back-link">← Torna al Pannello</a>
        </header>

        <?php
        $reveal_vulnerability = false;
        $force_reveal = isset($_GET['show_vuln']) && $_GET['show_vuln'] === '1';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ip_address'])) {
            $ip = $_POST['ip_address'];

            echo "<div class='search-info'>";
            echo "<p><strong>Target:</strong> " . htmlspecialchars($ip) . "</p>";
            echo "</div>";

            // ============================================
            // TENTATIVO DI SANITIZZAZIONE (FALLIMENTARE!)
            // ============================================
            // La blacklist blocca alcuni caratteri comuni, ma NON è completa
            // Mancano i backtick (`) e le sostituzioni di comando $()

            echo "<div class='debug-info'>";
            echo "<strong>⚠️ Controlli di sicurezza applicati:</strong><br>";
            echo "<code>Blacklist: ; | && ||</code>";
            echo "</div>";

            $blacklist = [';', '|', '&&', '||'];
            $ip_pulito = str_replace($blacklist, '', $ip);

            // Verifica se l'input è stato modificato dalla blacklist
            if ($ip_pulito !== $ip) {
                $reveal_vulnerability = true;
                echo "<div class='error'>";
                echo "<strong>🚫 ATTACCO RILEVATO!</strong>";
                echo "<p>Sono stati rilevati caratteri non permessi nell'input.</p>";
                echo "<p>Input originale: <code>" . htmlspecialchars($ip) . "</code></p>";
                echo "<p>Input filtrato: <code>" . htmlspecialchars($ip_pulito) . "</code></p>";
                echo "</div>";

            } else {
                if (preg_match('/(\$\(|`|&|>)/', $ip)) {
                    $reveal_vulnerability = true;
                }
                // Input apparentemente "pulito" - procediamo con l'esecuzione
                echo "<div class='success'>";
                echo "<p>✅ Input validato dalla blacklist</p>";
                echo "</div>";

                echo "<div class='debug-info'>";
                echo "<strong>Comando eseguito:</strong><br>";
                echo "<code>ping -c 3 " . htmlspecialchars($ip_pulito) . "</code>";
                echo "</div>";

                echo "<div class='results'>";
                echo "<h3>Risultati del Ping:</h3>";
                echo "<pre>";

                // ============================================
                // VULNERABILITA': COMMAND INJECTION
                // ============================================
                // Anche se passiamo la blacklist, il comando è ancora vulnerabile!
                // Caratteri come $() e backtick (`) permettono command substitution

                // Esecuzione del comando VULNERABILE
                // 2>&1 reindirizza stderr su stdout per vedere anche gli errori
                system("ping -c 3 " . $ip_pulito . " 2>&1");

                echo "</pre>";
                echo "</div>";
            }

        } else {
            echo "<div class='error'>";
            echo "<p>Nessun indirizzo IP fornito.</p>";
            echo "</div>";
        }
        ?>

        <?php if ($reveal_vulnerability || $force_reveal): ?>
        <div class="vulnerability-explanation">
            <h3>🛡️ Spiegazione della Vulnerabilità</h3>
            <p><strong>Tipo:</strong> Command Injection (OWASP A03:2021)</p>

            <h4>Codice vulnerabile:</h4>
            <pre><code>// Blacklist incompleta
$blacklist = [';', '|', '&&', '||'];
$ip_pulito = str_replace($blacklist, '', $ip);

// Esecuzione diretta del comando
system("ping -c 3 " . $ip_pulito);</code></pre>

            <h4>Perché la blacklist fallisce:</h4>
            <ul>
                <li><strong>Blacklist incompleta:</strong> Blocca solo alcuni caratteri (<code>; | && ||</code>)</li>
                <li><strong>Command substitution:</strong> Non blocca <code>$(comando)</code> e backtick <code>`comando`</code></li>
                <li><strong>Approccio errato:</strong> Le blacklist sono sempre incomplete</li>
            </ul>

            <h4>Esempi di attacco:</h4>
            <ul>
                <li><strong>Usando & (singolo) per eseguire in background:</strong> <code>8.8.8.8 & whoami</code></li>
                <li><strong>Eseguire id:</strong> <code>8.8.8.8 & id</code></li>
                <li><strong>Esfiltrare dati:</strong> <code>8.8.8.8 & cat /etc/passwd</code></li>
                <li><strong>Listare directory:</strong> <code>8.8.8.8 & ls -la</code></li>
                <li><strong>Command substitution con output su stderr:</strong> <code>8.8.8.8$(whoami >&2)</code></li>
                <li><strong>Backtick alternativo:</strong> <code>8.8.8.8`whoami >&2`</code></li>
            </ul>

            <h4>Come correggere:</h4>
            <pre><code>// 1. WHITELIST invece di blacklist
if (!preg_match('/^[0-9a-zA-Z.-]+$/', $ip)) {
    die("Formato IP non valido");
}

// 2. Usare escapeshellarg()
$ip_sicuro = escapeshellarg($ip);
system("ping -c 3 " . $ip_sicuro);

// 3. MEGLIO: Usare funzioni native PHP invece di shell
// exec() con array di parametri separati</code></pre>

            <h4>Differenza tra blacklist e whitelist:</h4>
            <table>
                <thead>
                    <tr>
                        <th>Blacklist (❌ NON sicuro)</th>
                        <th>Whitelist (✅ Sicuro)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Blocca caratteri "cattivi" noti</td>
                        <td>Permette SOLO caratteri validi</td>
                    </tr>
                    <tr>
                        <td>Sempre incompleta</td>
                        <td>Default-deny approach</td>
                    </tr>
                    <tr>
                        <td>Facilmente bypassabile</td>
                        <td>Molto più sicura</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <footer>
            <p>📚 Laboratorio di Secure Coding - OWASP Top 10: A03 Injection</p>
        </footer>
    </div>
</body>
</html>
