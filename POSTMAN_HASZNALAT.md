# Postman API Teszt

## Import

1. Postman → Import
2. Válaszd: `Film-API.postman_collection.json` + `Film-API.postman_environment.json`
3. Environment: **Film API - Local**

## Gyors teszt

```
1. Login - Admin
2. Get All Films
3. Get Profile (session teszt)
4. Logout
```

## Endpointok

- **Authentication** (7) - Register, Login, Profile
- **Films** (5) - CRUD + pagination
- **Actors** (5) - CRUD + bio
- **Genres** (3) - List, Create
- **Film-Genres** (3) - Relations
- **Admin** (3) - User management

## Magyar response kulcsok

- `filmek`, `szineszek`, `mufajok`, `jogosultsag`

## Session

- Login után PHPSESSID automatikusan mentődik
- Minden request használja a session-t
