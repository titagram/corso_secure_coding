# OWASP A07: Identification and Authentication Failures

## Descrizione del Modulo

Questo modulo dimostra vulnerabilità legate all'identificazione e autenticazione degli utenti (OWASP A07).

## Scenario

Portale di login per dipendenti di un'azienda che presenta vulnerabilità critiche nel processo di autenticazione.

## Struttura File

```
07-Identification_and_Authentication_Failures/
├── init.sql              # Script di inizializzazione database
├── db_connect.php        # Connessione al database
├── login.php            # Pagina di login (form HTML)
├── login_process.php    # Elaborazione del login (VULNERABILE)
├── dashboard.php        # Pagina protetta
├── logout.php           # Script di logout
└── README.md            # Questa documentazione
```

## Setup

### 1. Inizializzare il Database

```bash
mysql -u root -p < init.sql
```

### 2. Configurare il Database

Modifica `db_connect.php` se necessario per adattare le credenziali del database:
- Host: localhost
- Database: employee_portal
- User: root
- Password: (vuota di default)

### 3. Avviare il Server

```bash
php -S localhost:8000
```

### 4. Accedere all'Applicazione

Apri il browser: `http://localhost:8000/login.php`

## Account di Test

| Username   | Password          | Nome Completo    |
|------------|-------------------|------------------|
| m.rossi    | pa$$w0rd!         | Mario Rossi      |
| a.bianchi  | qwerty123         | Anna Bianchi     |
| g.verdi    | SuperAdmin@2024   | Giuseppe Verdi   |

## Vulnerabilità Presenti

### 1. User Enumeration (Enumerazione Utenti)

**File:** `login_process.php`

Il sistema fornisce messaggi di errore differenti che permettono di capire se un utente esiste o meno:

- **Utente non esistente:** "Errore: Utente non trovato."
- **Utente esistente, password errata:** "Errore: Password errata."

**Impatto:**
- Un attaccante può enumerare gli utenti validi del sistema
- Riduce lo spazio di ricerca per attacchi brute-force
- Facilita attacchi di social engineering mirati

### 2. Assenza di Protezione Brute-Force

**File:** `login_process.php`

Il sistema non implementa nessuna protezione contro attacchi brute-force:

- ❌ Nessun rate limiting (limitazione tentativi)
- ❌ Nessun account lockout (blocco account)
- ❌ Nessun CAPTCHA
- ❌ Nessun delay progressivo
- ❌ Nessuna notifica di accessi sospetti

**Impatto:**
- Possibilità di effettuare migliaia di tentativi di login senza limitazioni
- Attacchi automatizzati di password guessing
- Password deboli possono essere scoperte facilmente

## Esercizi Pratici

### Esercizio 1: User Enumeration

1. Tenta il login con username inesistente (es. "utente_falso")
2. Osserva il messaggio di errore
3. Tenta il login con username valido ma password errata (es. "m.rossi" con password "wrong")
4. Confronta i messaggi di errore

### Esercizio 2: Brute-Force Attack

Usa uno strumento come `hydra` o uno script Python per testare:

```bash
# Esempio con hydra (installare hydra prima)
hydra -l m.rossi -P /path/to/password-list.txt localhost -s 8000 http-post-form "/login_process.php:username=^USER^&password=^PASS^:Password errata"
```

## Mitigazioni (Da Implementare)

### Per User Enumeration:

1. **Messaggi di Errore Generici:**
   ```php
   $_SESSION['error'] = "Credenziali non valide.";
   ```

2. **Timing Costante:**
   Assicurarsi che il tempo di risposta sia simile sia per utenti esistenti che non esistenti.

### Per Brute-Force Protection:

1. **Rate Limiting:**
   ```php
   // Limitare tentativi per IP
   // Esempio: max 5 tentativi ogni 15 minuti
   ```

2. **Account Lockout:**
   ```php
   // Bloccare account dopo X tentativi falliti
   // Esempio: 5 tentativi = 15 minuti di blocco
   ```

3. **CAPTCHA:**
   ```php
   // Implementare reCAPTCHA dopo 3 tentativi falliti
   ```

4. **Multi-Factor Authentication (MFA):**
   Aggiungere un secondo fattore di autenticazione (SMS, app, email).

5. **Monitoring e Alerting:**
   ```php
   // Notificare utente/admin di tentativi sospetti
   error_log("ALERT: Multiple failed login attempts for user: " . $username);
   ```

## Riferimenti OWASP

- [OWASP Top 10 2021 - A07:2021](https://owasp.org/Top10/A07_2021-Identification_and_Authentication_Failures/)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [OWASP Credential Stuffing Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Credential_Stuffing_Prevention_Cheat_Sheet.html)

## Note di Sicurezza

⚠️ **ATTENZIONE:** Questo codice contiene vulnerabilità intenzionali per scopi didattici.
**NON UTILIZZARE MAI IN PRODUZIONE!**

## Licenza

Materiale didattico per corso di Secure Coding - Gabriele Tita.
