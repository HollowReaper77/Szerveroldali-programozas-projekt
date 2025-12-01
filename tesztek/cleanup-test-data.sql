-- Teszt adatok törlése a sikeres teszt futtatáshoz
-- HASZNÁLAT: mysql -u root film < tesztek/cleanup-test-data.sql

-- 1. Teszt felhasználó törlése (hogy a Register User teszt újra lefuthasson)
DELETE FROM felhasznalo WHERE email = 'teszt@cinematar.hu' OR felhasznalonev = 'teszt_user';

-- 2. Film-kapcsolatok törlése (foreign key miatt először)
-- Film-Műfaj kapcsolatok törlése
DELETE FROM film_mufaj WHERE film_id >= 100;

-- Film-Színész kapcsolatok törlése
DELETE FROM film_szineszek WHERE film_id >= 100;

-- Film-Rendező kapcsolatok törlése
DELETE FROM film_rendezok WHERE film_id >= 100;

-- 3. Teszt filmek törlése (ID >= 100)
DELETE FROM film WHERE film_id >= 100;

-- 4. Teszt színészek törlése (ID >= 100)
DELETE FROM szineszek WHERE szinesz_id >= 100;

-- 5. Teszt műfajok törlése (ID >= 20)
DELETE FROM mufajok WHERE mufaj_id >= 20;

-- 6. Teszt rendezők törlése (ID >= 50)
DELETE FROM rendezok WHERE rendezo_id >= 50;

-- 7. Session/cookie cleanup (ha szükséges)
-- DELETE FROM sessions WHERE user_id NOT IN (SELECT felhasznalo_id FROM felhasznalo);

SELECT 'Teszt adatok törölve!' as result;
SELECT COUNT(*) as 'Filmek szama' FROM film;
SELECT COUNT(*) as 'Szineszek szama' FROM szineszek;
SELECT COUNT(*) as 'Felhasznalok szama' FROM felhasznalo;
