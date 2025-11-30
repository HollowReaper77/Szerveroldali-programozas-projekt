# Kép URL kezelés

## Támogatott formátumok

```php
// Külső URL - változatlan
"https://example.com/poster.jpg"

// Relatív útvonal - kiegészítve
"/uploads/posters/film.jpg" → "http://localhost/uploads/posters/film.jpg"

// Fájlnév - prefix hozzáadva
"film.jpg" → "http://localhost/uploads/posters/film.jpg"
```

## Használat kontrollerben

```php
// Film poszter
$film_data['poszter_url'] = processImageUrl($film->poszter_url, 'poster');

// Profil kép
$user_data['profilkep_url'] = processImageUrl($user->profilkep_url, 'profile');
```

## Validáció

```php
validateImageUrl($imageUrl, $allowExternal = true);
```

## Mappa struktúra

```
uploads/
  ├── posters/    (film poszterek)
  └── profiles/   (profil képek)
```
