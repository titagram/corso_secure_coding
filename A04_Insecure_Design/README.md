# OWASP A04:2021 – Insecure Design

## Descrizione del Modulo

Questo laboratorio dimostra vulnerabilità di **design insicuro** nella categoria **OWASP A04:2021 – Insecure Design**. A differenza di altre vulnerabilità che riguardano errori di implementazione, A04 si concentra su **difetti nella progettazione architetturale** e nella **logica di business** dell'applicazione.

## ⚠️ ATTENZIONE

**Questa applicazione contiene vulnerabilità di sicurezza intenzionali. NON utilizzare in produzione o in ambienti reali.**

## Scenario

Sistema di prenotazioni per ristoranti, hotel ed eventi che presenta vulnerabilità critiche nella logica di business:
- Modifica prezzi nelle prenotazioni
- Applicazione coupon senza limiti
- Modifica/cancellazione prenotazioni senza controlli
- Bypass controlli di disponibilità

## Setup

### 1. Avvio Ambiente Docker

```bash
docker-compose up -d
```

L'applicazione sarà disponibile su: `http://localhost:8004`

Il database MySQL sarà accessibile su: `localhost:3304`

### 2. Credenziali Database

- **Host:** `db` (interno Docker) o `localhost:3304` (esterno)
- **Database:** `booking_system`
- **Username:** `root`
- **Password:** `rootpassword`

### 3. Account di Test

| Username | Password | Ruolo |
|----------|----------|-------|
| admin | admin123 | admin |
| mario.rossi | alice123 | user |
| luisa.verdi | manager123 | user |
| giovanni.bianchi | bob123 | user |

## Vulnerabilità Presenti

### 1. Modifica Prezzo nella Prenotazione

**File:** `create_booking.php`, `modify_booking.php`

**Descrizione:** Il prezzo totale viene calcolato lato client e può essere modificato dall'utente tramite:
- Modifica del campo hidden `total_price` nel form
- Modifica del campo `total_price` nella modifica prenotazione
- Bypass del calcolo JavaScript

**Codice Vulnerabile:**
```php
// VULNERABILITÀ: Il prezzo viene preso dal POST senza validazione!
$total_price = floatval($_POST['total_price'] ?? 0);
// Nessun controllo che il prezzo corrisponda al prezzo base del servizio
```

**Impatto:**
- L'utente può impostare un prezzo arbitrario (anche negativo o zero)
- Perdite finanziarie per l'azienda
- Violazione dell'integrità dei dati

**Come sfruttarla:**
1. Aprire il form di prenotazione
2. Aprire gli strumenti sviluppatore (F12)
3. Modificare il valore del campo hidden `total_price`
4. Oppure inviare direttamente una POST request con `total_price=0.01`

### 2. Applicazione Coupon Infinite Volte

**File:** `apply_coupon.php`

**Descrizione:** Il sistema permette di applicare lo stesso coupon infinite volte senza controlli:
- Nessun controllo se l'utente ha già usato il coupon
- Nessun controllo sul limite di utilizzi totali (`max_uses`)
- Nessun tracciamento dell'utilizzo del coupon

**Codice Vulnerabile:**
```php
// VULNERABILITÀ: Non controlla se l'utente ha già usato questo coupon
// VULNERABILITÀ: Non controlla se max_uses è stato raggiunto
$stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? ...");
// Applica sempre lo sconto senza verifiche
```

**Impatto:**
- Sconti cumulativi illimitati
- Perdite finanziarie
- Violazione delle regole di business

**Come sfruttarla:**
1. Creare una prenotazione
2. Applicare un coupon (es. `WELCOME10`)
3. Applicare lo stesso coupon più volte modificando il form
4. Oppure creare più prenotazioni e applicare lo stesso coupon a tutte

### 3. Modifica Prenotazioni già Confermate

**File:** `modify_booking.php`

**Descrizione:** Il sistema permette di modificare prenotazioni già confermate o completate:
- Nessun controllo sullo stato della prenotazione
- Nessun controllo se la data è nel passato
- Nessun controllo sulla disponibilità per la nuova data/ora
- Permette di modificare il prezzo anche dopo la conferma

**Codice Vulnerabile:**
```php
// VULNERABILITÀ: Nessun controllo se la prenotazione è già confermata/completata
// VULNERABILITÀ: Nessun controllo sulla disponibilità
$stmt = $conn->prepare("UPDATE bookings SET ... WHERE id = ?");
// Aggiorna senza verifiche sullo stato
```

**Impatto:**
- Modifica di prenotazioni già pagate
- Modifica di prenotazioni già completate
- Violazione dell'integrità dei dati
- Problemi operativi (doppie prenotazioni, conflitti)

**Come sfruttarla:**
1. Creare e confermare una prenotazione
2. Andare su "Modifica Prenotazione"
3. Modificare data, ora, numero ospiti o prezzo
4. Salvare le modifiche

### 4. Cancellazione Prenotazioni senza Controlli

**File:** `cancel_booking.php`

**Descrizione:** Il sistema permette di cancellare prenotazioni senza controlli appropriati:
- Nessun controllo se la prenotazione è già completata
- Nessun controllo se la data è nel passato
- Nessun controllo se è già stata pagata
- Nessun controllo sulla prossimità della data

**Codice Vulnerabile:**
```php
// VULNERABILITÀ: Cancella senza controllare lo stato
// Dovrebbe impedire se: status='completed', data passata, già pagato
$stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' ...");
// Cancella senza verifiche
```

**Impatto:**
- Cancellazione di servizi già erogati
- Cancellazione di prenotazioni già pagate senza rimborso
- Problemi di contabilità
- Violazione delle regole di business

**Come sfruttarla:**
1. Creare una prenotazione e confermarla
2. Modificare manualmente lo stato a 'completed' nel database
3. Tentare di cancellare la prenotazione
4. La cancellazione funzionerà anche se completata!

### 5. Bypass Controlli di Disponibilità

**File:** `create_booking.php`

**Descrizione:** Il sistema non verifica la disponibilità reale:
- Nessun controllo se ci sono già prenotazioni per quella data/ora
- Nessun controllo se il numero di ospiti supera la capacità
- Nessun controllo se il servizio è attivo
- Nessuna validazione server-side

**Codice Vulnerabile:**
```php
// VULNERABILITÀ: Nessun controllo sulla disponibilità
// VULNERABILITÀ: Nessun controllo se number_of_guests supera max_capacity
$stmt = $conn->prepare("INSERT INTO bookings ...");
// Inserisce senza verifiche
```

**Impatto:**
- Sovrapprenotazioni (overbooking)
- Conflitti di prenotazione
- Problemi operativi
- Insoddisfazione clienti

**Come sfruttarla:**
1. Creare una prenotazione per un servizio con capacità limitata
2. Impostare `number_of_guests` superiore a `max_capacity` (modificando il form)
3. Creare più prenotazioni per la stessa data/ora
4. Tutte verranno accettate senza controlli

## Esercizi Pratici

### Esercizio 1: Modificare il Prezzo di una Prenotazione

1. Accedi come utente normale
2. Vai su "Servizi" e seleziona un servizio
3. Apri gli strumenti sviluppatore (F12)
4. Nel form di prenotazione, trova il campo hidden `total_price`
5. Modifica il valore (es. da 50.00 a 0.01)
6. Compila il resto del form e invia
7. Verifica che la prenotazione sia stata creata con il prezzo modificato

**Alternativa (POST diretto):**
```bash
curl -X POST http://localhost:8004/create_booking.php \
  -d "service_id=1&booking_date=2024-12-25&booking_time=20:00&number_of_guests=2&total_price=0.01&discount_code=&discount_amount=0"
```

### Esercizio 2: Applicare lo Stesso Coupon Più Volte

1. Crea una prenotazione per un servizio (es. €50)
2. Applica il coupon `WELCOME10` (10% di sconto)
3. Modifica il form e applica di nuovo lo stesso coupon
4. Oppure crea più prenotazioni e applica lo stesso coupon a tutte
5. Verifica che lo sconto sia stato applicato più volte

**Codice coupon disponibili:**
- `WELCOME10` - 10% sconto, nessun limite
- `SUMMER20` - 20% sconto, max 100 utilizzi totali
- `VIP50` - 50% sconto, max 10 utilizzi totali
- `FIXED15` - €15 sconto fisso, nessun limite

### Esercizio 3: Modificare una Prenotazione già Confermata

1. Crea una prenotazione e confermala
2. Vai su "Dashboard" → "Modifica Prenotazione"
3. Modifica:
   - La data (anche nel passato)
   - L'ora
   - Il numero di ospiti (anche superiore alla capacità)
   - Il prezzo totale
4. Salva le modifiche
5. Verifica che siano state applicate

### Esercizio 4: Cancellare una Prenotazione Completata

1. Crea una prenotazione
2. Accedi al database e modifica lo stato a 'completed':
   ```sql
   UPDATE bookings SET status = 'completed' WHERE id = 1;
   ```
3. Torna all'applicazione e vai su "Dashboard"
4. Clicca su "Cancella" per quella prenotazione
5. Verifica che la cancellazione funzioni anche se completata

### Esercizio 5: Bypass Controlli di Capacità

1. Seleziona un servizio con capacità limitata (es. Tavolo per 4 persone)
2. Apri gli strumenti sviluppatore
3. Nel form, modifica il campo `number_of_guests` impostando un valore superiore alla capacità (es. 10 invece di 4)
4. Oppure rimuovi l'attributo `max` dal campo
5. Invia il form
6. Verifica che la prenotazione sia stata creata con numero di ospiti superiore alla capacità

## Mitigazioni (Da Implementare)

### Per Modifica Prezzo:

1. **Validazione Server-Side:**
   ```php
   // ✅ CORRETTO: Calcola il prezzo server-side
   $service = get_service($service_id);
   $base_price = $service['base_price'];
   $total_price = $base_price * $number_of_guests;
   
   // Applica sconto solo se valido
   if ($discount_code) {
       $discount = validate_and_apply_coupon($discount_code, $total_price);
       $total_price -= $discount;
   }
   
   // Ignora completamente il prezzo inviato dal client
   // $total_price = floatval($_POST['total_price']); // ❌ MAI FARE QUESTO
   ```

2. **Usare Prezzi dal Database:**
   - Sempre recuperare il prezzo base dal database
   - Calcolare il totale server-side
   - Non fidarsi mai del valore inviato dal client

### Per Coupon:

1. **Tracciare Utilizzo:**
   ```php
   // ✅ CORRETTO: Verifica se l'utente ha già usato il coupon
   $stmt = $conn->prepare("SELECT COUNT(*) FROM coupon_usage 
                          WHERE coupon_id = ? AND user_id = ?");
   $stmt->bind_param("ii", $coupon_id, $user_id);
   // Se già usato, rifiuta
   
   // Verifica limite totale
   if ($coupon['max_uses'] && $coupon['used_count'] >= $coupon['max_uses']) {
       // Coupon esaurito
   }
   
   // Registra l'utilizzo
   $stmt = $conn->prepare("INSERT INTO coupon_usage ...");
   ```

2. **Validazione Completa:**
   - Verificare se l'utente ha già usato il coupon
   - Verificare se il limite totale è stato raggiunto
   - Verificare date di validità
   - Verificare spesa minima

### Per Modifica Prenotazioni:

1. **Controlli sullo Stato:**
   ```php
   // ✅ CORRETTO: Verifica lo stato prima di permettere modifiche
   if ($booking['status'] === 'completed') {
       die("Non è possibile modificare una prenotazione completata");
   }
   
   if ($booking['status'] === 'cancelled') {
       die("Non è possibile modificare una prenotazione cancellata");
   }
   
   // Verifica se la data è nel passato
   if (strtotime($booking['booking_date']) < time()) {
       die("Non è possibile modificare una prenotazione passata");
   }
   ```

2. **Validazione Disponibilità:**
   ```php
   // ✅ CORRETTO: Verifica disponibilità per la nuova data/ora
   $existing = check_availability($service_id, $new_date, $new_time);
   if ($existing) {
       die("Data/ora non disponibile");
   }
   ```

### Per Cancellazione:

1. **Controlli Appropriati:**
   ```php
   // ✅ CORRETTO: Verifica se può essere cancellata
   if ($booking['status'] === 'completed') {
       die("Non è possibile cancellare una prenotazione completata");
   }
   
   // Verifica prossimità della data
   $booking_datetime = strtotime($booking['booking_date'] . ' ' . $booking['booking_time']);
   $hours_until = ($booking_datetime - time()) / 3600;
   
   if ($hours_until < 24) {
       die("Cancellazione non permessa meno di 24 ore prima");
   }
   
   // Se pagata, gestisci rimborso
   if ($booking['payment_status'] === 'paid') {
       // Processa rimborso prima di cancellare
   }
   ```

### Per Disponibilità:

1. **Verifica Server-Side:**
   ```php
   // ✅ CORRETTO: Verifica disponibilità e capacità
   function check_availability($service_id, $date, $time, $guests) {
       // Verifica capacità
       $service = get_service($service_id);
       if ($guests > $service['max_capacity']) {
           return false;
       }
       
       // Verifica conflitti
       $existing = get_bookings($service_id, $date, $time);
       $total_guests = array_sum(array_column($existing, 'number_of_guests'));
       
       if ($total_guests + $guests > $service['max_capacity']) {
           return false;
       }
       
       return true;
   }
   ```

2. **Transazioni Database:**
   - Usare transazioni per evitare race conditions
   - Lock delle righe durante il controllo

## Differenza con Altri Moduli

- **A01: Broken Access Control** → Problemi di autorizzazione (chi può accedere)
- **A02: Cryptographic Failures** → Problemi di cifratura
- **A03: Injection** → Problemi di input validation
- **A04: Insecure Design** → Problemi di business logic e architettura

**A04 si concentra su:**
- Flussi di business che possono essere sfruttati
- Mancanza di controlli a livello di progettazione
- Logica applicativa vulnerabile
- Processi che possono essere manipolati
- Difetti architetturali, non solo di implementazione

## Riferimenti OWASP

- [OWASP Top 10 2021 - A04:2021](https://owasp.org/Top10/A04_2021-Insecure_Design/)
- [OWASP Secure Coding Practices](https://owasp.org/www-project-secure-coding-practices-quick-reference-guide/)
- [OWASP Application Security Verification Standard](https://owasp.org/www-project-application-security-verification-standard/)

## Struttura File

```
A04_Insecure_Design/
├── docker-compose.yml          # Configurazione Docker
├── Dockerfile                   # Immagine PHP/Apache
├── init.sql                     # Popolamento database
├── README.md                    # Questa documentazione
├── db_connect.php               # Connessione database
├── index.php                    # Homepage
├── login.php                    # Pagina login
├── login_process.php            # Processamento login
├── register.php                 # Pagina registrazione
├── register_process.php         # Processamento registrazione
├── services.php                 # Lista servizi
├── create_booking.php           # ⚠️ VULNERABILE: Crea prenotazione
├── apply_coupon.php             # ⚠️ VULNERABILE: Applica coupon
├── modify_booking.php           # ⚠️ VULNERABILE: Modifica prenotazione
├── cancel_booking.php           # ⚠️ VULNERABILE: Cancella prenotazione
├── dashboard.php                # Dashboard utente
├── logout.php                   # Logout
├── header.php                   # Header comune
├── footer.php                   # Footer comune
└── style.css                    # Stili CSS
```

## Note Didattiche

- Gli studenti devono identificare tutte le vulnerabilità di business logic
- Implementare validazioni server-side appropriate
- Aggiungere controlli sullo stato delle prenotazioni
- Implementare tracciamento dell'utilizzo dei coupon
- Verificare disponibilità e capacità prima di creare prenotazioni
- Implementare controlli temporali (es. non modificare prenotazioni passate)

## Disclaimer

⚠️ **ATTENZIONE:** Questo codice contiene vulnerabilità intenzionali per scopi didattici.
**NON UTILIZZARE MAI IN PRODUZIONE!**

---

**Corso Secure Coding - 2025**

