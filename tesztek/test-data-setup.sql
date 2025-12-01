-- Teszt adatok beállítása a Postman tesztekhez
-- Ez a script biztosítja, hogy a tesztekben használt ID-k létezzenek

-- 1. Teszt felhasználó törlése (ha létezik) a Register teszt miatt
DELETE FROM felhasznalo WHERE email = 'teszt@cinematar.hu' OR felhasznalonev = 'teszt_user';

-- 2. Admin és Moderátor felhasználók biztosítása
-- Admin felhasználó frissítése (ha létezik)
UPDATE felhasznalo 
SET jelszo = '$2y$10$Fm7OjHa2cuE9Z8mPAnkoquoHaVQiCRWFXM0Uz4BsyP99l9gmcHecG', -- jelszó: admin123
    aktiv = 1,
    jogosultsag = 'admin'
WHERE email = 'admin@cinematar.hu';

-- Admin létrehozása (ha nem létezik)
INSERT IGNORE INTO felhasznalo (felhasznalonev, email, jelszo, jogosultsag, aktiv)
VALUES ('Admin', 'admin@cinematar.hu', '$2y$10$Fm7OjHa2cuE9Z8mPAnkoquoHaVQiCRWFXM0Uz4BsyP99l9gmcHecG', 'admin', 1);

-- Moderátor felhasználó
INSERT IGNORE INTO felhasznalo (felhasznalonev, email, jelszo, jogosultsag, aktiv)
VALUES ('Moderator', 'moderator@cinematar.hu', '$2y$10$6SmBK6VIkS2HGFOVT68GTu5bV4Nj8KeuX9VZcymGfbIhzTGEzRXvC', 'moderator', 1);

-- Moderátor visszaállítása eredeti névre és jelszóra (ha módosítva lett)
UPDATE felhasznalo 
SET felhasznalonev = 'Moderator',
    jelszo = '$2y$10$6SmBK6VIkS2HGFOVT68GTu5bV4Nj8KeuX9VZcymGfbIhzTGEzRXvC'
WHERE email = 'moderator@cinematar.hu';

-- 3. Film ID=1 létrehozása/biztosítása (ha nem létezik)
INSERT INTO film (film_id, cim, idotartam, poszter_url, leiras, kiadasi_ev)
VALUES (1, 'Teszt Film 1', 120, 'https://example.com/poster1.jpg', 'Ez egy teszt film a Postman tesztekhez', 2024)
ON DUPLICATE KEY UPDATE 
    cim = 'Teszt Film 1',
    idotartam = 120,
    kiadasi_ev = 2024;

-- 4. Színész ID=1 létrehozása/biztosítása
INSERT INTO szineszek (szinesz_id, nev, szuletesi_datum, bio)
VALUES (1, 'Teszt Színész', '1990-01-01', 'Teszt színész életrajz')
ON DUPLICATE KEY UPDATE 
    nev = 'Teszt Színész',
    szuletesi_datum = '1990-01-01',
    bio = 'Teszt színész életrajz';

-- 5. Műfaj ID=1 és ID=5 biztosítása
INSERT INTO mufajok (mufaj_id, nev)
VALUES (1, 'Akció')
ON DUPLICATE KEY UPDATE nev = 'Akció';

INSERT INTO mufajok (mufaj_id, nev)
VALUES (5, 'Sci-Fi')
ON DUPLICATE KEY UPDATE nev = 'Sci-Fi';

-- 6. Film-Műfaj kapcsolat létrehozása (Film ID=1 és Műfaj ID=1)
INSERT IGNORE INTO film_mufaj (film_id, mufaj_id)
VALUES (1, 1);

-- 7. Rendező ID=1 létrehozása/biztosítása
INSERT INTO rendezok (rendezo_id, nev, szuletesi_datum, bio)
VALUES (1, 'Teszt Rendező', '1980-01-01', 'Teszt rendező életrajz')
ON DUPLICATE KEY UPDATE 
    nev = 'Teszt Rendező',
    szuletesi_datum = '1980-01-01',
    bio = 'Teszt rendező életrajz';

-- 8. Film-Rendező kapcsolat
INSERT IGNORE INTO film_rendezok (film_id, rendezo_id)
VALUES (1, 1);

-- 9. Film-Színész kapcsolat
INSERT IGNORE INTO film_szineszek (film_id, szinesz_id)
VALUES (1, 1);

-- 10. Auto-increment értékek beállítása (hogy új elemek ne ütközzenek)
ALTER TABLE film AUTO_INCREMENT = 100;
ALTER TABLE szineszek AUTO_INCREMENT = 100;
ALTER TABLE mufajok AUTO_INCREMENT = 20;
ALTER TABLE rendezok AUTO_INCREMENT = 50;

SELECT 'Teszt adatok sikeresen beállítva!' as result;
SELECT CONCAT('Film ID=1: ', cim) as film FROM film WHERE film_id = 1;
SELECT CONCAT('Színész ID=1: ', nev) as szinesz FROM szineszek WHERE szinesz_id = 1;
SELECT CONCAT('Műfaj ID=1: ', nev) as mufaj FROM mufajok WHERE mufaj_id = 1;
