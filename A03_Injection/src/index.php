<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Utility Aziendale - OWASP A03 Lab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Pannello Utility Aziendale</h1>
            <p class="subtitle">Laboratorio OWASP A03: Injection Vulnerabilities</p>
        </header>

        <div class="forms-container">
            <!-- Form 1: Product Search (Vulnerabile a SQL Injection) -->
            <div class="form-section">
                <h2>🔍 Ricerca Prodotti</h2>
                <p class="description">Cerca prodotti nel catalogo aziendale</p>

                <form action="search.php" method="GET" class="utility-form">
                    <div class="form-group">
                        <label for="search_term">Nome del prodotto:</label>
                        <input
                            type="text"
                            id="search_term"
                            name="search_term"
                            placeholder="Es: Laptop, Mouse, Keyboard..."
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-primary">Cerca Prodotto</button>
                </form>

            </div>

            <!-- Form 2: Network Diagnostics (Vulnerabile a Command Injection) -->
            <div class="form-section">
                <h2>🌐 Diagnostica di Rete</h2>
                <p class="description">Esegui un ping verso un indirizzo IP o hostname</p>

                <form action="ping.php" method="POST" class="utility-form">
                    <div class="form-group">
                        <label for="ip_address">Indirizzo IP o Hostname:</label>
                        <input
                            type="text"
                            id="ip_address"
                            name="ip_address"
                            placeholder="Es: 8.8.8.8, localhost, google.com"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-secondary">Esegui Ping</button>
                </form>

            </div>
        </div>

        <footer>
            <p>📚 Laboratorio di Secure Coding - OWASP Top 10: A03 Injection</p>
        </footer>
    </div>
</body>
</html>
