-- Creazione del database
CREATE DATABASE IF NOT EXISTS `timetable-generator`;
USE `timetable-generator`;

-- Tabella users
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `nome` varchar(30) DEFAULT NULL,
    `cognome` varchar(30) DEFAULT NULL,
    `type` enum('user','admin') NOT NULL DEFAULT 'user',
    `profile_path` varchar(255) DEFAULT NULL,
    `last_login` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserimento utente admin di default
INSERT INTO `users` (`username`, `email`, `password`, `type`) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- La password è 'password' 