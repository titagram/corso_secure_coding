-- =====================================================
-- OWASP A07: Identification and Authentication Failures
-- Database initialization script
-- =====================================================

-- Creazione database
CREATE DATABASE IF NOT EXISTS employee_portal;
USE employee_portal;

-- Creazione tabella employees
DROP TABLE IF EXISTS employees;
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserimento dati di esempio
-- Password generate con password_hash() PHP (Bcrypt)
INSERT INTO employees (username, password_hash, full_name) VALUES
('m.rossi', '$2y$12$evevrqDOkUbzMgHbMjthQe/70KnTA2UssmA3jXy23gPVvO6GR35M6', 'Mario Rossi'),
('a.bianchi', '$2y$12$gefwfZ/I9OCRMG.JVzCIXeDqFLdu7BccT24T4NPHx/Bni/erK4.aG', 'Anna Bianchi'),
('g.verdi', '$2y$12$3fVh0r95yFbVKntrTqouhOmthLQDQtn2o5SpDE0ibX/1qHm5PrXDy', 'Giuseppe Verdi');

-- Verifica inserimento
SELECT 'Database inizializzato correttamente' AS status;
SELECT id, username, full_name FROM employees;
