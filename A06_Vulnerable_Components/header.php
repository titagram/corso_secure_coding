<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Blog</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- VULNERABILITÀ CRITICA: jQuery 1.7.2 obsoleto con CVE-2011-4969 (XSS) -->
    <!-- Questa versione di jQuery è vulnerabile a attacchi XSS -->
    <!-- CVE: https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2011-4969 -->
    <script src="https://code.jquery.com/jquery-1.7.2.min.js"></script>
    
    <!-- VULNERABILITÀ: Bootstrap 3.4.1 obsoleto (ultima versione 3.x, non più supportato) -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
    <!-- VULNERABILITÀ: Informazioni di versione esposte nei commenti HTML -->
    <!-- PHP Version: <?php echo phpversion(); ?> -->
    <!-- MySQL Version: <?php echo isset($mysql_version) ? $mysql_version : 'Unknown'; ?> -->
</head>
<body>
    <div class="container">

