# OWASP A02:2021 – Cryptographic Failures

## Descrizione del Modulo

Questo laboratorio dimostra vulnerabilità crittografiche comuni nella categoria **OWASP A02:2021 – Cryptographic Failures** (precedentemente "Sensitive Data Exposure"). L'applicazione è un e-commerce volutamente vulnerabile che gestisce dati sensibili in modo non sicuro.

## ⚠️ ATTENZIONE

**Questa applicazione contiene vulnerabilità di sicurezza intenzionali. NON utilizzare in produzione o in ambienti reali.**

## Scenario

Portale e-commerce che gestisce:
- Profili utente con dati personali (PII)
- Carte di credito per i pagamenti
- Storico ordini e transazioni
- Sistema di autenticazione

## Setup

### 1. Avvio Ambiente Docker

```bash
docker-compose up -d
```

L'applicazione sarà disponibile su: `http://localhost:8002`

Il database MySQL sarà accessibile su: `localhost:3302`

### 2. Credenziali Database

- **Host:** `db` (interno Docker) o `localhost:3302` (esterno)
- **Database:** `ecommerce_db`
- **Username:** `root`
- **Password:** `rootpassword`

### 3. Account di Test

Il database viene popolato automaticamente con 15 utenti di test. Ecco alcuni esempi:

| Username | Password | Algoritmo Hash | Hashcat Mode |
|----------|----------|----------------|--------------|
| mario.rossi | password | MD5 | 0 |
| luisa.verdi | 123456 | MD5 | 0 |
| admin | admin | MD5 | 0 |
| giulia.bianchi | qwerty | MD5 | 0 |
| anna.ferrari | password123 | SHA1 | 100 |
| marco.russo | letmein | SHA1 | 100 |
| luca.colombo | password | SHA256 | 1400 |
| elena.ricci | 12345678 | SHA256 | 1400 |
| davide.conti | password | bcrypt | 3200 |
| chiara.leone | 123456 | bcrypt | 3200 |

**Nota:** Alcuni utenti hanno password hashate con algoritmi deboli (MD5, SHA1, SHA256), altri con bcrypt ma password comunque deboli e facilmente craccabili.

## Vulnerabilità Presenti

### 1. Password Hashate con Algoritmi Deboli

**File:** `register_process.php`, `login_process.php`

**Descrizione:** Le password vengono hashate con algoritmi deboli e deprecati:
- **MD5** (hashcat mode 0) - facilmente rompibile in secondi
- **SHA1** (hashcat mode 100) - rompibile in minuti
- **SHA256** (hashcat mode 1400) - rompibile in minuti con password semplici
- **MD5 con salt statico** - ancora vulnerabile (salt hardcoded: "ecommerce2024")
- **bcrypt con password deboli** - algoritmo sicuro ma password facilmente craccabili

**Impatto:**
- Password facilmente recuperabili con hashcat
- Stesso hash per password identiche (MD5 senza salt)
- Violazione delle best practice di sicurezza

**Come sfruttarla:**
1. Estrarre gli hash dal database
2. Usare hashcat per rompere gli hash:
   ```bash
   # MD5
   hashcat -m 0 md5_hashes.txt rockyou.txt
   
   # SHA1
   hashcat -m 100 sha1_hashes.txt rockyou.txt
   
   # SHA256
   hashcat -m 1400 sha256_hashes.txt rockyou.txt
   
   # bcrypt (lento ma fattibile con password semplici)
   hashcat -m 3200 bcrypt_hashes.txt rockyou.txt
   ```

### 2. Dati Sensibili Memorizzati in Chiaro

**File:** `init.sql`, `dashboard.php`, `payment.php`

**Descrizione:** Dati personali sensibili memorizzati senza cifratura:
- **Carte di credito** complete (numero, scadenza, CVV)
- **Codice Fiscale** (PII)
- **Indirizzi completi**
- **Numeri di telefono**
- **Email**
- **Date di nascita**

**Impatto:**
- Violazione PCI-DSS (carte di credito)
- Violazione GDPR (dati personali)
- Accesso completo ai dati in caso di breach del database
- Identità rubata

**Come sfruttarla:**
1. Accedere direttamente al database MySQL
2. Eseguire query per estrarre dati:
   ```sql
   SELECT * FROM credit_cards;
   SELECT username, email, tax_code, phone FROM users;
   ```

### 3. CVV Memorizzato (Violazione PCI-DSS)

**File:** `credit_cards` table, `payment.php`, `add_card.php`

**Descrizione:** Il CVV (Card Verification Value) viene memorizzato nel database in chiaro.

**Impatto:**
- **Violazione critica degli standard PCI-DSS**
- Il CVV non deve MAI essere memorizzato, nemmeno cifrato
- Permette transazioni fraudolente

**Standard PCI-DSS:**
> "The three- or four-digit card validation code or value printed on the front of a payment card or the signature panel of a payment card must not be stored after authorization."

### 4. Session Tokens Prevedibili

**File:** `login_process.php`

**Descrizione:** I token di sessione sono generati in modo prevedibile:
```php
$session_token = $username . '_' . date('YmdHis');
```

**Impatto:**
- Possibilità di predire token di altri utenti
- Session hijacking
- Accesso non autorizzato agli account

**Come sfruttarla:**
1. Conoscere username di un utente
2. Predire il token basandosi sul pattern: `username_YYYYMMDDHHmmss`
3. Usare il token per impersonare l'utente

### 5. Esportazione Dati in Chiaro

**File:** `export_data.php`

**Descrizione:** La funzione di esportazione dati personali genera un file di testo in chiaro contenente:
- Tutti i dati personali
- Password hash
- Carte di credito complete con CVV
- Storico ordini

**Impatto:**
- File scaricabile contiene dati sensibili non cifrati
- Se intercettato, tutti i dati sono compromessi
- Nessuna protezione del file esportato

### 6. Comunicazione HTTP (Didattico)

**Nota:** In ambiente Docker locale, HTTPS non è configurato. In produzione, questo permetterebbe:
- Intercettazione del traffico (Man-in-the-Middle)
- Dati sensibili trasmessi in chiaro sulla rete
- Cookie di sessione intercettabili

## Esercizi Pratici

### Esercizio 1: Identificare Hash Deboli

1. Accedi al database MySQL:
   ```bash
   mysql -h localhost -P 3302 -u root -prootpassword --ssl=0 ecommerce_db
   ```

2. Estrai gli hash delle password:
   ```sql
   SELECT username, password_hash FROM users;
   ```

3. Identifica il tipo di hash:
   - MD5: 32 caratteri esadecimali
   - SHA1: 40 caratteri esadecimali
   - SHA256: 64 caratteri esadecimali
   - bcrypt: inizia con `$2y$`

4. Salva gli hash in file separati per tipo

### Esercizio 2: Rompere Hash con Hashcat

#### Preparazione

1. Scarica il dizionario `rockyou.txt`:
   ```bash
   wget https://github.com/brannondorsey/naive-hashcat/releases/download/data/rockyou.txt
   ```

2. Crea file separati per ogni tipo di hash:
   ```bash
   # Estrai MD5 (32 caratteri)
   # Estrai SHA1 (40 caratteri)
   # Estrai SHA256 (64 caratteri)
   # Estrai bcrypt (inizia con $2y$)
   ```

#### Cracking

```bash
# MD5 (mode 0) - Veloce (secondi)
hashcat -m 0 md5_hashes.txt rockyou.txt

# SHA1 (mode 100) - Veloce (minuti)
hashcat -m 100 sha1_hashes.txt rockyou.txt

# SHA256 (mode 1400) - Veloce con password semplici (minuti)
hashcat -m 1400 sha256_hashes.txt rockyou.txt

# bcrypt (mode 3200) - Lento ma fattibile con password semplici (ore)
hashcat -m 3200 bcrypt_hashes.txt rockyou.txt

# Mostra risultati
hashcat -m 0 md5_hashes.txt rockyou.txt --show
```

### Esercizio 3: Estrarre Dati Sensibili

1. Accedi al database e estrai:
   ```sql
   -- Carte di credito in chiaro
   SELECT * FROM credit_cards;
   
   -- Dati personali
   SELECT username, email, tax_code, phone, address FROM users;
   
   -- Password hash
   SELECT username, password_hash FROM users;
   ```

2. Analizza i dati estratti e identifica le vulnerabilità

### Esercizio 4: Predire Session Tokens

1. Registra un nuovo account
2. Osserva il pattern del session token
3. Prova a predire il token di un altro utente conoscendo il suo username
4. Usa il token per accedere all'account

### Esercizio 5: Analizzare Esportazione Dati

1. Accedi come utente
2. Vai su "Esporta Dati Personali"
3. Scarica il file
4. Analizza il contenuto (tutti i dati in chiaro!)
5. Identifica quali dati non dovrebbero essere esportati (CVV)

## Mitigazioni (Da Implementare)

### Per Password Hashing:

1. **Usare algoritmi sicuri:**
   ```php
   // ✅ CORRETTO: bcrypt o Argon2
   $hash = password_hash($password, PASSWORD_BCRYPT);
   // o
   $hash = password_hash($password, PASSWORD_ARGON2ID);
   
   // Verifica
   if (password_verify($password, $stored_hash)) {
       // Login riuscito
   }
   ```

2. **Salt automatico:** `password_hash()` genera salt casuali automaticamente

3. **Cost factor:** Usare cost factor appropriato (default 10 per bcrypt)

### Per Dati Sensibili:

1. **Cifratura a riposo:**
   ```php
   // Usare AES-256 per cifrare dati sensibili
   $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
   ```

2. **Tokenizzazione per carte di credito:**
   - Non memorizzare mai il numero completo
   - Usare servizi esterni (Stripe, PayPal) che gestiscono tokenizzazione
   - Memorizzare solo ultime 4 cifre per display

3. **CVV:**
   - **NON memorizzare MAI il CVV**
   - Usare solo per validazione al momento del pagamento

### Per Session Tokens:

1. **Token crittograficamente sicuri:**
   ```php
   // ✅ CORRETTO
   $token = bin2hex(random_bytes(32)); // 64 caratteri esadecimali
   // o
   $token = base64_encode(random_bytes(32));
   ```

2. **Usare sessioni PHP native:**
   ```php
   session_start(); // PHP gestisce token sicuri automaticamente
   ```

### Per Esportazione Dati:

1. **Cifrare il file:**
   ```php
   // Cifrare con AES-256
   $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
   ```

2. **Richiedere password aggiuntiva** per decifrare

3. **Non includere dati che non devono essere memorizzati** (CVV)

### Per Comunicazione:

1. **HTTPS obbligatorio:**
   - Configurare certificati SSL/TLS
   - Forzare redirect da HTTP a HTTPS
   - HSTS (HTTP Strict Transport Security)

## Tabella Riepilogativa Hashcat

| Algoritmo | Mode | Comando | Velocità | Password Semplici |
|-----------|------|---------|----------|-------------------|
| MD5 | 0 | `hashcat -m 0 hashes.txt rockyou.txt` | Secondi | ✅ Facilmente |
| SHA1 | 100 | `hashcat -m 100 hashes.txt rockyou.txt` | Minuti | ✅ Facilmente |
| SHA256 | 1400 | `hashcat -m 1400 hashes.txt rockyou.txt` | Minuti | ✅ Facilmente |
| bcrypt | 3200 | `hashcat -m 3200 hashes.txt rockyou.txt` | Ore/Giorni | ⚠️ Possibile |
| Argon2 | 9900 | `hashcat -m 9900 hashes.txt rockyou.txt` | Molto lento | ❌ Difficile |

**Nota:** Anche algoritmi "forti" come bcrypt sono vulnerabili se le password sono semplici e nel dizionario.

## Riferimenti OWASP

- [OWASP Top 10 2021 - A02:2021](https://owasp.org/Top10/A02_2021-Cryptographic_Failures/)
- [OWASP Cryptographic Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cryptographic_Storage_Cheat_Sheet.html)
- [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [PCI-DSS Requirements](https://www.pcisecuritystandards.org/document_library/)

## Struttura File

```
A02_Cryptographic_Failures/
├── docker-compose.yml          # Configurazione Docker
├── Dockerfile                   # Immagine PHP/Apache
├── init.sql                     # Popolamento database (15 utenti, carte, ordini)
├── README.md                    # Questa documentazione
├── db_connect.php               # Connessione database
├── index.php                    # Homepage
├── login.php                    # Pagina login
├── login_process.php            # ⚠️ VULNERABILE: Verifica password con hash deboli
├── register.php                 # Pagina registrazione
├── register_process.php         # ⚠️ VULNERABILE: Password hashate con MD5
├── dashboard.php                # ⚠️ VULNERABILE: Mostra dati in chiaro
├── payment.php                  # ⚠️ VULNERABILE: Carte in chiaro con CVV
├── add_card.php                 # ⚠️ VULNERABILE: Salva carte in chiaro
├── delete_card.php              # Elimina carta
├── products.php                 # Catalogo prodotti
├── view_orders.php              # Storico ordini
├── export_data.php              # ⚠️ VULNERABILE: Esporta dati in chiaro
├── logout.php                   # Logout
├── header.php                   # Header comune
├── footer.php                   # Footer comune
└── style.css                    # Stili CSS
```

## Note Didattiche

- Gli studenti devono identificare tutte le vulnerabilità crittografiche
- Implementare le mitigazioni appropriate
- Testare che le password deboli non funzionino più dopo le correzioni
- Verificare che i dati sensibili siano cifrati
- Rimuovere completamente il CVV dal database

## Disclaimer

⚠️ **ATTENZIONE:** Questo codice contiene vulnerabilità intenzionali per scopi didattici.
**NON UTILIZZARE MAI IN PRODUZIONE!**

---

**Corso Secure Coding - 2025**

