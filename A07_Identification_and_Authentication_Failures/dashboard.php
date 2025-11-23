<?php
/**
 * Dashboard - Pagina Protetta
 * Portale Dipendenti - Employee Portal
 */

session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['user_id'])) {
    // Log accesso non autorizzato
    error_log("Tentativo di accesso non autorizzato alla dashboard");
    header("Location: login.php");
    exit();
}

// Log accesso alla dashboard
error_log("Utente " . $_SESSION['username'] . " ha acceduto alla dashboard");

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Portale Dipendenti</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dashboard-container {
            background: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        .welcome-header {
            margin-bottom: 30px;
        }

        .welcome-header h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 15px;
        }

        .welcome-header p {
            color: #666;
            font-size: 18px;
        }

        .user-info {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .user-info h2 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .user-info p {
            color: #666;
            font-size: 14px;
        }

        .btn-logout {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
        }

        .info-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #e8eaf6;
            border-radius: 5px;
            text-align: left;
        }

        .info-section h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .info-section p {
            color: #666;
            line-height: 1.6;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="welcome-header">
            <h1>Dashboard Dipendenti</h1>
            <p>Area riservata del portale aziendale</p>
        </div>

        <div class="user-info">
            <h2>Benvenuto, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
            <p>Username: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p>ID Sessione: <?php echo htmlspecialchars(session_id()); ?></p>
        </div>

        <a href="logout.php" class="btn-logout">Esci dal Portale</a>

        <div class="info-section">
            <h3>Informazioni Account</h3>
            <p>
                Sei autenticato correttamente nel sistema.
                Questa è un'area protetta accessibile solo dopo il login.
            </p>
            <p style="margin-top: 10px;">
                <strong>Promemoria di sicurezza:</strong> Effettua sempre il logout quando
                termini la sessione, specialmente se utilizzi un computer condiviso.
            </p>
        </div>
    </div>
</body>
</html>
