<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$doc_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();

if (!$doc) {
    die("Documento non trovato o non autorizzato.");
}

$file_path = __DIR__ . $doc['file_path'];

if (file_exists($file_path)) {
    header('Content-Type: ' . $doc['file_type']);
    header('Content-Disposition: attachment; filename="' . $doc['original_filename'] . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
} else {
    die("File non trovato sul server.");
}
?>

