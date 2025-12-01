<?php
session_start();

// Teszt login
$_SESSION['user_id'] = 2; // Moderator
$_SESSION['username'] = 'Moderator';
$_SESSION['user_email'] = 'moderator@cinematar.hu';
$_SESSION['user_role'] = 'moderator';

// Redirect to Update Profile
$baseUrl = 'http://localhost/php/PHP%20projekt/Szerveroldali-programozas-projekt/public';

// PUT request using cURL
$ch = curl_init("$baseUrl/users/profile");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'felhasznalonev' => 'Moderator Frissítve',
    'email' => 'moderator@cinematar.hu'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Cookie: PHPSESSID=' . session_id()
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n";
