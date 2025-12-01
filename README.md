# CinemaTár - Film API

REST API filmadatbázis kezeléséhez PHP-ban.

## Funkciók

- Filmek, színészek, rendezők kezelése
- Műfajok hozzárendelése filmekhez
- Felhasználói regisztráció és bejelentkezés
- Jogosultságkezelés (admin, moderátor, user)

## Telepítés

1. **XAMPP indítása** (Apache + MySQL)

2. **Adatbázis létrehozása:**
```bash
mysql -u root -e "CREATE DATABASE film;"
mysql -u root film < backend/database/filmadatbazis.sql
```

3. **Böngészőben megnyitni:**
   - API: `http://localhost/php/PHP projekt/Szerveroldali-programozas-projekt/public/`
   - Frontend: `frontend/index.html`

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

- PHP 8.x
- MySQL
- Session alapú autentikáció
- BCrypt jelszó hashelés
