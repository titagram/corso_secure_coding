# OWASP A05:2021 – Security Misconfiguration

## Descrizione del Modulo

Questo laboratorio dimostra vulnerabilità di **misconfigurazione della sicurezza** nella categoria **OWASP A05:2021 – Security Misconfiguration**. L'applicazione è un sistema di gestione documentale che presenta numerose configurazioni di sicurezza errate che espongono dati sensibili e risorse critiche.

## ⚠️ ATTENZIONE

**Questa applicazione contiene vulnerabilità di sicurezza intenzionali. NON utilizzare in produzione o in ambienti reali.**

## Scenario

Sistema di gestione documentale che presenta configurazioni di sicurezza errate:
- File di configurazione esposti pubblicamente
- Directory listing abilitato
- Pannello amministrazione accessibile senza autenticazione
- File di backup e log accessibili via web
- Debug mode attivo in produzione
- Credenziali hardcoded

## Setup

### 1. Avvio Ambiente Docker

```bash
docker-compose up -d
```

L'applicazione sarà disponibile su: `http://localhost:8005`

Il database MySQL sarà accessibile su: `localhost:3305`

### 2. Credenziali Database

- **Host:** `db` (interno Docker) o `localhost:3305` (esterno)
- **Database:** `document_system`
- **Username:** `root`
- **Password:** `rootpassword`

### 3. Account di Test

| Username | Password | Ruolo |
|----------|----------|-------|
| admin | admin123 | admin |
| mario.rossi | alice123 | user |
| luisa.verdi | manager123 | user |

## Vulnerabilità Presenti

### 1. File di Configurazione Esposti Pubblicamente

**File:** `config.php`, `environment.txt`

**Descrizione:** File di configurazione contenenti informazioni sensibili sono accessibili via web:
- Credenziali database (username, password)
- API keys e secret keys
- Chiavi di cifratura
- Password admin hardcoded
- Configurazioni applicative

**Impatto:**
- Accesso completo alle credenziali del database
- Compromissione di API keys e secret keys
- Violazione della segretezza delle configurazioni
- Bypass completo dell'autenticazione

**Come sfruttarla:**
1. Accedi direttamente a: `http://localhost:8005/config.php`
2. Oppure: `http://localhost:8005/environment.txt`
3. Leggi tutte le credenziali e chiavi sensibili

### 2. Directory Listing Abilitato

**File:** `apache/httpd.conf`, `Dockerfile`

**Descrizione:** Il directory listing è abilitato per diverse directory sensibili:
- `/uploads/` - File caricati dagli utenti
- `/backups/` - File di backup del database
- `/logs/` - File di log dell'applicazione
- `/admin/` - Pannello amministrazione

**Codice Vulnerabile:**
```apache
Options Indexes FollowSymLinks
```

**Impatto:**
- Enumerazione di tutti i file nelle directory
- Accesso diretto ai file di backup
- Accesso ai log che possono contenere informazioni sensibili
- Identificazione della struttura dell'applicazione

**Come sfruttarla:**
1. Accedi a: `http://localhost:8005/uploads/`
2. Accedi a: `http://localhost:8005/backups/`
3. Accedi a: `http://localhost:8005/logs/`
4. Accedi a: `http://localhost:8005/admin/`
5. Naviga tra i file elencati

### 3. Pannello Amministrazione Accessibile Pubblicamente

**File:** `admin/panel.php`

**Descrizione:** Il pannello amministrazione è accessibile senza autenticazione:
- Nessun controllo se l'utente è loggato
- Nessun controllo sul ruolo dell'utente
- Nessuna restrizione IP
- Espone dati di tutti gli utenti e documenti

**Codice Vulnerabile:**
```php
// VULNERABILITÀ: Nessun controllo di autenticazione!
// Nessun controllo se l'utente è admin!
$users = $conn->query("SELECT * FROM users ORDER BY id");
$documents = $conn->query("SELECT d.*, u.username, u.email FROM documents d...");
```

**Impatto:**
- Accesso non autorizzato ai dati di tutti gli utenti
- Visualizzazione di documenti privati
- Violazione massiva della privacy
- Possibilità di enumerare la struttura del database

**Come sfruttarla:**
1. Accedi direttamente a: `http://localhost:8005/admin/panel.php`
2. Visualizza tutti gli utenti registrati con email e nomi completi
3. Visualizza tutti i documenti caricati, inclusi quelli privati

### 4. File di Backup Esposti

**File:** `backup.sql`, `backup_2024-11-20.sql.bak`

**Descrizione:** File di backup del database sono accessibili pubblicamente nella document root:
- Backup completi del database in formato SQL
- Contengono strutture delle tabelle
- Contengono dati sensibili (password hash, email, documenti)

**Impatto:**
- Accesso completo al dump del database
- Estrazione di password hash da craccare
- Accesso a dati storici
- Violazione della riservatezza dei dati

**Come sfruttarla:**
1. Accedi a: `http://localhost:8005/backup.sql`
2. Oppure: `http://localhost:8005/backup_2024-11-20.sql.bak`
3. Scarica il file e analizzalo
4. Estrai password hash, email, dati personali

### 5. File di Log Accessibili

**File:** `logs/app.log`

**Descrizione:** I file di log sono accessibili pubblicamente e possono contenere:
- Informazioni di debug
- Stack trace di errori
- Dati sensibili loggati accidentalmente
- Query SQL con parametri
- Informazioni sull'infrastruttura

**Impatto:**
- Rivelazione di informazioni sensibili
- Stack trace che mostrano struttura del codice
- Informazioni utili per ulteriori attacchi
- Violazione della riservatezza

**Come sfruttarla:**
1. Accedi a: `http://localhost:8005/logs/app.log`
2. Analizza i log per informazioni sensibili
3. Cerca stack trace e query SQL

### 6. Debug Mode Attivo in Produzione

**File:** `config.php`, `Dockerfile`, `db_connect.php`

**Descrizione:** Il debug mode è attivo in produzione con:
- `display_errors = On`
- `display_startup_errors = On`
- `error_reporting = E_ALL`
- Stack trace completi esposti

**Codice Vulnerabile:**
```php
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**Impatto:**
- Esposizione di stack trace agli utenti
- Rivelazione di informazioni sul codice sorgente
- Path disclosure (percorsi file system)
- Informazioni utili per attacchi mirati

**Come sfruttarla:**
1. Genera un errore nell'applicazione (es. query SQL errata)
2. Osserva lo stack trace completo esposto
3. Estrai informazioni su percorsi, nomi di funzioni, struttura del codice

### 7. Server Signature Esposta

**File:** `Dockerfile`, `apache/httpd.conf`

**Descrizione:** Apache espone la versione completa del server:
- `ServerTokens Full` - mostra versione completa
- `ServerSignature On` - mostra versione nelle pagine di errore

**Codice Vulnerabile:**
```apache
ServerTokens Full
ServerSignature On
```

**Impatto:**
- Identificazione precisa della versione del server
- Facilitazione di attacchi mirati per vulnerabilità note
- Informazioni utili per reconnaissance

**Come sfruttarla:**
1. Invia una richiesta HTTP e controlla gli header di risposta
2. Genera una pagina di errore e osserva il footer
3. Usa strumenti come `curl -I http://localhost:8005` per vedere gli header

### 8. File .htaccess Accessibili

**File:** `Dockerfile`

**Descrizione:** I file `.htaccess` sono accessibili pubblicamente, mentre normalmente Apache li blocca di default.

**Codice Vulnerabile:**
```apache
<Files ".htaccess">
    Require all granted
</Files>
```

**Impatto:**
- Rivelazione di regole di configurazione
- Identificazione di directory protette
- Informazioni sulla struttura dell'applicazione

**Come sfruttarla:**
1. Cerca file `.htaccess` nella document root
2. Accedi direttamente: `http://localhost:8005/.htaccess`
3. Analizza le regole di configurazione

### 9. Headers di Sicurezza Mancanti

**File:** `apache/httpd.conf`

**Descrizione:** Mancano header di sicurezza fondamentali:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Strict-Transport-Security`
- `Content-Security-Policy`
- `X-XSS-Protection`

**Impatto:**
- Vulnerabilità a clickjacking
- Vulnerabilità a MIME type sniffing
- Mancanza di protezione XSS
- Mancanza di protezione MITM

### 10. Credenziali Hardcoded

**File:** `config.php`

**Descrizione:** Credenziali hardcoded nel codice sorgente:
- Password admin: `admin123`
- API keys esposte
- Secret keys nel codice

**Codice Vulnerabile:**
```php
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');
define('API_KEY', 'sk_live_1234567890abcdefghijklmnop');
define('SECRET_KEY', 'my_super_secret_key_12345_do_not_share');
```

**Impatto:**
- Accesso non autorizzato al sistema
- Impossibilità di rotazione delle credenziali
- Compromissione in caso di leak del codice sorgente

**Come sfruttarla:**
1. Leggi `config.php` direttamente via web
2. Usa le credenziali hardcoded per accedere
3. Estrai API keys e secret keys

## Esercizi Pratici

### Esercizio 1: Accedere ai File di Configurazione

1. Accedi a `http://localhost:8005/config.php`
2. Leggi tutte le credenziali esposte
3. Accedi a `http://localhost:8005/environment.txt`
4. Estrai API keys e secret keys
5. Usa le credenziali per accedere direttamente al database

### Esercizio 2: Sfruttare Directory Listing

1. Accedi a `http://localhost:8005/uploads/`
2. Elenca tutti i file caricati
3. Accedi a `http://localhost:8005/backups/`
4. Scarica i file di backup SQL
5. Accedi a `http://localhost:8005/logs/`
6. Analizza i file di log

### Esercizio 3: Accedere al Pannello Admin Senza Autenticazione

1. Accedi direttamente a `http://localhost:8005/admin/panel.php`
2. Visualizza tutti gli utenti registrati
3. Visualizza tutti i documenti, inclusi quelli privati
4. Estrai email e informazioni personali

### Esercizio 4: Analizzare File di Backup

1. Scarica `http://localhost:8005/backup.sql`
2. Analizza la struttura del database
3. Estrai password hash
4. Prova a craccare gli hash con hashcat
5. Identifica altri dati sensibili

### Esercizio 5: Analizzare Log Files

1. Accedi a `http://localhost:8005/logs/app.log`
2. Cerca informazioni sensibili
3. Identifica stack trace esposti
4. Cerca query SQL loggate
5. Cerca dati personali accidentalmente loggati

### Esercizio 6: Identificare Server Signature

```bash
# Controlla gli header HTTP
curl -I http://localhost:8005

# Genera una pagina di errore e osserva il footer
curl http://localhost:8005/nonexistent.php
```

### Esercizio 7: Identificare Debug Mode

1. Genera un errore nell'applicazione (es. inserisci caratteri speciali in una query)
2. Osserva lo stack trace completo esposto
3. Estrai informazioni su percorsi file system
4. Identifica nomi di funzioni e struttura del codice

## Automazioni e Strumenti di Enumerazione

Per identificare automaticamente le misconfigurazioni di sicurezza, è possibile utilizzare diversi strumenti di penetration testing. Segui questa scaletta per un'analisi completa:

### Step 1 – WhatWeb

**Identifica versione server e tecnologie utilizzate:**

WhatWeb è uno strumento che identifica automaticamente la versione del server web, il framework, CMS e altre tecnologie utilizzate.

```bash
whatweb http://localhost:8005
```

**Cosa cercare:**
- Versione di Apache
- Versione di PHP
- Moduli Apache abilitati
- Informazioni sul server

**Output atteso:**
- Identificazione della versione completa di Apache
- Informazioni sui moduli abilitati (mod_rewrite, mod_autoindex)
- Versione di PHP

### Step 2 – Nikto

**Trova tutte le misconfigurazioni principali:**

Nikto è uno scanner web che identifica automaticamente migliaia di vulnerabilità e misconfigurazioni comuni.

```bash
nikto -h http://localhost:8005
```

**Cosa cercare:**
- Directory listing abilitato
- File di backup esposti
- File di configurazione accessibili
- Server signature esposta
- Header di sicurezza mancanti
- File pericolosi accessibili

**Output atteso:**
- Elenco di directory con listing abilitato
- File di backup e configurazione trovati
- Server signature esposta
- Headers di sicurezza mancanti

### Step 3 – Gobuster

**Enumerazione file e directory:**

Gobuster è uno strumento di brute-forcing per identificare file e directory nascoste o non linkate.

```bash
gobuster dir -u http://localhost:8005 -w /usr/share/wordlists/dirb/common.txt
```

**Alternativa con wordlist più estesa:**
```bash
gobuster dir -u http://localhost:8005 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

**Cosa cercare:**
- Directory nascoste (`/backups/`, `/logs/`, `/admin/`)
- File di configurazione (`config.php`, `.env`)
- File di backup (`backup.sql`, `*.bak`)
- File di log (`app.log`)

**Parametri aggiuntivi utili:**
```bash
# Include estensioni comuni
gobuster dir -u http://localhost:8005 -w /usr/share/wordlists/dirb/common.txt -x php,txt,log,sql,bak

# Timeout personalizzato
gobuster dir -u http://localhost:8005 -w /usr/share/wordlists/dirb/common.txt -t 50

# Output dettagliato
gobuster dir -u http://localhost:8005 -w /usr/share/wordlists/dirb/common.txt -v
```

### Step 4 – Nmap NSE

**Enumerazione professionale con script specializzati:**

Nmap con script NSE (Nmap Scripting Engine) può eseguire scan approfonditi per identificare vulnerabilità web specifiche.

```bash
nmap -p 8005 --script http-enum localhost
```

**Script NSE utili per Security Misconfiguration:**

```bash
# Enumerazione generale HTTP
nmap -p 8005 --script http-enum localhost

# Informazioni sul server
nmap -p 8005 --script http-server-header,http-headers localhost

# Ricerca file di backup e configurazione
nmap -p 8005 --script http-config-backup localhost

# Informazioni su PHP
nmap -p 8005 --script http-php-version localhost

# Scan completo con tutti gli script HTTP
nmap -p 8005 --script "http-*" localhost
```

**Cosa cercare:**
- Directory e file enumerati
- Versioni di software
- File di configurazione esposti
- Informazioni sul server

### Step 5 – Sfruttamento Manuale

**Apri il browser e sfrutta quello che hai trovato:**

Dopo aver eseguito gli strumenti automatici, verifica manualmente le risorse identificate:

1. **File di Configurazione:**
   - `http://localhost:8005/config.php` - Leggi credenziali e chiavi
   - `http://localhost:8005/environment.txt` - Estrai variabili d'ambiente

2. **File di Backup:**
   - `http://localhost:8005/backup.sql` - Analizza dump del database
   - `http://localhost:8005/backup_2024-11-20.sql.bak` - Backup storici

3. **Pannello Admin:**
   - `http://localhost:8005/admin/panel.php` - Accesso non autorizzato ai dati

4. **File di Log:**
   - `http://localhost:8005/logs/app.log` - Analizza informazioni sensibili nei log

5. **Directory Listing:**
   - `http://localhost:8005/uploads/` - Elenca file caricati
   - `http://localhost:8005/backups/` - Visualizza backup
   - `http://localhost:8005/logs/` - Accedi ai log

**Workflow Completo:**

```bash
# 1. Identifica tecnologie
whatweb http://localhost:8005

# 2. Scannerizza per misconfigurazioni
nikto -h http://localhost:8005 > nikto_results.txt

# 3. Enumera directory e file
gobuster dir -u http://localhost:8005 -w /usr/share/wordlists/dirb/common.txt -x php,txt,log,sql,bak > gobuster_results.txt

# 4. Enumera con Nmap
nmap -p 8005 --script http-enum,http-config-backup localhost > nmap_results.txt

# 5. Analizza i risultati e verifica manualmente nel browser
```

**Nota:** Questi strumenti sono inclusi in distribuzioni come Kali Linux. Se non disponibili, installali con:

```bash
# Kali Linux / Debian
sudo apt update
sudo apt install whatweb nikto gobuster nmap

# Oppure usa Docker
docker run -it --rm kalilinux/kali-last-release bash
```

## Mitigazioni (Da Implementare)

### Per File di Configurazione:

1. **Spostare File Sensibili fuori dalla Document Root:**
   ```php
   // ✅ CORRETTO: File fuori dalla document root
   /var/www/app/config.php  (fuori da document root)
   /var/www/html/          (document root)
   ```

2. **Usare Variabili d'Ambiente:**
   ```bash
   # ✅ CORRETTO: Variabili d'ambiente in Docker
   environment:
     - DB_PASSWORD=${DB_PASSWORD}
     - API_KEY=${API_KEY}
   ```

3. **Bloccare Accesso ai File di Configurazione:**
   ```apache
   # ✅ CORRETTO: Blocca accesso a file sensibili
   <FilesMatch "\.(env|ini|conf|config|bak|sql)$">
       Require all denied
   </FilesMatch>
   ```

### Per Directory Listing:

1. **Disabilitare Directory Listing:**
   ```apache
   # ✅ CORRETTO: Disabilita directory listing
   Options -Indexes +FollowSymLinks
   ```

2. **Aggiungere File index.php vuoto:**
   ```php
   // ✅ CORRETTO: File index.php che blocca listing
   <?php
   http_response_code(403);
   die('Access Denied');
   ?>
   ```

3. **Proteggere Directory Sensibili:**
   ```apache
   # ✅ CORRETTO: Blocca accesso a directory sensibili
   <Directory "/var/www/html/backups">
       Require all denied
   </Directory>
   ```

### Per Pannello Admin:

1. **Aggiungere Autenticazione:**
   ```php
   // ✅ CORRETTO: Verifica autenticazione e ruolo
   session_start();
   
   if (!isset($_SESSION['user_id'])) {
       header('Location: /login.php');
       exit;
   }
   
   if ($_SESSION['role'] !== 'admin') {
       http_response_code(403);
       die('Access Denied');
   }
   ```

2. **Restrizione IP (opzionale):**
   ```php
   // ✅ CORRETTO: Limita accesso a IP specifici
   $allowed_ips = ['192.168.1.100', '10.0.0.5'];
   if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
       die('Access Denied');
   }
   ```

### Per File di Backup:

1. **Non Esporre Backup nella Document Root:**
   ```bash
   # ✅ CORRETTO: Backup fuori dalla document root
   /var/www/backups/backup.sql  (fuori da document root)
   /var/www/html/              (document root)
   ```

2. **Cifrare Backup:**
   ```bash
   # ✅ CORRETTO: Cifra i backup
   mysqldump ... | openssl enc -aes-256-cbc -out backup.sql.enc
   ```

3. **Accesso Autenticato:**
   ```php
   // ✅ CORRETTO: Richiedi autenticazione per download backup
   if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
       die('Access Denied');
   }
   ```

### Per Log Files:

1. **Spostare Logs fuori dalla Document Root:**
   ```bash
   # ✅ CORRETTO: Logs fuori dalla document root
   /var/log/app/app.log  (fuori da document root)
   /var/www/html/        (document root)
   ```

2. **Bloccare Accesso:**
   ```apache
   # ✅ CORRETTO: Blocca accesso ai log
   <Directory "/var/www/html/logs">
       Require all denied
   </Directory>
   ```

3. **Non Loggare Dati Sensibili:**
   ```php
   // ✅ CORRETTO: Non loggare password o dati sensibili
   $log_data = [
       'username' => $username,
       // NON loggare: 'password' => $password
   ];
   ```

### Per Debug Mode:

1. **Disabilitare in Produzione:**
   ```php
   // ✅ CORRETTO: Disabilita debug in produzione
   define('APP_ENV', 'production');
   define('DEBUG_MODE', false);
   define('DISPLAY_ERRORS', false);
   
   if (APP_ENV === 'production') {
       ini_set('display_errors', 0);
       ini_set('display_startup_errors', 0);
       error_reporting(0);
   }
   ```

2. **Loggare Errori invece di Mostrarli:**
   ```php
   // ✅ CORRETTO: Logga errori invece di mostrarli
   ini_set('log_errors', 1);
   ini_set('error_log', '/var/log/app/errors.log');
   ```

### Per Server Signature:

1. **Nascondere Server Signature:**
   ```apache
   # ✅ CORRETTO: Nascondi versione server
   ServerTokens Prod
   ServerSignature Off
   ```

### Per Headers di Sicurezza:

1. **Aggiungere Security Headers:**
   ```apache
   # ✅ CORRETTO: Aggiungi security headers
   Header always set X-Content-Type-Options "nosniff"
   Header always set X-Frame-Options "DENY"
   Header always set X-XSS-Protection "1; mode=block"
   Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
   Header always set Content-Security-Policy "default-src 'self'"
   ```

### Per Credenziali:

1. **Non Hardcodare Credenziali:**
   ```php
   // ✅ CORRETTO: Leggi da variabili d'ambiente
   $admin_password = getenv('ADMIN_PASSWORD') ?: die('Admin password not configured');
   ```

2. **Usare Password Manager o Vault:**
   ```bash
   # ✅ CORRETTO: Usa secret management
   vault kv get secret/admin_password
   ```

## Checklist di Sicurezza

- [ ] File di configurazione fuori dalla document root
- [ ] Directory listing disabilitato
- [ ] File di backup non accessibili via web
- [ ] Log files fuori dalla document root
- [ ] Debug mode disabilitato in produzione
- [ ] Server signature nascosta
- [ ] Headers di sicurezza configurati
- [ ] Pannello admin protetto con autenticazione
- [ ] Credenziali non hardcoded
- [ ] File .htaccess non accessibili
- [ ] Permessi file corretti (755 per directory, 644 per file)
- [ ] Directory sensibili protette con .htaccess

## Riferimenti OWASP

- [OWASP Top 10 2021 - A05:2021](https://owasp.org/Top10/A05_2021-Security_Misconfiguration/)
- [OWASP Secure Configuration Guide](https://cheatsheetseries.owasp.org/cheatsheets/Cheat_Sheets.html)
- [OWASP Security Headers Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html)

## Struttura File

```
A05_Security_Misconfiguration/
├── docker-compose.yml          # Configurazione Docker
├── Dockerfile                   # ⚠️ VULNERABILE: Configurazione insicura
├── docker-entrypoint.sh         # Script di inizializzazione
├── init.sql                     # Popolamento database
├── README.md                    # Questa documentazione
├── apache/
│   └── httpd.conf               # ⚠️ VULNERABILE: Configurazione Apache insicura
├── config.php                   # ⚠️ VULNERABILE: File di configurazione esposto
├── environment.txt              # ⚠️ VULNERABILE: File environment esposto
├── db_connect.php               # ⚠️ VULNERABILE: Mostra errori dettagliati
├── index.php                    # Homepage
├── login.php                    # Pagina login
├── login_process.php            # Processamento login
├── register.php                 # Pagina registrazione
├── register_process.php         # Processamento registrazione
├── dashboard.php                # Dashboard utente
├── files.php                    # Lista documenti
├── upload.php                   # ⚠️ VULNERABILE: Upload con validazione minima
├── download.php                 # Download documenti
├── logout.php                   # Logout
├── header.php                   # Header comune
├── footer.php                   # Footer comune
├── style.css                    # Stili CSS
├── admin/
│   └── panel.php                # ⚠️ VULNERABILE: Accessibile pubblicamente
├── uploads/                     # ⚠️ VULNERABILE: Directory listing abilitato
├── backups/                     # ⚠️ VULNERABILE: File di backup esposti
│   ├── backup.sql
│   └── backup_2024-11-20.sql.bak
└── logs/                        # ⚠️ VULNERABILE: Log files accessibili
    └── app.log
```

## Note Didattiche

- Gli studenti devono identificare tutte le configurazioni di sicurezza errate
- Implementare le mitigazioni appropriate per ogni vulnerabilità
- Verificare che i file sensibili non siano più accessibili
- Configurare correttamente Apache per disabilitare directory listing
- Spostare file sensibili fuori dalla document root
- Implementare autenticazione per il pannello admin
- Disabilitare debug mode in produzione
- Aggiungere security headers appropriati

## Disclaimer

⚠️ **ATTENZIONE:** Questo codice contiene vulnerabilità intenzionali per scopi didattici.
**NON UTILIZZARE MAI IN PRODUZIONE!**

---

**Corso Secure Coding - 2025**

