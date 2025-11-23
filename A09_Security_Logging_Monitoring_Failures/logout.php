<?php
session_start();
require_once 'logger.php';

// VULNERABILITÀ: Non logga logout!
$logger->log('INFO', 'User logged out');

session_destroy();
header('Location: index.php');
exit;

