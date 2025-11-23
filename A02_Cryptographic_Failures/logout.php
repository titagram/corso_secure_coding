<?php
session_start();

// Distruggi la sessione
session_destroy();

header('Location: index.php');
exit();
?>

