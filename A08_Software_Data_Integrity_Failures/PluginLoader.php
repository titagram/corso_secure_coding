<?php
// VULNERABILITÀ CRITICA: Classe vulnerabile a deserializzazione non sicura
// Questa classe può essere sfruttata per eseguire codice arbitrario tramite unserialize()

class PluginLoader {
    public $plugin_file;
    public $config_data;
    public $command;
    
    // VULNERABILITÀ: Magic method __wakeup() viene chiamato durante unserialize()
    // Se un attaccante controlla i dati serializzati, può eseguire comandi arbitrari
    public function __wakeup() {
        // VULNERABILITÀ: Esegue comandi senza validazione
        if (isset($this->command) && !empty($this->command)) {
            // VULNERABILITÀ CRITICA: Esecuzione comando arbitrario
            // Esegue il comando e salva l'output in un file per verifica
            $output_file = '/tmp/plugin_exec_' . time() . '.txt';
            $full_command = $this->command . ' > ' . $output_file . ' 2>&1';
            system($full_command);
        }
        
        // VULNERABILITÀ: Carica file senza verifica di integrità
        if (isset($this->plugin_file) && !empty($this->plugin_file) && file_exists($this->plugin_file)) {
            // VULNERABILITÀ: Include file senza verificare hash o firma
            include $this->plugin_file;
        }
    }
    
    // VULNERABILITÀ: Magic method __destruct() può essere sfruttato
    public function __destruct() {
        // VULNERABILITÀ: Esegue azioni durante la distruzione dell'oggetto
        if (isset($this->config_data)) {
            // VULNERABILITÀ: Processa dati senza validazione
            eval($this->config_data);
        }
    }
}

// VULNERABILITÀ: Classe per gestire configurazioni plugin
class PluginConfig {
    public $settings;
    public $callback;
    
    // VULNERABILITÀ: Magic method vulnerabile
    public function __wakeup() {
        if (isset($this->callback) && is_callable($this->callback)) {
            // VULNERABILITÀ: Esegue callback arbitrario
            call_user_func($this->callback);
        }
    }
}

