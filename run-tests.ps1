# Automatikus teszt futtatás script
# Ez a script törli a teszt adatokat, beállítja az adatbázist és futtatja a Newman teszteket

Write-Host "`n=== Film API Tesztek Futtatása ===" -ForegroundColor Cyan
Write-Host "Teszt adatok törlése..." -ForegroundColor Yellow

# Cleanup teszt adatok
Get-Content tesztek/cleanup-test-data.sql | C:\xampp\mysql\bin\mysql.exe -u root film 2>&1 | Out-Null

# Setup teszt adatok
Get-Content tesztek/test-data-setup.sql | C:\xampp\mysql\bin\mysql.exe -u root film 2>&1 | Out-Null

Write-Host "Teszt adatok beállítva!" -ForegroundColor Green
Write-Host "`nNewman tesztek futtatása..." -ForegroundColor Yellow

# Newman tesztek futtatása
newman run tesztek/Film-API.postman_collection.json -e tesztek/Film-API.postman_environment.json --cookie-jar tesztek/cookies.json

Write-Host "`n=== Tesztek Befejezve ===" -ForegroundColor Cyan
