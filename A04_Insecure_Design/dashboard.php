<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Recupera prenotazioni dell'utente
$stmt = $conn->prepare("SELECT b.*, s.name as service_name, s.base_price as service_base_price 
                        FROM bookings b 
                        JOIN services s ON b.service_id = s.id 
                        WHERE b.user_id = ? 
                        ORDER BY b.booking_date DESC, b.booking_time DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();

require_once 'header.php';
?>

<div class="nav">
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="services.php">📋 Servizi</a>
        <a href="dashboard.php">📊 Dashboard</a>
    </div>
    <div class="user-info">
        Ciao, <?php echo htmlspecialchars($_SESSION['full_name']); ?> | 
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</div>

<h1>📊 Le Tue Prenotazioni</h1>

<div class="card">
    <a href="services.php" class="btn btn-success">➕ Nuova Prenotazione</a>
</div>

<?php if ($bookings->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Servizio</th>
                <th>Data</th>
                <th>Ora</th>
                <th>Ospiti</th>
                <th>Prezzo Totale</th>
                <th>Sconto</th>
                <th>Stato</th>
                <th>Pagamento</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($booking = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $booking['id']; ?></td>
                    <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                    <td><?php echo date('H:i', strtotime($booking['booking_time'])); ?></td>
                    <td><?php echo $booking['number_of_guests']; ?></td>
                    <td>
                        <strong>€<?php echo number_format($booking['total_price'], 2); ?></strong>
                        <?php if ($booking['total_price'] < $booking['service_base_price']): ?>
                            <br><small style="color: red;">⚠️ Prezzo modificato!</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($booking['discount_code']): ?>
                            <?php echo htmlspecialchars($booking['discount_code']); ?>
                            (€<?php echo number_format($booking['discount_amount'], 2); ?>)
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($booking['status']); ?></td>
                    <td><?php echo htmlspecialchars($booking['payment_status']); ?></td>
                    <td>
                        <a href="modify_booking.php?id=<?php echo $booking['id']; ?>" class="btn">Modifica</a>
                        <?php if ($booking['status'] !== 'cancelled'): ?>
                            <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Sei sicuro di voler cancellare questa prenotazione?')">
                                Cancella
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">
        <p>Nessuna prenotazione trovata. <a href="services.php">Crea la tua prima prenotazione</a></p>
    </div>
<?php endif; ?>

<?php
require_once 'footer.php';
?>

