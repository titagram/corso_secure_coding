<?php
/**
 * Modifica una prenotazione esistente
 * OWASP A04: Insecure Design
 * 
 * VULNERABILITÀ CRITICA: Permette di modificare prenotazioni già confermate
 * VULNERABILITÀ: Permette di modificare il prezzo
 * VULNERABILITÀ: Nessun controllo sullo stato della prenotazione
 */

session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$booking_id = intval($_GET['id'] ?? 0);

// Recupera prenotazione
$stmt = $conn->prepare("SELECT b.*, s.name as service_name, s.base_price as service_base_price, s.max_capacity 
                        FROM bookings b 
                        JOIN services s ON b.service_id = s.id 
                        WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    $_SESSION['error'] = "Prenotazione non trovata o non autorizzata.";
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'] ?? '';
    $booking_time = $_POST['booking_time'] ?? '';
    $number_of_guests = intval($_POST['number_of_guests'] ?? 0);
    
    // VULNERABILITÀ CRITICA: Il prezzo può essere modificato!
    $total_price = floatval($_POST['total_price'] ?? $booking['total_price']);
    
    // VULNERABILITÀ: Nessun controllo se la prenotazione è già confermata/completata
    // VULNERABILITÀ: Nessun controllo sulla disponibilità per la nuova data/ora
    // VULNERABILITÀ: Nessun controllo se number_of_guests supera max_capacity
    
    $stmt = $conn->prepare("UPDATE bookings SET booking_date = ?, booking_time = ?, number_of_guests = ?, total_price = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssidii", $booking_date, $booking_time, $number_of_guests, $total_price, $booking_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Prenotazione modificata con successo!";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Errore durante la modifica: " . $conn->error;
    }
}

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="dashboard.php">📊 Dashboard</a>
    </div>
    <div class="user-info">
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>Modifica Prenotazione #<?php echo $booking['id']; ?></h1>

<div class="vulnerability-warning">
    <strong>⚠️ VULNERABILITÀ:</strong> Questo form permette di modificare prenotazioni già confermate e di cambiare il prezzo.
    Non ci sono controlli sullo stato della prenotazione o sulla disponibilità.
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
    <h2><?php echo htmlspecialchars($booking['service_name']); ?></h2>
    <p><strong>Stato attuale:</strong> <?php echo htmlspecialchars($booking['status']); ?></p>
    <p><strong>Prezzo base servizio:</strong> €<?php echo number_format($booking['service_base_price'], 2); ?></p>
    <p><strong>Prezzo attuale prenotazione:</strong> €<?php echo number_format($booking['total_price'], 2); ?></p>
</div>

<div class="card">
    <form method="POST" action="modify_booking.php?id=<?php echo $booking_id; ?>">
        <div class="form-group">
            <label for="booking_date">Data Prenotazione *</label>
            <input type="date" id="booking_date" name="booking_date" 
                   value="<?php echo $booking['booking_date']; ?>" required>
        </div>

        <div class="form-group">
            <label for="booking_time">Ora Prenotazione *</label>
            <input type="time" id="booking_time" name="booking_time" 
                   value="<?php echo date('H:i', strtotime($booking['booking_time'])); ?>" required>
        </div>

        <div class="form-group">
            <label for="number_of_guests">Numero Ospiti *</label>
            <input type="number" id="number_of_guests" name="number_of_guests" 
                   value="<?php echo $booking['number_of_guests']; ?>" 
                   min="1" max="<?php echo $booking['max_capacity']; ?>" required>
        </div>

        <div class="form-group">
            <label for="total_price">Prezzo Totale *</label>
            <input type="number" id="total_price" name="total_price" 
                   value="<?php echo $booking['total_price']; ?>" 
                   step="0.01" min="0" required>
            <small style="color: red;">⚠️ VULNERABILITÀ: Puoi modificare il prezzo! Il controllo è solo lato client.</small>
        </div>

        <button type="submit" class="btn">Salva Modifiche</button>
        <a href="dashboard.php" class="btn btn-danger">Annulla</a>
    </form>
</div>

<?php
require_once 'footer.php';
?>

