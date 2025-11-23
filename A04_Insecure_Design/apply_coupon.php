<?php
/**
 * Applica un coupon sconto
 * OWASP A04: Insecure Design
 * 
 * VULNERABILITÀ CRITICA: Coupon applicabile infinite volte
 * Nessun controllo se l'utente ha già usato il coupon
 * Nessun controllo sul limite di utilizzi totali
 */

session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Codice non fornito']);
    exit;
}

// VULNERABILITÀ: Recupera il coupon senza controllare se è già stato usato
$stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND valid_from <= CURDATE() AND valid_until >= CURDATE()");
$stmt->bind_param("s", $code);
$stmt->execute();
$coupon = $stmt->get_result()->fetch_assoc();

if (!$coupon) {
    echo json_encode(['success' => false, 'message' => 'Codice sconto non valido o scaduto']);
    exit;
}

// VULNERABILITÀ CRITICA: Non controlla se l'utente ha già usato questo coupon
// VULNERABILITÀ: Non controlla se max_uses è stato raggiunto
// VULNERABILITÀ: Non verifica se l'utente è loggato

$discount_amount = 0;
$base_price = floatval($_GET['base_price'] ?? 0);

if ($coupon['discount_percent']) {
    // Sconto percentuale
    $discount_amount = ($base_price * $coupon['discount_percent']) / 100;
    
    // VULNERABILITÀ: Non verifica min_purchase
    if ($coupon['min_purchase'] && $base_price < $coupon['min_purchase']) {
        echo json_encode(['success' => false, 'message' => 'Spesa minima non raggiunta']);
        exit;
    }
} else if ($coupon['discount_amount']) {
    // Sconto fisso
    $discount_amount = $coupon['discount_amount'];
}

echo json_encode([
    'success' => true,
    'discount_amount' => $discount_amount,
    'discount_percent' => $coupon['discount_percent'],
    'message' => 'Sconto applicato con successo'
]);
?>

