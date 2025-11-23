# OWASP A09:2021 – Security Logging and Monitoring Failures

## Descrizione del Modulo

Questo laboratorio dimostra vulnerabilità legate a **logging e monitoraggio di sicurezza** nella categoria **OWASP A09:2021 – Security Logging and Monitoring Failures**. L'applicazione è un sistema di gestione risorse sensibili (Vault) che contiene intenzionalmente vulnerabilità di logging insufficiente, log manipolabili, assenza di alerting e monitoraggio.

## ⚠️ ATTENZIONE

**Questa applicazione contiene vulnerabilità intenzionali. NON utilizzare in produzione o in ambienti reali.**

## Scenario

Sistema Vault per gestire risorse sensibili (chiavi API, credenziali, documenti riservati) che:
- Non logga eventi critici (tentativi di login falliti, accessi non autorizzati)
- Permette manipolazione e cancellazione dei log
- Non genera alert per attività sospette
- Include informazioni sensibili nei log
- Non ha sistema di monitoraggio in tempo reale

## Setup

### 1. Avvio Ambiente Docker

```bash
docker-compose up -d
```

L'applicazione sarà disponibile su: `http://localhost:8009`

Il database MySQL sarà accessibile su: `localhost:3309`

### 2. Credenziali Database

- **Host:** `db` (interno Docker) o `localhost:3309` (esterno)
- **Database:** `vault_system`
- **Username:** `root`
- **Password:** `rootpassword`

### 3. Account di Test

| Username | Password | Ruolo |
|----------|----------|-------|
| admin | password | admin |
| mario.rossi | password | user |
| lucia.verdi | password | user |
| auditor | password | auditor |

## Vulnerabilità Presenti

### 1. Mancanza di Logging di Eventi Critici

**File:** `login_process.php`, `vault.php`, `logger.php`

**Descrizione:** Il sistema non logga tentativi di login falliti, accessi non autorizzati a risorse, o altre attività sospette. Questo permette a un attaccante di eseguire brute-force attacks o accedere a risorse senza lasciare traccia.

**Impatto:**
- Nessun audit trail per tentativi di accesso falliti
- Impossibile rilevare brute-force attacks
- Nessuna traccia di accessi non autorizzati
- Impossibile investigare incidenti di sicurezza

**Come sfruttarla:**

#### Passo 1: Eseguire Brute-Force Attack

1. Vai su `http://localhost:8009/login.php`
2. Prova a fare login con credenziali errate multiple volte
3. Vai su `http://localhost:8009/logs.php` (dopo login)
4. **Nota:** Non vedrai alcun log dei tentativi falliti!

#### Passo 2: Verificare Assenza di Logging

1. Accedi come `admin` / `password`
2. Vai su `http://localhost:8009/logs.php`
3. Controlla i log - vedrai solo login riusciti, nessun tentativo fallito
4. Prova ad accedere a una risorsa a cui non hai accesso
5. **Nota:** Non vedrai log di tentativi di accesso negati!

#### Passo 3: Script per Brute-Force

Crea uno script Python per testare brute-force:

```python
import requests

url = "http://localhost:8009/login_process.php"
usernames = ["admin", "mario.rossi", "lucia.verdi"]
passwords = ["password", "123456", "admin", "test", "password123"]

for username in usernames:
    for password in passwords:
        data = {
            "username": username,
            "password": password
        }
        response = requests.post(url, data=data, allow_redirects=False)
        if response.status_code == 302:
            print(f"✅ SUCCESS: {username}:{password}")
        else:
            print(f"❌ FAILED: {username}:{password}")
```

**Nota:** Puoi eseguire questo script migliaia di volte senza essere rilevato perché i tentativi falliti non sono loggati!

### 2. Log Facilmente Manipolabili

**File:** `logs.php`, `logger.php`

**Descrizione:** I log sono salvati in file di testo semplice accessibili pubblicamente, senza protezione, integrità (hash/firma), o controllo accessi. Un attaccante può leggere, modificare o cancellare i log per coprire le proprie tracce.

**Impatto:**
- Cancellazione di tracce di attacchi
- Modifica di log per incolpare altri utenti
- Lettura di informazioni sensibili dai log
- Impossibilità di investigare incidenti

**Come sfruttarla:**

#### Passo 1: Accedere ai Log

1. Accedi come qualsiasi utente
2. Vai su `http://localhost:8009/logs.php`
3. **Nota:** I log sono accessibili senza controllo accessi!

#### Passo 2: Cancellare Log

1. Vai su `http://localhost:8009/logs.php`
2. Clicca su "Cancella Tutti i Log"
3. Conferma l'azione
4. **Nota:** I log vengono cancellati senza autenticazione/autorizzazione!

#### Passo 3: Modificare Log Manualmente

1. Accedi al container Docker:
   ```bash
   docker exec -it <container_id> bash
   ```
2. Modifica il file di log:
   ```bash
   echo "[2024-01-01 00:00:00] INFO: User logged in | IP: 192.168.1.1 | User: attacker" >> /var/www/html/logs/app.log
   ```
3. Ricarica `http://localhost:8009/logs.php`
4. **Nota:** Il log modificato appare come se fosse legittimo!

#### Passo 4: Cancellare Singole Righe

1. Vai su `http://localhost:8009/logs.php`
2. Per ogni riga di log, c'è un pulsante "Elimina"
3. Clicca su "Elimina" per una riga sospetta
4. **Nota:** Puoi cancellare singole righe per coprire tracce!

#### Passo 5: Accedere Direttamente al File di Log

1. Prova ad accedere direttamente: `http://localhost:8009/logs/app.log`
2. **Nota:** Il file potrebbe essere accessibile direttamente (dipende dalla configurazione Apache)

### 3. Informazioni Sensibili nei Log

**File:** `login_process.php`, `register_process.php`, `logger.php`

**Descrizione:** Il sistema logga password, token, session ID, e altre informazioni sensibili in chiaro nei log. Un attaccante che accede ai log può rubare credenziali e compromettere account.

**Impatto:**
- Furto di credenziali
- Compromissione di account
- Accesso non autorizzato
- Violazione di privacy

**Come sfruttarla:**

#### Passo 1: Verificare Password nei Log

1. Vai su `http://localhost:8009/register.php`
2. Registra un nuovo utente con password: `MySecretPassword123!`
3. Accedi come admin e vai su `http://localhost:8009/logs.php`
4. Cerca nei log - troverai la password in chiaro!

#### Passo 2: Cercare Token e Session ID

1. Accedi al sistema
2. Vai su `http://localhost:8009/logs.php`
3. Cerca nei log per "session_id" o "token"
4. **Nota:** Troverai session ID e token nei log!

#### Passo 3: Estrarre Credenziali dai Log

1. Accedi al container Docker:
   ```bash
   docker exec -it <container_id> bash
   ```
2. Leggi il file di log:
   ```bash
   cat /var/www/html/logs/app.log | grep -i password
   ```
3. **Nota:** Vedrai password in chiaro nei log!

### 4. Nessun Alerting/Monitoraggio

**File:** `admin.php`, `logger.php`, `vault.php`

**Descrizione:** Il sistema non genera alert per attività sospette come:
- Tentativi di login multipli falliti
- Accessi a risorse sensibili
- Transazioni ad alto valore
- Accessi da IP sospetti
- Attività anomale

**Impatto:**
- Impossibile rilevare attacchi in tempo reale
- Nessuna notifica per attività sospette
- Impossibile rispondere rapidamente a incidenti
- Nessuna correlazione di eventi

**Come sfruttarla:**

#### Passo 1: Eseguire Attività Sospette Senza Alert

1. Accedi come `admin`
2. Vai su `http://localhost:8009/admin.php?action=create_transaction`
3. Crea una transazione con importo molto alto (es. €1,000,000)
4. **Nota:** Non riceverai alcun alert!

#### Passo 2: Accedere a Risorse Sensibili

1. Accedi come utente normale
2. Vai su `http://localhost:8009/vault.php`
3. Prova ad accedere a risorse "top_secret"
4. **Nota:** Anche se l'accesso viene negato, non viene generato alcun alert!

#### Passo 3: Verificare Assenza di Monitoring

1. Vai su `http://localhost:8009/admin.php?action=monitoring`
2. **Nota:** Il pannello di monitoring mostra che non è implementato!

### 5. Log Cancellabili Facilmente

**File:** `logs.php`, `logger.php`

**Descrizione:** I log possono essere cancellati facilmente senza backup, verifica di integrità, o controllo accessi. Un attaccante può cancellare tutti i log dopo un attacco per coprire le proprie tracce.

**Impatto:**
- Cancellazione di evidenze di attacchi
- Impossibilità di investigare incidenti
- Perdita di audit trail
- Nessun backup dei log

**Come sfruttarla:**

#### Passo 1: Eseguire Attacco e Cancellare Log

1. Esegui un brute-force attack (vedi Sezione 1)
2. Se riesci a compromettere un account, accedi
3. Vai su `http://localhost:8009/logs.php`
4. Clicca su "Cancella Tutti i Log"
5. **Nota:** Tutte le tracce dell'attacco vengono cancellate!

#### Passo 2: Verificare Assenza di Backup

1. Cancella i log
2. Cerca file di backup:
   ```bash
   docker exec -it <container_id> bash
   ls -la /var/www/html/logs/
   ```
3. **Nota:** Non ci sono backup dei log!

### 6. Nessuna Analisi dei Log

**File:** `admin.php`, `logger.php`

**Descrizione:** Il sistema non analizza i log per:
- Pattern sospetti
- Correlazione di eventi
- Rilevamento di anomalie
- Statistiche di sicurezza

**Impatto:**
- Impossibile rilevare attacchi complessi
- Nessuna correlazione di eventi multipli
- Impossibile identificare pattern sospetti
- Nessuna intelligence sulla sicurezza

**Come sfruttarla:**

#### Passo 1: Eseguire Attacco Distribuito

1. Esegui tentativi di login da IP diversi
2. Ogni tentativo sembra isolato
3. **Nota:** Il sistema non correla i tentativi multipli!

#### Passo 2: Verificare Assenza di Analisi

1. Vai su `http://localhost:8009/admin.php?action=monitoring`
2. **Nota:** Non c'è analisi dei log, correlazione di eventi, o rilevamento di pattern!

## Esercizi Pratici

### Esercizio 1: Brute-Force Attack Non Rilevato

1. Crea uno script che prova 1000 combinazioni username/password
2. Verifica che nessun tentativo fallito sia loggato
3. Se riesci a trovare una password valida, accedi
4. Cancella i log per coprire le tracce

### Esercizio 2: Modificare Log per Incolpare Altri

1. Accedi come admin
2. Modifica manualmente i log per far sembrare che un altro utente abbia fatto azioni sospette
3. Verifica che i log modificati appaiano come legittimi

### Esercizio 3: Estrarre Credenziali dai Log

1. Registra un nuovo utente
2. Accedi ai log e estrai la password
3. Usa la password estratta per accedere all'account

### Esercizio 4: Transazione Sospetta Senza Alert

1. Accedi come admin
2. Crea una transazione con importo molto alto (€10,000,000)
3. Verifica che non venga generato alcun alert
4. Accedi a risorse sensibili senza generare alert

### Esercizio 5: Cancellare Tracce di Attacco

1. Esegui un attacco (brute-force, accesso non autorizzato, etc.)
2. Accedi ai log
3. Cancella tutte le righe relative al tuo attacco
4. Verifica che non ci siano più tracce

## Mitigazioni (Da Implementare)

### Per Logging di Eventi Critici:

1. **Loggare Tutti gli Eventi Critici:**
   ```php
   // ✅ CORRETTO: Logga anche tentativi falliti
   if (!$success) {
       $logger->log('WARNING', "Failed login attempt", [
           'username' => $username,
           'ip' => $_SERVER['REMOTE_ADDR'],
           'timestamp' => time()
       ]);
   }
   ```

2. **Loggare Accessi Non Autorizzati:**
   ```php
   // ✅ CORRETTO: Logga tentativi di accesso negati
   if (!$has_access) {
       $logger->log('ALERT', "Unauthorized access attempt", [
           'user_id' => $user_id,
           'resource_id' => $resource_id,
           'ip' => $_SERVER['REMOTE_ADDR']
       ]);
   }
   ```

3. **Loggare Tutte le Operazioni Sensibili:**
   ```php
   // ✅ CORRETTO: Logga tutte le operazioni
   $logger->log('INFO', "Resource accessed", [
       'user_id' => $user_id,
       'resource_id' => $resource_id,
       'action' => $action,
       'success' => $success
   ]);
   ```

### Per Protezione dei Log:

1. **Proteggere File di Log:**
   ```php
   // ✅ CORRETTO: File di log fuori dalla root web
   $log_file = '/var/log/vault/app.log';  // Fuori da /var/www/html
   ```

2. **Controllo Accessi:**
   ```php
   // ✅ CORRETTO: Solo admin può accedere ai log
   if ($_SESSION['role'] !== 'admin') {
       die("Access denied");
   }
   ```

3. **Integrità dei Log:**
   ```php
   // ✅ CORRETTO: Calcola hash per verificare integrità
   $log_hash = hash_file('sha256', $log_file);
   // Salva hash separatamente e verifica periodicamente
   ```

4. **Crittografia:**
   ```php
   // ✅ CORRETTO: Crittografa log sensibili
   $encrypted = openssl_encrypt($log_entry, 'AES-256-CBC', $key);
   file_put_contents($log_file, $encrypted, FILE_APPEND);
   ```

5. **Rotazione Log:**
   ```php
   // ✅ CORRETTO: Ruota log quando raggiungono dimensione massima
   if (filesize($log_file) > 10 * 1024 * 1024) {  // 10MB
       rename($log_file, $log_file . '.' . date('Y-m-d'));
   }
   ```

### Per Informazioni Sensibili:

1. **Non Loggare Password:**
   ```php
   // ❌ VULNERABILE
   $logger->log('INFO', "Login", ['password' => $password]);
   
   // ✅ CORRETTO: Non loggare password
   $logger->log('INFO', "Login attempt", ['username' => $username]);
   ```

2. **Sanitizzare Dati Sensibili:**
   ```php
   // ✅ CORRETTO: Maschera dati sensibili
   $logger->log('INFO', "Transaction", [
       'credit_card' => substr($card, -4),  // Solo ultime 4 cifre
       'amount' => $amount
   ]);
   ```

3. **Usare Livelli di Log Appropriati:**
   ```php
   // ✅ CORRETTO: Dati sensibili solo in log crittografati
   if ($level === 'SENSITIVE') {
       $encrypted = encrypt($data);
       log_to_secure_storage($encrypted);
   }
   ```

### Per Alerting/Monitoraggio:

1. **Alert per Tentativi Falliti:**
   ```php
   // ✅ CORRETTO: Genera alert dopo X tentativi falliti
   $failed_attempts = get_failed_attempts($username, $ip);
   if ($failed_attempts > 5) {
       send_alert("Multiple failed login attempts", [
           'username' => $username,
           'ip' => $ip,
           'attempts' => $failed_attempts
       ]);
   }
   ```

2. **Alert per Transazioni Sospette:**
   ```php
   // ✅ CORRETTO: Alert per importi alti
   if ($amount > 10000) {
       send_alert("High-value transaction", [
           'user_id' => $user_id,
           'amount' => $amount,
           'type' => $transaction_type
       ]);
   }
   ```

3. **Monitoraggio in Tempo Reale:**
   ```php
   // ✅ CORRETTO: Usa SIEM o sistema di monitoraggio
   // Integra con ELK Stack, Splunk, o simili
   send_to_siem([
       'event' => 'unauthorized_access',
       'severity' => 'high',
       'details' => $details
   ]);
   ```

4. **Correlazione di Eventi:**
   ```php
   // ✅ CORRETTO: Correla eventi multipli
   $suspicious_pattern = detect_pattern([
       'multiple_failed_logins',
       'access_from_new_ip',
       'high_value_transaction'
   ]);
   if ($suspicious_pattern) {
       send_alert("Suspicious activity pattern detected");
   }
   ```

### Per Analisi dei Log:

1. **Analisi Pattern:**
   ```php
   // ✅ CORRETTO: Analizza log per pattern sospetti
   $patterns = analyze_logs([
       'brute_force_detection',
       'anomaly_detection',
       'correlation_analysis'
   ]);
   ```

2. **Dashboard di Monitoraggio:**
   ```php
   // ✅ CORRETTO: Dashboard con statistiche e alert
   $dashboard = [
       'failed_logins_today' => count_failed_logins_today(),
       'suspicious_activities' => get_suspicious_activities(),
       'alerts' => get_active_alerts()
   ];
   ```

3. **Integrazione SIEM:**
   ```php
   // ✅ CORRETTO: Invia log a SIEM centralizzato
   send_to_siem($log_entry);
   ```

## Riferimenti OWASP

- [OWASP Top 10 2021 - A09:2021](https://owasp.org/Top10/A09_2021-Security_Logging_and_Monitoring_Failures/)
- [OWASP Logging Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html)
- [OWASP Application Security Verification Standard - Logging](https://owasp.org/www-project-application-security-verification-standard/)

## Struttura File

```
A09_Security_Logging_Monitoring_Failures/
├── docker-compose.yml          # Configurazione Docker
├── Dockerfile                   # Immagine PHP/Apache
├── init.sql                     # Popolamento database
├── README.md                    # Questa documentazione
├── db_connect.php               # Connessione database
├── logger.php                   # ⚠️ VULNERABILE: Sistema di logging vulnerabile
├── index.php                    # Homepage
├── login.php                    # Pagina login
├── login_process.php            # ⚠️ VULNERABILE: Non logga tentativi falliti
├── register.php                 # Pagina registrazione
├── register_process.php          # ⚠️ VULNERABILE: Logga password in chiaro
├── dashboard.php                # Dashboard utente
├── vault.php                    # ⚠️ VULNERABILE: Accesso risorse senza logging completo
├── logs.php                     # ⚠️ VULNERABILE: Log accessibili e cancellabili
├── admin.php                    # ⚠️ VULNERABILE: Nessun alerting/monitoraggio
├── logout.php                   # Logout
├── header.php                   # Header comune
├── footer.php                   # Footer comune
└── style.css                    # Stili CSS
```

## Note Finali

Questo modulo dimostra l'importanza di:
- Loggare tutti gli eventi critici, inclusi tentativi falliti
- Proteggere i log da accesso non autorizzato e manipolazione
- Non includere informazioni sensibili nei log
- Implementare sistemi di alerting e monitoraggio
- Analizzare i log per rilevare pattern sospetti
- Centralizzare e proteggere i log

**Ricorda:** Queste vulnerabilità sono intenzionali e create per scopi didattici. In produzione, implementa sempre tutte le mitigazioni appropriate.

