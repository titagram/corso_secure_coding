-- =====================================================
-- OWASP A04:2021 - Insecure Design
-- Sistema di Prenotazioni Vulnerabile
-- =====================================================

DROP DATABASE IF EXISTS booking_system;
CREATE DATABASE booking_system;
USE booking_system;

-- Tabella utenti
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella servizi/prenotabili (es. tavoli ristorante, stanze hotel, eventi)
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    base_price DECIMAL(10, 2) NOT NULL,
    max_capacity INT NOT NULL,
    service_type VARCHAR(50), -- 'restaurant', 'hotel', 'event'
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella prenotazioni
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    number_of_guests INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    discount_code VARCHAR(50),
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    payment_amount DECIMAL(10, 2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella coupon/sconti
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_percent DECIMAL(5, 2),
    discount_amount DECIMAL(10, 2),
    min_purchase DECIMAL(10, 2),
    max_uses INT DEFAULT NULL, -- NULL = illimitato
    used_count INT DEFAULT 0,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella utilizzo coupon (per tracciare chi ha usato quale coupon)
CREATE TABLE coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_id INT NOT NULL,
    used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERIMENTO UTENTI
-- =====================================================
INSERT INTO users (username, password_hash, email, full_name, phone, role) VALUES
('admin', '$2y$10$yW1qWFuaWc9tHvY7bKGJ4.ZbucFhKDaGN18q5iEG2l9LPKzipCkW.', 'admin@booking.local', 'Amministratore', '+39 333 1111111', 'admin'),
('mario.rossi', '$2y$10$IQjv/vXvJgUTKfVvCvip1.WCcRFbN8NVyrymqqwWedKPiBFjrIAsW', 'mario.rossi@email.com', 'Mario Rossi', '+39 333 2222222', 'user'),
('luisa.verdi', '$2y$10$PaguDlcJmsIH.ht1jtenueeY9369KLfu3cGWirbr7Nd6CbIPcIp0e', 'luisa.verdi@email.com', 'Luisa Verdi', '+39 333 3333333', 'user'),
('giovanni.bianchi', '$2y$10$EhgldoOSiwVxZvPgPy9/DuCmWz5qfwFak.Ltm6wXyB9eeOmhSIWdO', 'giovanni.bianchi@email.com', 'Giovanni Bianchi', '+39 333 4444444', 'user');

-- Password: admin123, alice123, manager123, bob123 (bcrypt)

-- =====================================================
-- INSERIMENTO SERVIZI
-- =====================================================
INSERT INTO services (name, description, base_price, max_capacity, service_type) VALUES
('Tavolo Vista Mare', 'Tavolo esclusivo con vista panoramica sul mare', 50.00, 4, 'restaurant'),
('Tavolo Romantico', 'Tavolo per due con atmosfera intima', 35.00, 2, 'restaurant'),
('Sala Privata', 'Sala riservata per eventi privati', 200.00, 20, 'restaurant'),
('Suite Deluxe', 'Camera suite di lusso con balcone', 150.00, 2, 'hotel'),
('Camera Standard', 'Camera confortevole con vista giardino', 80.00, 2, 'hotel'),
('Evento Serale', 'Biglietto per evento serale con cena', 75.00, 1, 'event'),
('Workshop Culinario', 'Prenotazione per workshop di cucina', 120.00, 1, 'event'),
('Degustazione Vini', 'Serata di degustazione vini premium', 60.00, 1, 'event');

-- =====================================================
-- INSERIMENTO COUPON
-- =====================================================
INSERT INTO coupons (code, discount_percent, discount_amount, min_purchase, max_uses, valid_from, valid_until) VALUES
('WELCOME10', 10.00, NULL, 50.00, NULL, '2024-01-01', '2024-12-31'), -- 10% sconto, nessun limite utilizzi
('SUMMER20', 20.00, NULL, 100.00, 100, '2024-06-01', '2024-08-31'), -- 20% sconto, max 100 utilizzi totali
('VIP50', 50.00, NULL, 200.00, 10, '2024-01-01', '2024-12-31'), -- 50% sconto, max 10 utilizzi totali
('FIXED15', NULL, 15.00, 30.00, NULL, '2024-01-01', '2024-12-31'), -- 15€ sconto fisso, nessun limite
('FIRST5', 5.00, NULL, 0.00, 1, '2024-01-01', '2024-12-31'); -- 5% sconto, 1 solo utilizzo per utente

-- =====================================================
-- INSERIMENTO PRENOTAZIONI DI ESEMPIO
-- =====================================================
INSERT INTO bookings (user_id, service_id, booking_date, booking_time, number_of_guests, total_price, discount_code, discount_amount, status, payment_status, payment_amount) VALUES
(2, 1, '2024-12-25', '20:00:00', 4, 50.00, NULL, 0.00, 'confirmed', 'paid', 50.00),
(3, 2, '2024-12-24', '19:30:00', 2, 35.00, 'WELCOME10', 3.50, 'confirmed', 'paid', 31.50),
(4, 4, '2024-12-26', '14:00:00', 2, 150.00, NULL, 0.00, 'pending', 'unpaid', 0.00);

-- Messaggio di conferma
SELECT 'Database booking_system inizializzato con successo!' AS status;
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_services FROM services;
SELECT COUNT(*) AS total_coupons FROM coupons;
SELECT COUNT(*) AS total_bookings FROM bookings;

