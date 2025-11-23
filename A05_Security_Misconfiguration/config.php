<?php
/**
 * File di Configurazione
 * OWASP A05: Security Misconfiguration
 * 
 * VULNERABILITÀ: File di configurazione esposto pubblicamente!
 * Questo file contiene informazioni sensibili e NON dovrebbe essere accessibile via web
 */

// VULNERABILITÀ: Debug mode attivo in produzione!
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);
define('LOG_ERRORS', true);
define('ERROR_REPORTING', E_ALL);

// Database Configuration
define('DB_HOST', 'db');
define('DB_NAME', 'document_system');
define('DB_USER', 'root');
define('DB_PASSWORD', 'rootpassword');

// Application Settings
define('APP_NAME', 'Document Management System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'production'); // VULNERABILITÀ: Dovrebbe essere 'production' ma con debug disabilitato!

// Security Settings (VULNERABILI!)
define('API_KEY', 'sk_live_1234567890abcdefghijklmnop');
define('SECRET_KEY', 'my_super_secret_key_12345_do_not_share');
define('ENCRYPTION_KEY', 'encryption_key_for_sensitive_data_12345');

// File Upload Settings
define('UPLOAD_DIR', '/var/www/html/uploads');
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png']);

// Session Settings
define('SESSION_LIFETIME', 7200);
define('SESSION_NAME', 'DOCSESSID');

// Admin Panel (VULNERABILE: accessibile pubblicamente!)
define('ADMIN_PATH', '/admin/');
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // VULNERABILITÀ: Password hardcoded!

// Logging
define('LOG_FILE', '/var/www/html/logs/app.log');
define('LOG_LEVEL', 'DEBUG'); // VULNERABILITÀ: Dovrebbe essere 'ERROR' in produzione!

// Error Display (VULNERABILITÀ CRITICA!)
if (DISPLAY_ERRORS) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(ERROR_REPORTING);
}

