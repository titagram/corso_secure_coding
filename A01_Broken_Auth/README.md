# Sistema Gestione Fatture - Applicazione Vulnerabile

Questa è un'applicazione PHP volutamente vulnerabile creata per scopi educativi nel contesto di un corso di Secure Coding.

## ATTENZIONE

⚠️ **Questa applicazione contiene vulnerabilità di sicurezza intenzionali. NON utilizzare in produzione o in ambienti reali.**

## Obiettivo Didattico

Dimostrare la vulnerabilità **OWASP A01: Broken Access Control**

## Setup

### 1. Prerequisiti
- PHP 7.4 o superiore
- MySQL 5.7 o superiore
- Web server (Apache/Nginx) con supporto PHP

### 2. Configurazione Database

```bash
mysql -u root -p < init.sql
```

### 3. Configurazione Connessione

Modifica `db_connect.php` se necessario:
- `$username` (default: 'root')
- `$password` (default: '')

### 4. Avvio

Copia i file nella document root del tuo web server oppure usa il server PHP integrato:

```bash
php -S localhost:8000
```

Accedi a: `http://localhost:8000/login.php`

## Credenziali di Test

| Username | Password    | Ruolo   |
|----------|-------------|---------|
| admin    | admin123    | admin   |
| manager  | manager123  | manager |
| alice    | alice123    | user    |
| bob      | bob123      | user    |

## Struttura Applicazione

- `login.php` - Autenticazione utente
- `dashboard.php` - Dashboard principale
- `my_invoices.php` - Elenco fatture utente
- `view_invoice.php` - Dettaglio fattura ⚠️ **VULNERABILE**
- `admin_panel.php` - Pannello amministrazione ⚠️ **VULNERABILE**
- `reports.php` - Report per manager
- `logout.php` - Logout

## Vulnerabilità Presenti

### 1. IDOR (Insecure Direct Object Reference) - Escalation Orizzontale

**File:** `view_invoice.php`

**Descrizione:** La pagina non verifica che la fattura richiesta appartenga all'utente loggato.

**Come sfruttarla:**
1. Accedi come `alice` (ha le fatture 1 e 2)
2. Vai su `view_invoice.php?id=3` (fattura di bob)
3. Puoi visualizzare i dati di fatture di altri utenti

### 2. Privilege Escalation - Escalation Verticale

**File:** `admin_panel.php`

**Descrizione:** La pagina verifica solo se l'utente è loggato, ma non controlla il ruolo.

**Come sfruttarla:**
1. Accedi come `alice` (ruolo: user)
2. Scrivi direttamente l'URL: `admin_panel.php`
3. Accedi al pannello admin senza averne i permessi

## Come Correggere le Vulnerabilità

**Per gli studenti:** Analizzare il codice e implementare i controlli di accesso mancanti.

---

**Corso Secure Coding - 2025**
