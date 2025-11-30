-- Users tábla létrehozása a filmdb adatbázisban
-- Ez a tábla tárolja a regisztrált felhasználókat

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `jelszo` varchar(255) NOT NULL,
  `profilkep_url` varchar(255) DEFAULT NULL,
  `letrehozva` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index az email mezőre a gyorsabb kereséshez
CREATE INDEX idx_email ON users(email);

-- Példa felhasználó hozzáadása (opcionális)
-- Jelszó: password123
INSERT INTO `users` (`nev`, `email`, `jelszo`) VALUES
('Teszt Felhasználó', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
