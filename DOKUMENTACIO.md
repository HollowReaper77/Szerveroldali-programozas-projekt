# CinemaTár - Projekt Dokumentáció

**Szerveroldali programozás - 2025 őszi félév**

---

## 1. Projekt áttekintése

**CinemaTár** - Filmadatbázis kezelő REST API és webalkalmazás.

### Funkciók
- Filmek böngészése, keresése (cím, műfaj, színész, rendező)
- Felhasználói regisztráció és bejelentkezés (session)
- Profil és jelszó kezelés
- Film CRUD műveletek (moderátor/admin)
- Felhasználó kezelés (admin)
- Kép feltöltés (URL vagy fájl)

### Technológiák
- **Backend:** PHP 8.x, MySQL 5.7+, MVC architektúra
- **Frontend:** HTML5, CSS3, JavaScript (Fetch API)
- **Biztonság:** BCrypt hash, session auth, prepared statements
- **Szerver:** Apache (XAMPP)

---

## 2. Adatbázis modell

### Fő táblák

**felhasznalo** - Felhasználói adatok (admin/moderator/user szerepkörök)  
**film** - Filmek alapadatai (cím, év, időtartam, leírás, poszter)  
**szineszek** - Színészek (név, születési dátum, bio)  
**rendezok** - Rendezők (név, születési dátum, bio)  
**mufajok** - Műfajok (horror, sci-fi, dráma, stb.)  
**orszagok** - Gyártó országok  
**velemenyek** - Felhasználói értékelések filmekhez

### Kapcsolótáblák

- `film_mufaj` - Film ↔ Műfaj (N:M)
- `film_szineszek` - Film ↔ Színész (N:M)
- `film_rendezok` - Film ↔ Rendező (N:M)
- `film_orszagok` - Film ↔ Ország (N:M)

---

## 3. API végpontok

### Autentikáció
| Metódus | Végpont | Leírás | Jogosultság |
|---------|---------|--------|-------------|
| POST | `/users/register` | Regisztráció | - |
| POST | `/users/login` | Bejelentkezés | - |
| POST | `/users/logout` | Kijelentkezés | Bejelentkezett |
| GET | `/users/profile` | Profil lekérése | Bejelentkezett |
| PUT | `/users/profile` | Profil módosítása | Bejelentkezett |
| PUT | `/users/password` | Jelszó módosítása | Bejelentkezett |

### Filmek
| Metódus | Végpont | Leírás | Jogosultság |
|---------|---------|--------|-------------|
| GET | `/films` | Filmek listája (lapozással) | - |
| GET | `/films/{id}` | Film részletei | - |
| POST | `/films` | Új film | Moderátor+ |
| PUT | `/films/{id}` | Film módosítása | Moderátor+ |
| DELETE | `/films/{id}` | Film törlése | Moderátor+ |

### Admin
| Metódus | Végpont | Leírás | Jogosultság |
|---------|---------|--------|-------------|
| GET | `/users` | Összes felhasználó | Admin |
| PUT | `/users/{id}/role` | Szerepkör módosítása | Admin |
| DELETE | `/users/{id}` | Felhasználó törlése | Admin |

**További végpontok:** Színészek (`/actors`), Műfajok (`/genres`), Rendezők (`/directors`), Film-műfaj kapcsolatok (`/film-genres`)

---

## 4. Telepítés

### 1. XAMPP telepítése
1. Töltsd le: https://www.apachefriends.org/
2. Telepítsd (Apache, MySQL, PHP, phpMyAdmin)
3. Indítsd el az Apache-ot és MySQL-t

### 2. Projekt másolása
```
C:\xampp\htdocs\php\PHP projekt\Szerveroldali-programozas-projekt\
```

### 3. Adatbázis importálása
1. Nyisd meg: `http://localhost/phpmyadmin`
2. Importáld: `backend/database/filmadatbazis.sql`
3. Ellenőrzés: `film` adatbázis, 11 tábla, tesztadatok

### 4. Konfiguráció

**Backend** (`backend/includes/config.php`):
```php
$host = 'localhost';
$db_name = 'film';
$username = 'root';
$password = '';
```

**Frontend** (`frontend/config.js`):
```javascript
const API_CONFIG = {
    BASE_URL: 'http://localhost/php/PHP projekt/Szerveroldali-programozas-projekt/public'
};
```

### 5. Indítás
- **API:** `http://localhost/php/PHP projekt/Szerveroldali-programozas-projekt/public/films`
- **Frontend:** Nyisd meg `frontend/index.html` böngészőben

---

## 5. Tesztelési adatok

### Teszt felhasználók

| Email | Jelszó | Jogosultság |
|-------|--------|-------------|
| admin@cinematar.hu | `admin123` | Admin |
| moderator@cinematar.hu | `moderator123` | Moderátor |
| user@test.com | `user123` | User |

### Postman tesztek
1. Importáld: `tesztek/Film-API.postman_collection.json`
2. Importáld: `tesztek/Film-API.postman_environment.json`
---

## 6. Használat

### Főbb funkciók

**Filmek böngészése** - `index.html` (vendég is elérheti)  
**Keresés** - `kereses.html` (cím, műfaj, színész, rendező alapján)  
**Regisztráció** - `regisztracio.html`  
**Bejelentkezés** - `bejelentkezes.html`  
**Profil kezelés** - `profil.html` (bejelentkezés szükséges)  
**Jelszó módosítás** - `jelszo_modositas.html` (bejelentkezés szükséges)  
**Admin felület** - `admin.html` (moderátor/admin jogosultság)
  - **Film kezelés** - Új film hozzáadása, szerkesztése, törlése, képfeltöltés
  - **Felhasználó kezelés** - Szerepkör módosítása, felhasználó törlése (csak admin)

### Jogosultságok

| Funkció | Vendég | User | Moderátor | Admin |
|---------|--------|------|-----------|-------|
| Filmek böngészése | Igen | Igen | Igen | Igen |
| Keresés | Igen | Igen | Igen | Igen |
| Profil szerkesztése | - | Igen | Igen | Igen |
| Jelszó módosítása | - | Igen | Igen | Igen |
| Film CRUD | - | - | Igen | Igen |
| Képfeltöltés | - | - | Igen | Igen |
| Felhasználó kezelés | - | - | - | Igen |

### Képfeltöltés
- **Módok:** URL megadása vagy fájl feltöltése (max 5MB)
- **Formátumok:** JPG, PNG, GIF, WebP
- **Tárolt hely:** `uploads/` mappa
- **Jogosultság:** Moderátor vagy admin

---

## 7. Projekt állapot

### ✅ Kész funkciók
- REST API teljes CRUD műveletekkel (filmek, színészek, műfajok, rendezők, országok)
- Felhasználókezelés (regisztráció, bejelentkezés, session)
- Profil és jelszó módosítás
- Film kezelés admin felületen (hozzáadás, szerkesztés, törlés)
- Képfeltöltés (URL vagy fájl)
- Felhasználó kezelés admin felületen (szerepkör módosítás, törlés)
- Keresési funkció (cím, műfaj, színész, rendező alapján)
- Dinamikus navigációs menü (jogosultság alapján)
- Sötét/világos téma váltás
- Selenium tesztek (20 db)
- Teljes dokumentáció

### 🎯 Projekt célja teljesítve
A CinemaTár weboldal és API **100%-ban kész** és használatra alkalmas!


