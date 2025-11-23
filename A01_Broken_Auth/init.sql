-- Database per l'applicazione di Gestione Fatture (volutamente vulnerabile)
-- Corso Secure Coding - OWASP A01: Broken Access Control

DROP DATABASE IF EXISTS invoicing_app;
CREATE DATABASE invoicing_app;
USE invoicing_app;

-- Tabella utenti
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'manager', 'admin') NOT NULL DEFAULT 'user'
);

-- Tabella fatture
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    details TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Inserimento utenti di esempio
-- Note: le password sono hashate con password_hash() di PHP
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$yW1qWFuaWc9tHvY7bKGJ4.ZbucFhKDaGN18q5iEG2l9LPKzipCkW.', 'admin'),     -- password: admin123
('manager', '$2y$10$PaguDlcJmsIH.ht1jtenueeY9369KLfu3cGWirbr7Nd6CbIPcIp0e', 'manager'), -- password: manager123
('alice', '$2y$10$IQjv/vXvJgUTKfVvCvip1.WCcRFbN8NVyrymqqwWedKPiBFjrIAsW', 'user'),     -- password: alice123
('bob', '$2y$10$EhgldoOSiwVxZvPgPy9/DuCmWz5qfwFak.Ltm6wXyB9eeOmhSIWdO', 'user');       -- password: bob123

-- Inserimento fatture di esempio
INSERT INTO invoices (user_id, amount, details, status) VALUES
(3, 100.00, 'Consulenza mese di Ottobre', 'pending'),
(3, 250.00, 'Sviluppo sito web', 'paid'),
(4, 500.00, 'Formazione aziendale', 'pending');
