# CinemaTár - Film API

REST API filmadatbázis kezeléséhez. Tartalmaz filmek, színészek, műfajok és felhasználó-kezelési funkciókat.

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

## Tesztelés

### Automatikus tesztek Newman CLI-vel:

```bash
# Minden teszt futtatása cookie jar-ral (ajánlott)
newman run tesztek/Film-API.postman_collection.json --cookie-jar tesztek/cookies.json

# Adott kategória tesztelése
newman run tesztek/Film-API.postman_collection.json --folder "Films"
newman run tesztek/Film-API.postman_collection.json --folder "Authentication" --cookie-jar tesztek/cookies.json
```

**Fontos:** A `--cookie-jar` kapcsoló szükséges az autentikációs tesztekhez, hogy a session cookie-k megmaradjanak a kérések között.

### Teszt adatok beállítása:

```bash
mysql -u root film < tesztek/test-data-setup.sql
```

Ez létrehozza:
- Moderátor felhasználót (email: moderator@cinematar.hu, jelszó: moderator123)
- Admin jogosultságot az admin felhasználónak

## Teszt eredmények

**46/57 teszt sikeres (81%)**

- ✅ Films: 11/11 (100%)
- ✅ Actors: 11/11 (100%)
- ✅ Genres: 6/6 (100%)
- ✅ Film-Genres: 6/6 (100%)
- ⚠️ Authentication: 10/16 (63%)
- ⚠️ Admin: 2/7 (29%)

### Ismert problémák

1. **Register User konfliktus**: A `teszt_user` már létezik az adatbázisban (várható viselkedés)
2. **Session kezelés**: Newman CLI nem perzisztálja automatikusan a session cookie-kat a kérések között, ezért az admin tesztek és jelszó módosítás sikertelen
3. **Update Profile 500 hiba**: Profil frissítési endpoint hibát dob (külön vizsgálandó)

### Magyar mezőnevek

Az API következetes magyar elnevezéseket használ:
- `filmek`, `film_id`, `cim`, `kiadasi_ev`, `idotartam`
- `szineszek`, `szinesz_id`, `nev`, `szuletesi_datum`
- `mufajok`, `mufaj_id`
- `felhasznalo`, `felhasznalonev`, `jogosultsag`

## Telepítés

1. XAMPP indítása (Apache + MySQL)
2. Adatbázis importálása
3. Backend elérése: `http://localhost/php/PHP projekt/Szerveroldali-programozas-projekt/public/`

## Technológiák

- PHP 8.x
- MySQL
- PDO adatbázis kapcsolat
- Session alapú autentikáció
- Newman CLI automatikus teszteléshez
