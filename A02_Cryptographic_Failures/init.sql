-- =====================================================
-- OWASP A02:2021 - Cryptographic Failures
-- Database E-Commerce Vulnerabile
-- =====================================================

DROP DATABASE IF EXISTS ecommerce_db;
CREATE DATABASE ecommerce_db;
USE ecommerce_db;

-- Tabella utenti con password hashate con algoritmi DEBOLI
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(10),
    tax_code VARCHAR(16),  -- Codice Fiscale in CHIARO
    date_of_birth DATE,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella carte di credito (VULNERABILE: dati in CHIARO!)
CREATE TABLE credit_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    card_number VARCHAR(19) NOT NULL,  -- IN CHIARO!
    cardholder_name VARCHAR(100) NOT NULL,
    expiry_month INT NOT NULL,
    expiry_year INT NOT NULL,
    cvv VARCHAR(3) NOT NULL,  -- IN CHIARO! (violazione PCI-DSS)
    card_type VARCHAR(20),
    added_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella sessioni (VULNERABILE: token prevedibili)
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(64) NOT NULL,  -- Prevedibile!
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella prodotti
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    category VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella ordini
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella dettagli ordine
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERIMENTO UTENTI CON PASSWORD HASHATE DEBOLI
-- =====================================================
-- NOTA: Questi hash sono intenzionalmente deboli per scopi didattici
-- Gli studenti dovranno identificare e rompere questi hash con hashcat

-- Utenti con MD5 (hashcat mode 0) - SENZA SALT
-- Password reali facilmente craccabili
INSERT INTO users (username, password_hash, email, full_name, phone, address, city, postal_code, tax_code, date_of_birth) VALUES
-- MD5("password") - hashcat -m 0
('mario.rossi', '5f4dcc3b5aa765d61d8327deb882cf99', 'mario.rossi@email.com', 'Mario Rossi', '+39 333 1234567', 'Via Roma 123', 'Milano', '20100', 'RSSMRA80A01F205X', '1980-01-01'),
-- MD5("123456") - hashcat -m 0
('luisa.verdi', 'e10adc3949ba59abbe56e057f20f883e', 'luisa.verdi@email.com', 'Luisa Verdi', '+39 334 2345678', 'Via Garibaldi 45', 'Roma', '00100', 'VRDLSU75B15H501Y', '1975-02-15'),
-- MD5("admin") - hashcat -m 0
('admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@ecommerce.local', 'Amministratore Sistema', '+39 335 3456789', 'Via Admin 1', 'Torino', '10100', 'DMNSTR90A01A001A', '1990-01-01'),
-- MD5("qwerty") - hashcat -m 0
('giulia.bianchi', 'd8578edf8458ce06fbc5bb76a58c5ca4', 'giulia.bianchi@email.com', 'Giulia Bianchi', '+39 336 4567890', 'Via Verdi 78', 'Firenze', '50100', 'BNCGLI88C52F205Z', '1988-03-22'),
-- MD5("welcome") - hashcat -m 0
('paolo.neri', '40be4e59b9a2a2b5dffb918c0e86b3d7', 'paolo.neri@email.com', 'Paolo Neri', '+39 337 5678901', 'Via Neri 12', 'Bologna', '40100', 'NRIPLA85D15L219W', '1985-04-15');

-- Utenti con SHA1 (hashcat mode 100) - SENZA SALT
INSERT INTO users (username, password_hash, email, full_name, phone, address, city, postal_code, tax_code, date_of_birth) VALUES
-- SHA1("password123") - hashcat -m 100
('anna.ferrari', 'cbfdac6008f9cab4083784cbd1874f76618d2a97', 'anna.ferrari@email.com', 'Anna Ferrari', '+39 338 6789012', 'Via Ferrari 34', 'Napoli', '80100', 'FRRNNA92E55F205A', '1992-05-05'),
-- SHA1("letmein") - hashcat -m 100
('marco.russo', '0d107d09f5bbe40cade3de5c71e9e9b7efbe2ced', 'marco.russo@email.com', 'Marco Russo', '+39 339 7890123', 'Via Russo 56', 'Palermo', '90100', 'RSSMRC87F10A001B', '1987-06-10'),
-- SHA1("monkey") - hashcat -m 100
('sara.romano', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', 'sara.romano@email.com', 'Sara Romano', '+39 340 8901234', 'Via Romano 90', 'Genova', '16100', 'RMNSRA89G20H501C', '1989-07-20');

-- Utenti con SHA256 (hashcat mode 1400) - SENZA SALT
INSERT INTO users (username, password_hash, email, full_name, phone, address, city, postal_code, tax_code, date_of_birth) VALUES
-- SHA256("password") - hashcat -m 1400
('luca.colombo', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', 'luca.colombo@email.com', 'Luca Colombo', '+39 341 9012345', 'Via Colombo 11', 'Venezia', '30100', 'CLMLCU91H15A001D', '1991-08-15'),
-- SHA256("12345678") - hashcat -m 1400
('elena.ricci', 'ef797c8118f02dfb649607dd5d3f8c7623048c9c063d532cc95c5ed7a898a64f', 'elena.ricci@email.com', 'Elena Ricci', '+39 342 0123456', 'Via Ricci 22', 'Verona', '37100', 'RCCLNE93I25F205E', '1993-09-25');

-- Utenti con MD5 + SALT STATICO (ancora vulnerabile!)
-- Salt: "ecommerce2024" (hardcoded nel codice)
INSERT INTO users (username, password_hash, email, full_name, phone, address, city, postal_code, tax_code, date_of_birth) VALUES
-- MD5("password" + "ecommerce2024") - hashcat -m 0 con regole
('francesco.marino', 'a8f5f167f44f4964e6c998dee827110c', 'francesco.marino@email.com', 'Francesco Marino', '+39 343 1234567', 'Via Marino 33', 'Bari', '70100', 'MRNFNC94L10A001F', '1994-10-10'),
-- MD5("admin123" + "ecommerce2024")
('valentina.gallo', 'b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8', 'valentina.gallo@email.com', 'Valentina Gallo', '+39 344 2345678', 'Via Gallo 44', 'Catania', '95100', 'GLLVLN95M15H501G', '1995-11-15');

-- Utenti con BCRYPT ma password DEBOLI (facilmente craccabili con hashcat -m 3200)
-- Questi dimostrano che anche algoritmi "forti" sono vulnerabili con password semplici
INSERT INTO users (username, password_hash, email, full_name, phone, address, city, postal_code, tax_code, date_of_birth) VALUES
-- bcrypt("password") - hashcat -m 3200 (lento ma fattibile con password semplici)
('davide.conti', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'davide.conti@email.com', 'Davide Conti', '+39 345 3456789', 'Via Conti 55', 'Padova', '35100', 'CNTDVD96N20A001H', '1996-12-20'),
-- bcrypt("123456") - hashcat -m 3200
('chiara.leone', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'chiara.leone@email.com', 'Chiara Leone', '+39 346 4567890', 'Via Leone 66', 'Trieste', '34100', 'LNECHR97O25F205I', '1997-01-25'),
-- bcrypt("admin") - hashcat -m 3200
-- Nota: Hash bcrypt valido per password debole (facilmente craccabile)
('andrea.martini', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'andrea.martini@email.com', 'Andrea Martini', '+39 347 5678901', 'Via Martini 77', 'Brescia', '25100', 'MRTNDR98P30A001J', '1998-02-28');

-- =====================================================
-- INSERIMENTO CARTE DI CREDITO (IN CHIARO!)
-- =====================================================
-- VULNERABILITÀ CRITICA: Carte di credito memorizzate in plaintext
-- Include anche CVV (violazione PCI-DSS)

INSERT INTO credit_cards (user_id, card_number, cardholder_name, expiry_month, expiry_year, cvv, card_type) VALUES
(1, '4532015112830366', 'MARIO ROSSI', 12, 2025, '123', 'Visa'),
(1, '5500000000000004', 'MARIO ROSSI', 6, 2026, '456', 'Mastercard'),
(2, '4111111111111111', 'LUISA VERDI', 3, 2025, '789', 'Visa'),
(3, '378282246310005', 'AMMINISTRATORE SISTEMA', 9, 2027, '321', 'American Express'),
(4, '5105105105105100', 'GIULIA BIANCHI', 11, 2025, '654', 'Mastercard'),
(5, '4242424242424242', 'PAOLO NERI', 2, 2026, '987', 'Visa'),
(6, '5555555555554444', 'ANNA FERRARI', 7, 2025, '147', 'Mastercard'),
(7, '4000000000000002', 'MARCO RUSSO', 4, 2026, '258', 'Visa'),
(8, '371449635398431', 'SARA ROMANO', 8, 2025, '369', 'American Express'),
(9, '6011111111111117', 'LUCA COLOMBO', 1, 2027, '741', 'Discover'),
(10, '3056930009020004', 'ELENA RICCI', 5, 2026, '852', 'Diners Club'),
(11, '4000000000000010', 'FRANCESCO MARINO', 10, 2025, '963', 'Visa'),
(12, '4000000000000028', 'VALENTINA GALLO', 12, 2026, '159', 'Visa'),
(13, '4000000000000036', 'DAVIDE CONTI', 6, 2025, '357', 'Visa'),
(14, '4000000000000044', 'CHIARA LEONE', 3, 2027, '753', 'Visa'),
(15, '4000000000000051', 'ANDREA MARTINI', 9, 2026, '951', 'Visa');

-- =====================================================
-- INSERIMENTO PRODOTTI
-- =====================================================
INSERT INTO products (name, description, price, stock, category) VALUES
('Smartphone Pro Max', 'Smartphone di ultima generazione con display 6.7", 256GB', 899.99, 50, 'Elettronica'),
('Laptop Business', 'Laptop aziendale 16GB RAM, 512GB SSD, Intel i7', 1299.99, 30, 'Informatica'),
('Cuffie Wireless', 'Cuffie Bluetooth con cancellazione attiva del rumore', 199.99, 100, 'Audio'),
('Smartwatch Sport', 'Smartwatch con GPS, monitoraggio fitness, resistente all''acqua', 299.99, 75, 'Wearable'),
('Tablet 10 pollici', 'Tablet Android con display Full HD, 128GB', 449.99, 60, 'Elettronica'),
('Tastiera Meccanica', 'Tastiera meccanica RGB retroilluminata, switch blu', 89.99, 120, 'Accessori'),
('Mouse Gaming', 'Mouse ottico gaming 16000 DPI, RGB', 59.99, 150, 'Accessori'),
('Monitor 27" 4K', 'Monitor IPS 27 pollici, risoluzione 4K UHD', 449.99, 40, 'Monitor'),
('Webcam HD', 'Webcam Full HD 1080p con microfono integrato', 79.99, 80, 'Accessori'),
('SSD 1TB', 'SSD NVMe M.2 1TB, velocità lettura 3500MB/s', 129.99, 100, 'Componenti');

-- =====================================================
-- INSERIMENTO ORDINI
-- =====================================================
INSERT INTO orders (user_id, order_date, total_amount, status, shipping_address, payment_method) VALUES
(1, '2024-01-15 10:30:00', 899.99, 'completed', 'Via Roma 123, Milano, 20100', 'credit_card'),
(1, '2024-02-20 14:45:00', 199.99, 'shipped', 'Via Roma 123, Milano, 20100', 'credit_card'),
(2, '2024-01-22 09:15:00', 1299.99, 'completed', 'Via Garibaldi 45, Roma, 00100', 'credit_card'),
(3, '2024-03-10 16:20:00', 449.99, 'pending', 'Via Admin 1, Torino, 10100', 'credit_card'),
(4, '2024-02-05 11:00:00', 299.99, 'completed', 'Via Verdi 78, Firenze, 50100', 'credit_card'),
(5, '2024-03-18 13:30:00', 89.99, 'shipped', 'Via Neri 12, Bologna, 40100', 'credit_card'),
(6, '2024-01-28 15:45:00', 59.99, 'completed', 'Via Ferrari 34, Napoli, 80100', 'credit_card'),
(7, '2024-02-12 10:00:00', 449.99, 'pending', 'Via Russo 56, Palermo, 90100', 'credit_card'),
(8, '2024-03-05 12:15:00', 79.99, 'completed', 'Via Romano 90, Genova, 16100', 'credit_card'),
(9, '2024-01-30 14:00:00', 129.99, 'shipped', 'Via Colombo 11, Venezia, 30100', 'credit_card');

-- Dettagli ordini
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 899.99),
(2, 3, 1, 199.99),
(3, 2, 1, 1299.99),
(4, 5, 1, 449.99),
(5, 4, 1, 299.99),
(6, 6, 1, 89.99),
(7, 7, 1, 59.99),
(8, 8, 1, 449.99),
(9, 9, 1, 79.99),
(10, 10, 1, 129.99);

-- =====================================================
-- INSERIMENTO SESSIONI (con token prevedibili)
-- =====================================================
-- VULNERABILITÀ: Token basati su username + timestamp (facilmente prevedibili)
INSERT INTO sessions (user_id, session_token, created_at, expires_at) VALUES
(1, 'mario.rossi_20240115103000', '2024-01-15 10:30:00', '2024-01-15 18:30:00'),
(2, 'luisa.verdi_20240122091500', '2024-01-22 09:15:00', '2024-01-22 17:15:00'),
(3, 'admin_20240310162000', '2024-03-10 16:20:00', '2024-03-11 00:20:00');

-- Messaggio di conferma
SELECT 'Database ecommerce_db inizializzato con successo!' AS status;
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_credit_cards FROM credit_cards;
SELECT COUNT(*) AS total_products FROM products;
SELECT COUNT(*) AS total_orders FROM orders;

