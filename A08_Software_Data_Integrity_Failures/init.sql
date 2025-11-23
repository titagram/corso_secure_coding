-- =====================================================
-- DATABASE: Sistema di Gestione Plugin
-- VULNERABILITÀ: A08:2021 – Software and Data Integrity Failures
-- =====================================================

CREATE DATABASE IF NOT EXISTS plugin_system;
USE plugin_system;

-- =====================================================
-- TABELLA: users
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABELLA: plugins
-- =====================================================
CREATE TABLE plugins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    version VARCHAR(20) NOT NULL,
    description TEXT,
    author VARCHAR(100),
    file_path VARCHAR(255),
    config_data TEXT,  -- VULNERABILITÀ: Configurazione serializzata senza verifica
    hash VARCHAR(64),  -- VULNERABILITÀ: Hash presente ma non verificato
    signature TEXT,    -- VULNERABILITÀ: Firma presente ma non verificata
    download_url VARCHAR(255),  -- VULNERABILITÀ: URL non verificato
    is_active BOOLEAN DEFAULT FALSE,
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABELLA: plugin_updates
-- =====================================================
CREATE TABLE plugin_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plugin_id INT NOT NULL,
    version VARCHAR(20) NOT NULL,
    update_url VARCHAR(255) NOT NULL,  -- VULNERABILITÀ: URL non verificato
    hash VARCHAR(64),  -- VULNERABILITÀ: Hash non verificato
    changelog TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERIMENTO UTENTI DI TEST
-- =====================================================
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@plugin-system.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),  -- password: password
('mario.rossi', 'mario.rossi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),  -- password: password
('lucia.verdi', 'lucia.verdi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');  -- password: password

-- =====================================================
-- INSERIMENTO PLUGIN DI ESEMPIO
-- =====================================================
INSERT INTO plugins (name, version, description, author, file_path, config_data, hash, signature, download_url, is_active) VALUES
('analytics', '1.0.0', 'Plugin per analisi statistiche', 'Plugin Team', 'plugins/analytics_v1.0.0.php', 'a:2:{s:4:"api_key";s:10:"test123456";s:6:"enabled";b:1;}', 'abc123def456', 'signature_analytics_v1', 'http://plugins.example.com/analytics_v1.0.0.php', TRUE),
('backup', '2.1.0', 'Plugin per backup automatico', 'Backup Solutions', 'plugins/backup_v2.1.0.php', 'a:3:{s:8:"schedule";s:5:"daily";s:8:"location";s:8:"/backups";s:6:"enabled";b:1;}', 'xyz789abc123', 'signature_backup_v2', 'http://plugins.example.com/backup_v2.1.0.php', FALSE),
('security', '1.5.2', 'Plugin di sicurezza avanzata', 'Security Pro', 'plugins/security_v1.5.2.php', 'a:2:{s:8:"firewall";b:1;s:6:"enabled";b:1;}', 'def456ghi789', 'signature_security_v1', 'http://plugins.example.com/security_v1.5.2.php', TRUE);

-- =====================================================
-- INSERIMENTO AGGIORNAMENTI PLUGIN
-- =====================================================
INSERT INTO plugin_updates (plugin_id, version, update_url, hash, changelog) VALUES
(1, '1.1.0', 'http://plugins.example.com/analytics_v1.1.0.php', 'new_hash_analytics', 'Bug fixes and performance improvements'),
(2, '2.2.0', 'http://plugins.example.com/backup_v2.2.0.php', 'new_hash_backup', 'New features and security updates'),
(3, '1.6.0', 'http://plugins.example.com/security_v1.6.0.php', 'new_hash_security', 'Critical security patches');

