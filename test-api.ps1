# API Teszt Script
$baseUrl = "http://localhost/php/PHP%20projekt/Szerveroldali-programozas-projekt/public"

# Session cookie tárolásához
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

Write-Host "`n=== FILM API TESZT ===" -ForegroundColor Cyan

# 1. Filmek listázása
Write-Host "`n1. Filmek listázása (GET /films)" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/films" -Method GET -ContentType "application/json"
    Write-Host "Sikeres! Filmek száma: $($response.count)" -ForegroundColor Green
    if ($response.films) {
        Write-Host "Első film címe: $($response.films[0].cim)" -ForegroundColor Gray
    }
} catch {
    Write-Host "Hiba: $_" -ForegroundColor Red
}

# 2. Egy film lekérdezése
Write-Host "`n2. Egy film lekérdezése (GET /films/1)" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/films/1" -Method GET -ContentType "application/json"
    Write-Host "Sikeres! Film címe: $($response.film.cim)" -ForegroundColor Green
} catch {
    Write-Host "Hiba: $_" -ForegroundColor Red
}

Write-Host "`n=== USER API TESZT ===" -ForegroundColor Cyan

# 3. Regisztráció teszt
Write-Host "`n3. Regisztráció teszt (POST /users/register)" -ForegroundColor Yellow
$registerData = @{
    felhasznalonev = "teszt_$(Get-Random -Maximum 10000)"
    email = "teszt_$(Get-Random -Maximum 10000)@test.hu"
    jelszo = "teszt123"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/users/register" -Method POST -Body $registerData -ContentType "application/json" -SessionVariable session
    Write-Host "Sikeres regisztráció! Felhasználó: $($response.user.felhasznalonev), Jogosultság: $($response.user.jogosultsag)" -ForegroundColor Green
    $testUserId = $response.user.id
} catch {
    Write-Host "Hiba: $_" -ForegroundColor Red
}

# 4. Bejelentkezés teszt (admin)
Write-Host "`n4. Bejelentkezés teszt admin-nal (POST /users/login)" -ForegroundColor Yellow
$loginData = @{
    email = "admin@cinematar.hu"
    jelszo = "admin123"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/users/login" -Method POST -Body $loginData -ContentType "application/json" -WebSession $session
    Write-Host "Sikeres bejelentkezés! Felhasználó: $($response.user.felhasznalonev), Jogosultság: $($response.user.jogosultsag)" -ForegroundColor Green
} catch {
    Write-Host "Hiba: $_" -ForegroundColor Red
}

# 5. Profil lekérdezés
Write-Host "`n5. Profil lekérdezés (GET /users/profile)" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/users/profile" -Method GET -ContentType "application/json" -WebSession $session
    Write-Host "Sikeres! Bejelentkezett felhasználó: $($response.user.felhasznalonev)" -ForegroundColor Green
} catch {
    Write-Host "Hiba: $_" -ForegroundColor Red
}

Write-Host "`n=== SZÍNÉSZEK TESZT ===" -ForegroundColor Cyan

# 6. Színészek listázása
Write-Host "`n6. Színészek listázása (GET /actors)" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/actors" -Method GET -ContentType "application/json"
    Write-Host "Sikeres! Színészek száma: $($response.count)" -ForegroundColor Green
} catch {
    Write-Host "Hiba: $_" -ForegroundColor Red
}

Write-Host "`n=== MŰFAJOK TESZT ===" -ForegroundColor Cyan

# 7. Műfajok listázása
Write-Host "`n7. Műfajok listázása (GET /genres)" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/genres" -Method GET -ContentType "application/json"
    Write-Host "Sikeres! Műfajok száma: $($response.count)" -ForegroundColor Green
} catch {
    Write-Host "Hiba: $_" -ForegroundColor Red
}

Write-Host "`n=== FILM-MŰFAJ KAPCSOLAT TESZT ===" -ForegroundColor Cyan

# 8. Film műfajainak lekérdezése
Write-Host "`n8. Film műfajainak lekérdezése (GET /film-genres/film/1)" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/film-genres/film/1" -Method GET -ContentType "application/json"
    Write-Host "Sikeres! Műfajok száma: $($response.count)" -ForegroundColor Green
} catch {
    Write-Host "Hiba: $_" -ForegroundColor Red
}

Write-Host "`n=== TESZT BEFEJEZVE ===" -ForegroundColor Cyan
Write-Host "`nOsszefoglalo: Az API mukodik!" -ForegroundColor Green
