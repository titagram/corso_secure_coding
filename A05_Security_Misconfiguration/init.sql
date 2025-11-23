-- =====================================================
-- OWASP A05:2021 - Security Misconfiguration
-- Sistema di Gestione Documentale Vulnerabile
-- =====================================================

DROP DATABASE IF EXISTS document_system;
CREATE DATABASE document_system;
USE document_system;

-- Tabella utenti
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella documenti
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100),
    description TEXT,
    is_private BOOLEAN DEFAULT FALSE,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERIMENTO UTENTI
-- =====================================================
-- VULNERABILITÀ: Credenziali di default non modificate!
INSERT INTO users (username, password_hash, email, full_name, role) VALUES
('admin', '$2y$10$yW1qWFuaWc9tHvY7bKGJ4.ZbucFhKDaGN18q5iEG2l9LPKzipCkW.', 'admin@documents.local', 'Amministratore', 'admin'),
-- Password: admin123

('mario.rossi', '$2y$10$IQjv/vXvJgUTKfVvCvip1.WCcRFbN8NVyrymqqwWedKPiBFjrIAsW', 'mario.rossi@email.com', 'Mario Rossi', 'user'),
-- Password: alice123

('luisa.verdi', '$2y$10$PaguDlcJmsIH.ht1jtenueeY9369KLfu3cGWirbr7Nd6CbIPcIp0e', 'luisa.verdi@email.com', 'Luisa Verdi', 'user');
-- Password: manager123

-- Messaggio di conferma
SELECT 'Database document_system inizializzato con successo!' AS status;
SELECT COUNT(*) AS total_users FROM users;

