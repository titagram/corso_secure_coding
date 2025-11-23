-- =====================================================
-- OWASP A06:2021 - Vulnerable and Outdated Components
-- Sistema Blog Vulnerabile
-- =====================================================

DROP DATABASE IF EXISTS blog_system;
CREATE DATABASE blog_system;
USE blog_system;

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

-- Tabella post del blog
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella commenti
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT,
    author_name VARCHAR(100),
    author_email VARCHAR(100),
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERIMENTO UTENTI
-- =====================================================
INSERT INTO users (username, password_hash, email, full_name, role) VALUES
('admin', '$2y$10$yW1qWFuaWc9tHvY7bKGJ4.ZbucFhKDaGN18q5iEG2l9LPKzipCkW.', 'admin@blog.local', 'Amministratore', 'admin'),
-- Password: admin123

('mario.rossi', '$2y$10$IQjv/vXvJgUTKfVvCvip1.WCcRFbN8NVyrymqqwWedKPiBFjrIAsW', 'mario.rossi@email.com', 'Mario Rossi', 'user');
-- Password: alice123

-- =====================================================
-- INSERIMENTO POST DI ESEMPIO
-- =====================================================
INSERT INTO posts (user_id, title, content, excerpt, status) VALUES
(1, 'Benvenuto nel Blog', 'Questo è il primo post del blog. Il sistema utilizza componenti obsoleti e vulnerabili per scopi didattici.', 'Primo post del blog', 'published'),
(1, 'Sicurezza Informatica', 'La sicurezza informatica è fondamentale. È importante mantenere aggiornati tutti i componenti del sistema.', 'Articolo sulla sicurezza', 'published'),
(2, 'PHP e MySQL', 'PHP e MySQL sono tecnologie potenti ma devono essere mantenute aggiornate per evitare vulnerabilità.', 'Tecnologie web', 'published');

-- =====================================================
-- INSERIMENTO COMMENTI DI ESEMPIO (con payload XSS per test)
-- =====================================================
INSERT INTO comments (post_id, user_id, author_name, author_email, content) VALUES
(1, NULL, 'Test User', 'test@test.com', 'Ottimo articolo!'),
(1, NULL, 'Hacker', 'hacker@test.com', '<img src=x onerror="alert(\'XSS con jQuery 1.7.2!\')">'),
(1, NULL, 'Security Researcher', 'security@test.com', '<script>alert("XSS Test")</script>');

-- Messaggio di conferma
SELECT 'Database blog_system inizializzato con successo!' AS status;
SELECT 'VULNERABILITÀ: MySQL 5.7 non è più supportato e contiene vulnerabilità note!' AS warning;
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_posts FROM posts;

