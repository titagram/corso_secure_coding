-- Database di inizializzazione per il laboratorio OWASP A03: Injection
-- Questo database contiene dati di esempio per dimostrare vulnerabilità SQL Injection

USE a03_db;

-- Tabella dei prodotti
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL
);

-- Inserimento di prodotti di esempio
INSERT INTO products (name, description, price) VALUES
    ('Laptop', 'Laptop aziendale ad alte prestazioni, 16GB RAM, 512GB SSD', 1299.99),
    ('Mouse', 'Mouse wireless ergonomico con batteria ricaricabile', 29.99),
    ('Keyboard', 'Tastiera meccanica retroilluminata RGB', 89.99),
    ('Monitor', 'Monitor 27 pollici 4K UHD con tecnologia IPS', 449.99),
    ('Webcam', 'Webcam HD 1080p con microfono integrato', 79.99);

-- Tabella degli utenti (con dati sensibili!)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    email VARCHAR(100)
);

-- Inserimento di utenti di esempio con HASH DEBOLI facilmente rompibili con hashcat
-- Questi hash sono intenzionalmente deboli per scopi didattici
-- 
-- Come rompere con hashcat:
-- MD5:    hashcat -m 0 hash.txt rockyou.txt
-- SHA1:   hashcat -m 100 hash.txt rockyou.txt
-- SHA256: hashcat -m 1400 hash.txt rockyou.txt
-- NTLM:   hashcat -m 1000 hash.txt rockyou.txt
-- bcrypt: hashcat -m 3200 hash.txt rockyou.txt (molto lento, ma dimostra che anche algoritmi "forti" sono deboli con password semplici)
--
-- Password reali (per verificare dopo il cracking):
-- admin:    password (MD5)
-- b.rossi:  123456 (SHA1)
-- m.verdi:  admin (MD5)
-- s.bianchi: qwerty (SHA256)
-- g.neri:   welcome (MD5)

INSERT INTO users (username, password_hash, role, email) VALUES
    ('admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'administrator', 'admin@azienda.local'),
    -- MD5("password") - hashcat mode 0
    ('b.rossi', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'user', 'bruno.rossi@azienda.local'),
    -- SHA1("123456") - hashcat mode 100
    ('m.verdi', '21232f297a57a5a743894a0e4a801fc3', 'user', 'mario.verdi@azienda.local'),
    -- MD5("admin") - hashcat mode 0
    ('s.bianchi', '65e84be33532fb784c48129675f9eff3a682b27168c0ea744b2cf58ee02337c5', 'user', 'sara.bianchi@azienda.local'),
    -- SHA256("qwerty") - hashcat mode 1400
    ('g.neri', '40be4e59b9a2a2b5dffb918c0e86b3d7', 'user', 'giuseppe.neri@azienda.local');
    -- MD5("welcome") - hashcat mode 0

-- Messaggio di conferma
SELECT 'Database a03_db inizializzato con successo!' AS status;
