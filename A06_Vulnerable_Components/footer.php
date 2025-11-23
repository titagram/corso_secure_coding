    </div>
    <footer>
        <p>📚 Laboratorio di Secure Coding - OWASP Top 10: A06 Vulnerable and Outdated Components</p>
        <p><small>⚠️ Questa applicazione contiene componenti obsoleti e vulnerabili per scopi didattici</small></p>
        <!-- VULNERABILITÀ: Informazioni di versione esposte nel footer -->
        <p><small>PHP: <?php echo phpversion(); ?> | MySQL: <?php echo isset($mysql_version) ? $mysql_version : 'Unknown'; ?> | Apache: <?php echo apache_get_version(); ?></small></p>
    </footer>
</body>
</html>

