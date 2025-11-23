-- =====================================================
-- DATABASE: Sistema Vault - Risorse Sensibili
-- VULNERABILITÀ: A09:2021 – Security Logging and Monitoring Failures
-- =====================================================

CREATE DATABASE IF NOT EXISTS vault_system;
USE vault_system;

-- =====================================================
-- TABELLA: users
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'auditor') DEFAULT 'user',
    failed_login_attempts INT DEFAULT 0,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABELLA: resources
-- =====================================================
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('document', 'key', 'secret', 'credential', 'financial') NOT NULL,
    content TEXT,  -- VULNERABILITÀ: Contenuto sensibile in chiaro
    access_level ENUM('public', 'restricted', 'confidential', 'top_secret') DEFAULT 'restricted',
    owner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABELLA: access_logs
-- =====================================================
-- VULNERABILITÀ: Tabella log presente ma non utilizzata correttamente
CREATE TABLE access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    resource_id INT,
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    success BOOLEAN DEFAULT TRUE,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABELLA: transactions
-- =====================================================
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT,
    transaction_type ENUM('access', 'modify', 'delete', 'transfer', 'export') NOT NULL,
    amount DECIMAL(10, 2) DEFAULT 0.00,
    description TEXT,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERIMENTO UTENTI DI TEST
-- =====================================================
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@vault-system.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),  -- password: password
('mario.rossi', 'mario.rossi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),  -- password: password
('lucia.verdi', 'lucia.verdi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),  -- password: password
('auditor', 'auditor@vault-system.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'auditor');  -- password: password

-- =====================================================
-- INSERIMENTO RISORSE SENSIBILI
-- =====================================================
INSERT INTO resources (name, description, category, content, access_level, owner_id) VALUES
('Chiave API Produzione', 'Chiave API per servizi di produzione', 'key', 'sk_live_1234567890abcdefghijklmnopqrstuvwxyz', 'top_secret', 1),
('Password Database', 'Credenziali database principale', 'credential', 'username: db_admin, password: SuperSecret123!', 'top_secret', 1),
('Documento Riservato A', 'Documento classificato livello A', 'document', 'Contenuto riservato: Progetto Alpha - Budget 2024: €1,500,000', 'confidential', 1),
('Chiave SSH Server', 'Chiave privata SSH per server produzione', 'key', '-----BEGIN RSA PRIVATE KEY-----\nMIIEpAIBAAKCAQEA...', 'top_secret', 1),
('Token JWT Segreto', 'Token JWT per autenticazione API', 'secret', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...', 'top_secret', 1),
('Dati Finanziari Cliente X', 'Informazioni finanziarie riservate', 'financial', 'Account: 1234567890, Balance: €250,000, Credit Card: 4532-1234-5678-9010', 'confidential', 2),
('Password Admin Sistema', 'Password amministratore sistema legacy', 'credential', 'admin:AdminPass2024!', 'top_secret', 1),
('Documento Riservato B', 'Documento classificato livello B', 'document', 'Contenuto riservato: Strategia Q4 2024 - Target: +30% revenue', 'restricted', 2);

-- =====================================================
-- INSERIMENTO LOG DI ESEMPIO (pochi, incompleti)
-- =====================================================
-- VULNERABILITÀ: Solo alcuni eventi loggati, molti mancanti
INSERT INTO access_logs (user_id, resource_id, action, ip_address, success, details) VALUES
(1, 1, 'access', '192.168.1.100', TRUE, 'Accesso autorizzato'),
(2, 6, 'access', '192.168.1.101', TRUE, 'Accesso autorizzato'),
(1, 3, 'modify', '192.168.1.100', TRUE, 'Documento modificato');
-- VULNERABILITÀ: Nessun log per tentativi di accesso falliti, accessi non autorizzati, etc.

-- =====================================================
-- INSERIMENTO TRANSAZIONI DI ESEMPIO
-- =====================================================
INSERT INTO transactions (user_id, resource_id, transaction_type, amount, description, status) VALUES
(1, 1, 'access', 0.00, 'Accesso a chiave API', 'completed'),
(2, 6, 'access', 0.00, 'Accesso a dati finanziari', 'completed'),
(1, 3, 'modify', 0.00, 'Modifica documento riservato', 'completed');
-- VULNERABILITÀ: Transazioni ad alto valore non loggate, nessun alert

