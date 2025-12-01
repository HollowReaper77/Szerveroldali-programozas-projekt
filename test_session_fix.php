<?php
// Test script to debug Update Profile and Change Password issues

$baseUrl = 'http://localhost/php/PHP%20projekt/Szerveroldali-programozas-projekt/public';

// 1. Login as Moderator
echo "=== LOGIN MODERATOR ===\n";
$ch = curl_init("$baseUrl/users/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'moderator@cinematar.hu',
    'jelszo' => 'moderator123'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "Headers:\n$headers\n";
echo "Body: $body\n\n";

// Extract session cookie
preg_match('/PHPSESSID=([^;]+)/', $headers, $matches);
$sessionId = $matches[1] ?? null;

if (!$sessionId) {
    die("ERROR: No session cookie found!\n");
}

echo "Session ID: $sessionId\n\n";

// 2. Update Profile
echo "=== UPDATE PROFILE ===\n";
$ch = curl_init("$baseUrl/users/profile");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'felhasznalonev' => 'Moderator Frissítve',
    'email' => 'moderator@cinematar.hu'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Cookie: PHPSESSID=$sessionId"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// 3. Change Password
echo "=== CHANGE PASSWORD ===\n";
$ch = curl_init("$baseUrl/users/password");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'regi_jelszo' => 'moderator123',
    'uj_jelszo' => 'newpass123'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Cookie: PHPSESSID=$sessionId"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
