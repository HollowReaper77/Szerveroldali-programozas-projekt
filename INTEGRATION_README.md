# 🎬 Cinematár - Backend és Frontend Integráció

## ✅ Mi történt?

A backend REST API és a frontend teljes mértékben **össze lett hangolva**. Most már a két rész kommunikál egymással!

---

## 📦 Létrehozott/Módosított Fájlok

### **Frontend JavaScript fájlok:**
1. ✅ `frontend/config.js` - API konfiguráció és helper függvények
2. ✅ `frontend/search.js` - Film keresés API integrációval
3. ✅ `frontend/films.js` - Főoldal dinamikus filmek betöltése
4. ✅ `frontend/login.js` - Bejelentkezés API hívással
5. ✅ `frontend/register.js` - Regisztráció API hívással
6. ✅ `frontend/profile.js` - Profil kezelés és kijelentkezés

### **Backend fájlok:**
7. ✅ `backend/models/user.php` - User model (CRUD, auth)
8. ✅ `backend/controllers/UserController.php` - Felhasználókezelés
9. ✅ `public/index.php` - `/users` endpoint-ok hozzáadva
10. ✅ `backend/database/create_users_table.sql` - Users tábla SQL

### **Módosított HTML fájlok:**
11. ✅ `frontend/index.html` - Script tag-ek hozzáadva
12. ✅ `frontend/kereses.html` - Config.js betöltve
13. ✅ `frontend/bejelentkezes.html` - Config.js betöltve
14. ✅ `frontend/regisztracio.html` - Config.js betöltve
15. ✅ `frontend/profil.html` - Kijelentkezés gomb + script tag-ek

---

## 🚀 Használat Előtt - ADATBÁZIS BEÁLLÍTÁS

### 1️⃣ **Users tábla létrehozása**

Nyisd meg a phpMyAdmin-t és futtasd le ezt az SQL szkriptet:

```bash
# Vagy importáld a fájlt:
backend/database/create_users_table.sql
```

**VAGY** másold be ezt a kódot phpMyAdmin-ba:

```sql
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
```

### 2️⃣ **API URL ellenőrzése**

Nyisd meg: `frontend/config.js`

Ellenőrizd, hogy a `BASE_URL` helyes-e:

```javascript
BASE_URL: 'http://localhost/php/PHP%20projekt/Szerveroldali-programozas-projekt/public'
```

Ha a projekted más helyen van, módosítsd!

---

## 🎯 Backend API Endpointok

### **Filmek (Films):**
- `GET /films` - Összes film listázása
- `GET /films/{id}` - Egy film részletei
- `POST /films` - Új film létrehozása
- `PUT /films/{id}` - Film módosítása
- `DELETE /films/{id}` - Film törlése

### **Színészek (Actors):**
- `GET /actors` - Összes színész
- `GET /actors/{id}` - Egy színész

### **Rendezők (Directors):**
- `GET /directors` - Összes rendező
- `GET /directors/{id}` - Egy rendező

### **Műfajok (Genres):**
- `GET /genres` - Összes műfaj

### **Országok (Countries):**
- `GET /countries` - Összes ország

### **Felhasználók (Users) - ÚJ!:**
- `POST /users/register` - Regisztráció
- `POST /users/login` - Bejelentkezés
- `POST /users/logout` - Kijelentkezés
- `GET /users/profile` - Profil lekérése
- `PUT /users/profile` - Profil frissítése
- `POST /users/change-password` - Jelszó módosítás

---

## 🔧 Frontend Funkciók

### **1. Film Keresés (`kereses.html`)**
- API-ból tölti be az összes filmet
- Valós idejű keresés cím és leírás alapján
- Dinamikus kártyák megjelenítése

### **2. Főoldal (`index.html`)**
- Filmek dinamikus betöltése az API-ból
- Helyettesíti a statikus HTML listát

### **3. Bejelentkezés (`bejelentkezes.html`)**
- Email/jelszó validáció
- POST `/users/login` hívás
- Session + localStorage kezelés
- Automatikus átirányítás sikeres belépés után

### **4. Regisztráció (`regisztracio.html`)**
- Név, email, jelszó validáció
- Jelszó megerősítés ellenőrzés
- POST `/users/register` hívás
- Automatikus bejelentkezés regisztráció után

### **5. Profil (`profil.html`)**
- Felhasználó adatok megjelenítése
- Kijelentkezés funkció
- LocalStorage + API szinkronizáció

---

## 🧪 Tesztelés

### **1. XAMPP indítása:**
```bash
Apache: ON
MySQL: ON
```

### **2. Adatbázis ellenőrzése:**
- phpMyAdmin: http://localhost/phpmyadmin
- Tábla: `filmdb_temp_name.users`

### **3. Frontend megnyitása böngészőben:**
```
http://localhost/php/PHP%20projekt/Szerveroldali-programozas-projekt/frontend/index.html
```

### **4. API tesztelés Postman-nel (opcionális):**
```
POST http://localhost/php/PHP%20projekt/Szerveroldali-programozas-projekt/public/users/register

Body (JSON):
{
  "nev": "Teszt User",
  "email": "test@test.com",
  "jelszo": "password123"
}
```

---

## ⚠️ Gyakori Hibák és Megoldások

### **1. CORS hiba:**
```
Access to fetch at '...' from origin '...' has been blocked by CORS policy
```

**Megoldás:** A `public/index.php` már tartalmazza a CORS header-öket:
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
```

### **2. "Users tábla nem található":**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'filmdb.users' doesn't exist
```

**Megoldás:** Futtasd le a `backend/database/create_users_table.sql` fájlt phpMyAdmin-ban!

### **3. Session nem működik:**
**Megoldás:** Ellenőrizd, hogy a `session_start()` van-e az `index.php` elején.

### **4. API URL hibás:**
**Megoldás:** Nyisd meg `frontend/config.js`-t és módosítsd a `BASE_URL`-t a saját mappádra.

---

## 📊 Projekt Struktúra (Teljes)

```
Szerveroldali-programozas-projekt/
├── backend/
│   ├── controllers/
│   │   ├── FilmController.php
│   │   ├── SzineszController.php
│   │   ├── RendezoController.php
│   │   ├── MufajController.php
│   │   ├── NemzetisegController.php
│   │   ├── SzereploController.php
│   │   ├── FilmMufajController.php
│   │   └── UserController.php ✨ ÚJ
│   ├── models/
│   │   ├── film.php
│   │   ├── szinesz.php
│   │   ├── rendezo.php
│   │   ├── mufaj.php
│   │   ├── orszag.php
│   │   ├── szereplo.php
│   │   ├── film_mufaj.php
│   │   └── user.php ✨ ÚJ
│   ├── includes/
│   │   ├── config.php
│   │   └── helpers.php
│   └── database/
│       └── create_users_table.sql ✨ ÚJ
├── frontend/
│   ├── *.html (index, kereses, profil, stb.)
│   ├── app.js (téma kezelés)
│   ├── config.js ✨ ÚJ (API konfig)
│   ├── search.js ✨ ÚJ (film keresés)
│   ├── films.js ✨ ÚJ (főoldal filmek)
│   ├── login.js ✨ ÚJ (bejelentkezés)
│   ├── register.js ✨ ÚJ (regisztráció)
│   ├── profile.js ✨ ÚJ (profil kezelés)
│   └── style.css
├── public/
│   └── index.php (router - bővítve users endpoint-okkal)
└── README.md
```

---

## 🎉 Kész vagy!

Most már a backend és frontend **teljesen összehangolt**:

✅ Filmek API-ból töltődnek  
✅ Keresés működik  
✅ Regisztráció/Bejelentkezés működik  
✅ Session kezelés beállítva  
✅ Profil oldal dinamikus  

**Következő lépés:** Teszteld a funkciókat böngészőben! 🚀
