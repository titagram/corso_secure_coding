<?php
/**
 * Crea una nuova prenotazione
 * OWASP A04: Insecure Design
 * 
 * VULNERABILITÀ 1: Il prezzo può essere modificato dall'utente
 * VULNERABILITÀ 2: Nessun controllo server-side sulla disponibilità
 * VULNERABILITÀ 3: Nessuna validazione del numero di ospiti vs capacità
 */

session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = intval($_POST['service_id'] ?? 0);
    $booking_date = $_POST['booking_date'] ?? '';
    $booking_time = $_POST['booking_time'] ?? '';
    $number_of_guests = intval($_POST['number_of_guests'] ?? 0);
    
    // VULNERABILITÀ CRITICA 1: Il prezzo viene preso dal POST senza validazione!
    // L'utente può modificare il prezzo nel form o via POST request
    $total_price = floatval($_POST['total_price'] ?? 0);
    
    // VULNERABILITÀ 2: Nessun controllo se il servizio esiste o è attivo
    // VULNERABILITÀ 3: Nessun controllo sulla disponibilità per quella data/ora
    // VULNERABILITÀ 4: Nessun controllo se number_of_guests supera max_capacity
    
    $discount_code = $_POST['discount_code'] ?? '';
    $discount_amount = floatval($_POST['discount_amount'] ?? 0);
    
    // VULNERABILITÀ 5: Il discount_amount viene preso dal POST senza validazione!
    // L'utente può impostare qualsiasi sconto
    
    // Calcola prezzo finale (senza validazione!)
    $final_price = $total_price - $discount_amount;
    if ($final_price < 0) {
        $final_price = 0; // Prezzo negativo = gratis!
    }
    
    // Inserisce la prenotazione senza controlli di business logic
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_id, booking_date, booking_time, number_of_guests, total_price, discount_code, discount_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iissidssd", $user_id, $service_id, $booking_date, $booking_time, $number_of_guests, $final_price, $discount_code, $discount_amount);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Prenotazione creata con successo!";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Errore durante la creazione della prenotazione: " . $conn->error;
    }
}

// Recupera servizio
$service_id = intval($_GET['service_id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service) {
    $_SESSION['error'] = "Servizio non trovato.";
    header("Location: services.php");
    exit();
}

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="services.php">📋 Servizi</a>
        <a href="dashboard.php">📊 Dashboard</a>
    </div>
    <div class="user-info">
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>Nuova Prenotazione</h1>

<div class="vulnerability-warning">
    <strong>⚠️ VULNERABILITÀ:</strong> Questo form permette di modificare il prezzo e lo sconto.
    I controlli sono solo lato client e possono essere bypassati facilmente.
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php 
        echo htmlspecialchars($_SESSION['error']); 
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<div class="card">
    <h2><?php echo htmlspecialchars($service['name']); ?></h2>
    <p><?php echo htmlspecialchars($service['description']); ?></p>
    <p><strong>Prezzo base:</strong> €<?php echo number_format($service['base_price'], 2); ?></p>
    <p><strong>Capacità massima:</strong> <?php echo $service['max_capacity']; ?> persone</p>
</div>

<div class="card">
    <form method="POST" action="create_booking.php" id="bookingForm">
        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
        
        <!-- VULNERABILITÀ: Campo prezzo nascosto ma modificabile -->
        <input type="hidden" name="total_price" id="total_price" value="<?php echo $service['base_price']; ?>">
        <input type="hidden" name="discount_amount" id="discount_amount" value="0">
        
        <div class="form-group">
            <label for="booking_date">Data Prenotazione *</label>
            <input type="date" id="booking_date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="form-group">
            <label for="booking_time">Ora Prenotazione *</label>
            <input type="time" id="booking_time" name="booking_time" required>
        </div>

        <div class="form-group">
            <label for="number_of_guests">Numero Ospiti *</label>
            <input type="number" id="number_of_guests" name="number_of_guests" min="1" max="<?php echo $service['max_capacity']; ?>" required 
                   onchange="calculatePrice()">
            <small>⚠️ Il controllo max è solo lato client - può essere bypassato!</small>
        </div>

        <div class="form-group">
            <label for="discount_code">Codice Sconto (opzionale)</label>
            <input type="text" id="discount_code" name="discount_code" placeholder="Inserisci codice sconto">
            <button type="button" class="btn" onclick="applyCoupon()">Applica Sconto</button>
        </div>

        <div class="alert alert-info">
            <strong>Totale:</strong> <span id="display_total">€<?php echo number_format($service['base_price'], 2); ?></span>
            <br>
            <small>⚠️ Il prezzo viene calcolato lato client e può essere modificato!</small>
        </div>

        <button type="submit" class="btn">Conferma Prenotazione</button>
        <a href="services.php" class="btn btn-danger">Annulla</a>
    </form>
</div>

<script>
function calculatePrice() {
    const basePrice = <?php echo $service['base_price']; ?>;
    const guests = parseInt(document.getElementById('number_of_guests').value) || 1;
    const total = basePrice * guests;
    
    document.getElementById('total_price').value = total;
    document.getElementById('display_total').textContent = '€' + total.toFixed(2);
}

function applyCoupon() {
    const code = document.getElementById('discount_code').value;
    if (!code) {
        alert('Inserisci un codice sconto');
        return;
    }
    
    // VULNERABILITÀ: Il calcolo dello sconto è lato client
    // In un'app reale, questo dovrebbe essere fatto server-side
    fetch('apply_coupon.php?code=' + encodeURIComponent(code))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const discount = data.discount_amount || 0;
                document.getElementById('discount_amount').value = discount;
                const currentTotal = parseFloat(document.getElementById('total_price').value);
                const newTotal = Math.max(0, currentTotal - discount);
                document.getElementById('display_total').textContent = '€' + newTotal.toFixed(2);
                alert('Sconto applicato: €' + discount.toFixed(2));
            } else {
                alert('Codice sconto non valido');
            }
        });
}
</script>

<?php
require_once 'footer.php';
?>

