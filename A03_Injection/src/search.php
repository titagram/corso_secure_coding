<?php
/**
 * Script di ricerca prodotti - VULNERABILE A SQL INJECTION
 * Laboratorio OWASP A03: Injection
 *
 * VULNERABILITA': Questo script concatena direttamente l'input dell'utente
 * nella query SQL senza usare prepared statements, permettendo SQL Injection
 */

require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risultati Ricerca - OWASP A03 Lab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🔍 Risultati Ricerca Prodotti</h1>
            <a href="index.php" class="back-link">← Torna al Pannello</a>
        </header>

        <?php
        $reveal_vulnerability = false;
        $force_reveal = isset($_GET['show_vuln']) && $_GET['show_vuln'] === '1';

        if (isset($_GET['search_term'])) {
            $term = $_GET['search_term'];
            if (preg_match("/['\";]|--|\\/\\*/", $term)) {
                $reveal_vulnerability = true;
            }

            echo "<div class='search-info'>";
            echo "<p><strong>Termine di ricerca:</strong> " . htmlspecialchars($term) . "</p>";
            echo "</div>";

            // ============================================
            // VULNERABILITA': SQL INJECTION
            // ============================================
            // La query viene costruita concatenando direttamente l'input dell'utente
            // SENZA usare prepared statements o escape appropriato
            // Questo permette a un attaccante di iniettare codice SQL arbitrario

            $sql = "SELECT name, description, price FROM products WHERE name LIKE '%$term%'";

            echo "<div class='debug-info'>";
            echo "<strong>⚠️ Query SQL eseguita:</strong><br>";
            echo "<code>" . htmlspecialchars($sql) . "</code>";
            echo "</div>";

            try {
                // Esecuzione della query VULNERABILE (senza prepared statement!)
                $result = $mysqli->query($sql);

                if ($result === false) {
                    $reveal_vulnerability = true;
                    echo "<div class='error'>";
                    echo "<strong>Errore SQL:</strong> " . htmlspecialchars($mysqli->error);
                    echo "</div>";
                } else {
                    if ($result->num_rows > 0) {
                        echo "<div class='results'>";
                        echo "<h2>Prodotti trovati: " . $result->num_rows . "</h2>";
                        echo "<table>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>Nome</th>";
                        echo "<th>Descrizione</th>";
                        echo "<th>Prezzo</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";

                        // Mostra tutti i risultati della query
                        // Questo è fondamentale per visualizzare i dati estratti con UNION
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                            echo "</tr>";
                        }

                        echo "</tbody>";
                        echo "</table>";
                        echo "</div>";
                    } else {
                        echo "<div class='no-results'>";
                        echo "<p>Nessun prodotto trovato per: <strong>" . htmlspecialchars($term) . "</strong></p>";
                        echo "</div>";
                    }
                }

            } catch (Exception $e) {
                echo "<div class='error'>";
                echo "<strong>Errore:</strong> " . htmlspecialchars($e->getMessage());
                echo "</div>";
            }
        } else {
            echo "<div class='error'>";
            echo "<p>Nessun termine di ricerca fornito.</p>";
            echo "</div>";
        }
        ?>

        <?php if ($reveal_vulnerability || $force_reveal): ?>
        <div class="vulnerability-explanation">
            <h3>🛡️ Spiegazione della Vulnerabilità</h3>
            <p><strong>Tipo:</strong> SQL Injection (OWASP A03:2021)</p>

            <h4>Codice vulnerabile:</h4>
            <pre><code>$term = $_GET['search_term'];
$sql = "SELECT name, description, price FROM products WHERE name LIKE '%$term%'";
$result = $mysqli->query($sql);</code></pre>

            <h4>Esempi di attacco:</h4>
            <ul>
                <li><strong>Bypass autenticazione:</strong> <code>' OR '1'='1</code></li>
                <li><strong>Estrazione dati con UNION:</strong> <code>' UNION SELECT username, password_hash, role FROM users-- -</code></li>
                <li><strong>Commenti SQL:</strong> <code>' OR 1=1-- -</code></li>
            </ul>

            <h4>Come correggere:</h4>
            <pre><code>// Usare prepared statements
$stmt = $mysqli->prepare("SELECT name, description, price FROM products WHERE name LIKE ?");
$searchTerm = "%$term%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();</code></pre>
        </div>
        <?php endif; ?>

        <footer>
            <p>📚 Laboratorio di Secure Coding - OWASP Top 10: A03 Injection</p>
        </footer>
    </div>
</body>
</html>
