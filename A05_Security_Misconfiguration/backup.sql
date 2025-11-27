-- VULNERABILITÀ: File di backup esposto pubblicamente!
-- Questo file contiene dati sensibili e NON dovrebbe essere accessibile via web
-- Backup generato il: 2024-11-23 10:00:00

-- Database: document_system
-- Backup completo con dati utenti

USE document_system;

-- Backup tabella users (VULNERABILE: contiene password hash!)
INSERT INTO users (id, username, password_hash, email, full_name, role, created_at) VALUES
(1, 'admin', '$2y$10$yW1qWFuaWc9tHvY7bKGJ4.ZbucFhKDaGN18q5iEG2l9LPKzipCkW.', 'admin@documents.local', 'Amministratore', 'admin', '2024-01-01 00:00:00'),
(2, 'mario.rossi', '$2y$10$IQjv/vXvJgUTKfVvCvip1.WCcRFbN8NVyrymqqwWedKPiBFjrIAsW', 'mario.rossi@email.com', 'Mario Rossi', 'user', '2024-01-15 10:30:00'),
(3, 'luisa.verdi', '$2y$10$PaguDlcJmsIH.ht1jtenueeY9369KLfu3cGWirbr7Nd6CbIPcIp0e', 'luisa.verdi@email.com', 'Luisa Verdi', 'user', '2024-02-20 14:45:00');

-- Backup tabella documents
INSERT INTO documents (id, user_id, filename, original_filename, file_path, file_size, file_type, description, is_private, uploaded_at) VALUES
(1, 2, 'doc_001.pdf', 'Contratto_Cliente_ABC.pdf', '/uploads/doc_001.pdf', 245678, 'application/pdf', 'Contratto importante con cliente ABC', 1, '2024-11-20 09:15:00'),
(2, 3, 'doc_002.docx', 'Report_Mensile.docx', '/uploads/doc_002.docx', 156789, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'Report mensile vendite', 0, '2024-11-21 11:30:00');

-- Note: Questo backup contiene dati sensibili e non dovrebbe essere accessibile pubblicamente!

