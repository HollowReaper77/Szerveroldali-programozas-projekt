# CinemaTár - Film API

REST API filmadatbázis kezeléséhez PHP-ban.

## Funkciók

- Filmek, színészek, rendezők kezelése
- Műfajok hozzárendelése filmekhez
- Felhasználói regisztráció és bejelentkezés
- Jogosultságkezelés (admin, moderátor, user)
- Filmekhez kapcsolódó hozzászólások megtekintése és beküldése a keresés oldalon
- **Dinamikus főoldal:** kiemelt filmek slider automatikus váltással, legújabb filmek carousel kétirányú navigációval
- **Sötét/világos téma** váltás (localStorage-ban mentve)
- **Responsive design:** mobil-, tablet- és desktop-optimalizált elrendezés
- **Megnézett filmek** jelölése és nyomon követése (bejelentkezett felhasználóknak)

## Telepítés

1. **XAMPP indítása** (Apache + MySQL)

2. **Adatbázis importálása:**
   - Nyisd meg a phpMyAdmin-t: `http://localhost/phpmyadmin`
   - Importáld a `backend/database/filmadatbazis.sql` fájlt

3. **Alkalmazás elérése:**
   - Frontend: `http://localhost/php/PHP projekt/Szerveroldali-programozas-projekt/frontend/index.html`
   - API: `http://localhost/php/PHP projekt/Szerveroldali-programozas-projekt/public/films`

## Főbb oldalak

- **`index.html`** - Főoldal kiemelt filmekkel és legújabb kiadásokkal
- **`kereses.html`** - Filmkeresés (cím, műfaj, színész, rendező alapján) + vélemények
- **`profil.html`** - Bejelentkezett felhasználó profil és jelszó szerkesztése
- **`admin.html`** - Filmek és felhasználók kezelése (moderátor/admin jogosultság szükséges)
- **`regisztracio.html`** - Új felhasználó regisztrációja
- **`bejelentkezes.html`** - Bejelentkezés

## Alapértelmezett felhasználók

| Email | Jelszó | Jogosultság |
|-------|--------|-------------|
| admin@cinematar.hu | admin123 | admin |
| moderator@cinematar.hu | moderator123 | moderator |
| user@cinematar.hu | user123 | user |

## API Végpontok

### Filmek
- `GET /films` - Összes film lekérése (pagination)
- `GET /films/{id}` - Film részletei
- `POST /films` - Új film létrehozása
- `PUT /films/{id}` - Film módosítása
- `DELETE /films/{id}` - Film törlése

### Színészek
- `GET /actors` - Összes színész lekérése
- `GET /actors/{id}` - Színész részletei
- `POST /actors` - Új színész létrehozása
- `PUT /actors/{id}` - Színész módosítása
- `DELETE /actors/{id}` - Színész törlése

### Műfajok
- `GET /genres` - Összes műfaj lekérése
- `GET /genres/{id}` - Műfaj részletei
- `POST /genres` - Új műfaj létrehozása

### Film-Műfaj kapcsolatok
- `GET /film-genres/film/{id}` - Film műfajai
- `POST /film-genres` - Műfaj hozzáadása filmhez
- `DELETE /film-genres/film/{filmId}/genre/{genreId}` - Műfaj eltávolítása

### Autentikáció
- `POST /users/register` - Regisztráció
- `POST /users/login` - Bejelentkezés
- `GET /users/profile` - Profil lekérése
- `PUT /users/profile` - Profil módosítása
- `PUT /users/password` - Jelszó módosítása
- `POST /users/logout` - Kijelentkezés

### Vélemények
- `GET /reviews/film/{id}` - Az adott filmhez tartozó összes vélemény listázása
- `POST /reviews` - Új vélemény létrehozása (bejelentkezett felhasználónak)

> **Tipp:** a `frontend/kereses.html` oldalon bármelyik találatra kattintva megjelenik egy részletes modal, ahol a felhasználók elolvashatják a hozzászólásokat, illetve bejelentkezve új véleményt is beküldhetnek.

### Megnézett filmek
- `GET /watched-films` - Bejelentkezett felhasználó megnézett filmjei
- `POST /watched-films` - Film megjelölése megnézettként
- `DELETE /watched-films/{filmId}` - Film eltávolítása a megnézettek közül

### Admin (csak admin jogosultsággal)
- `GET /users` - Összes felhasználó
- `PUT /users/{id}/role` - Jogosultság módosítása
- `DELETE /users/{id}` - Felhasználó törlése

## Adatbázis

Adatbázis név: `film`

Táblák:
- `film` - Filmek adatai
- `szineszek` - Színészek adatai
- `mufajok` - Műfajok
- `felhasznalo` - Felhasználók
- `film_mufaj` - Film-műfaj kapcsolótábla
- `szerepel_benne` - Film-színész kapcsolótábla

## Tesztelés Postman-nel

1. Importáld a `tesztek/Film-API.postman_collection.json` fájlt
2. Importáld a `tesztek/Film-API.postman_environment.json` fájlt
3. Futtasd a teszteket

## Technológiák

- **Backend:** PHP 8.x
- **Adatbázis:** MySQL
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (Fetch API)
- **Autentikáció:** Session alapú
- **Biztonság:** BCrypt jelszó hashelés, prepared statements
- **UI/UX:** Sötét/világos téma, responsive layout, kétirányú carousel navigáció
- **Képkezelés:** URL vagy fájl feltöltés támogatása (max 5MB, JPG/PNG/GIF/WebP)
