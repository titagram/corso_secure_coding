<?php
// VULNERABILITÀ: Sistema di logging vulnerabile con molte carenze

class Logger {
    private $log_file;
    
    public function __construct($log_file = '/var/www/html/logs/app.log') {
        $this->log_file = $log_file;
    }
    
    // VULNERABILITÀ: Log accessibile e modificabile (file di testo semplice)
    // VULNERABILITÀ: Nessuna integrità (hash/firma)
    // VULNERABILITÀ: Nessuna rotazione log
    // VULNERABILITÀ: Nessuna crittografia
    
    public function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user = $_SESSION['username'] ?? 'anonymous';
        
        // VULNERABILITÀ: Include informazioni sensibili nei log (password, token, etc.)
        $log_entry = "[$timestamp] $level: $message | IP: $ip | User: $user";
        
        // VULNERABILITÀ: Aggiunge contesto senza sanitizzazione
        if (!empty($context)) {
            $log_entry .= " | Context: " . json_encode($context);
        }
        $log_entry .= "\n";
        
        // VULNERABILITÀ: Scrive in file accessibile pubblicamente
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    // VULNERABILITÀ: Metodo per loggare login - ma non logga tentativi falliti!
    public function logLogin($username, $success) {
        // VULNERABILITÀ CRITICA: Logga solo login riusciti, ignora falliti!
        if ($success) {
            $this->log('INFO', "Login successful - username: $username");
        }
        // VULNERABILITÀ: Non logga tentativi falliti - nessun audit trail per brute-force!
    }
    
    // VULNERABILITÀ: Log accesso risorse - ma incompleto
    public function logResourceAccess($resource_id, $action, $success) {
        // VULNERABILITÀ: Logga solo accessi riusciti, ignora tentativi non autorizzati!
        if ($success) {
            $this->log('INFO', "Resource access - resource_id: $resource_id, action: $action");
        }
        // VULNERABILITÀ: Non logga tentativi di accesso non autorizzati!
    }
    
    // VULNERABILITÀ: Log transazioni - ma non per importi sospetti
    public function logTransaction($transaction_type, $amount, $details) {
        // VULNERABILITÀ: Logga sempre, ma non genera alert per importi sospetti
        $this->log('INFO', "Transaction - type: $transaction_type, amount: $amount", $details);
        // VULNERABILITÀ: Nessun alerting per transazioni ad alto valore o sospette!
    }
    
    // VULNERABILITÀ: Metodo per cancellare log (dovrebbe essere protetto!)
    public function clearLogs() {
        // VULNERABILITÀ CRITICA: Permette cancellazione log senza autenticazione/autorizzazione!
        file_put_contents($this->log_file, '');
        $this->log('INFO', 'Logs cleared'); // Ironia: logga la cancellazione!
    }
    
    // VULNERABILITÀ: Metodo per leggere log (dovrebbe essere protetto!)
    public function getLogs($lines = 100) {
        // VULNERABILITÀ: Permette lettura log senza controllo accessi
        if (file_exists($this->log_file)) {
            $content = file_get_contents($this->log_file);
            $log_lines = explode("\n", $content);
            return array_slice(array_reverse($log_lines), 0, $lines);
        }
        return [];
    }
}

// VULNERABILITÀ: Istanza globale accessibile ovunque
$logger = new Logger();

