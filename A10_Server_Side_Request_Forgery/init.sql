-- =====================================================
-- DATABASE: Sistema URL Preview
-- VULNERABILITÀ: A10:2021 – Server-Side Request Forgery (SSRF)
-- =====================================================

CREATE DATABASE IF NOT EXISTS url_preview;
USE url_preview;

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
-- TABELLA: url_previews
-- =====================================================
CREATE TABLE url_previews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    url TEXT NOT NULL,
    title VARCHAR(255),
    description TEXT,
    image_url TEXT,
    content_preview TEXT,
    status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABELLA: url_history
-- =====================================================
CREATE TABLE url_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    url TEXT NOT NULL,
    method VARCHAR(50) DEFAULT 'GET',
    response_code INT,
    response_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERIMENTO UTENTI DI TEST
-- =====================================================
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@url-preview.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),  -- password: password
('mario.rossi', 'mario.rossi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),  -- password: password
('lucia.verdi', 'lucia.verdi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');  -- password: password

