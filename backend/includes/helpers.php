<?php

/**
 * JSON input feldolgozás hibakezeléssel
 * @return object|null
 */
function getJsonInput() {
    $input = file_get_contents("php://input");
    
    if (empty($input)) {
        http_response_code(400);
        echo json_encode(["message" => "Üres kérés."]);
        exit;
    }
    
    $data = json_decode($input);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["message" => "Hibás JSON formátum: " . json_last_error_msg()]);
        exit;
    }
    
    return $data;
}

/**
 * Input validálás - szám ellenőrzés
 */
function validateNumber($value, $fieldName, $min = null, $max = null) {
    if (!is_numeric($value)) {
        http_response_code(400);
        echo json_encode(["message" => "{$fieldName} számnak kell lennie."]);
        exit;
    }
    
    if ($min !== null && $value < $min) {
        http_response_code(400);
        echo json_encode(["message" => "{$fieldName} minimum {$min} kell legyen."]);
        exit;
    }
    
    if ($max !== null && $value > $max) {
        http_response_code(400);
        echo json_encode(["message" => "{$fieldName} maximum {$max} lehet."]);
        exit;
    }
    
    return true;
}

/**
 * URL validálás
 */
function validateUrl($url, $fieldName) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(["message" => "{$fieldName} érvénytelen URL."]);
        exit;
    }
    return true;
}

/**
 * Szöveg hossz validálás
 */
function validateLength($text, $fieldName, $min = 0, $max = null) {
    $length = strlen($text);
    
    if ($length < $min) {
        http_response_code(400);
        echo json_encode(["message" => "{$fieldName} minimum {$min} karakter kell legyen."]);
        exit;
    }
    
    if ($max !== null && $length > $max) {
        http_response_code(400);
        echo json_encode(["message" => "{$fieldName} maximum {$max} karakter lehet."]);
        exit;
    }
    
    return true;
}

/**
 * Dátum validálás
 */
function validateDate($date, $fieldName) {
    // Ellenőrzi, hogy a dátum YYYY-MM-DD formátumú-e
    $datePattern = '/^\d{4}-\d{2}-\d{2}$/';
    
    if (!preg_match($datePattern, $date)) {
        http_response_code(400);
        echo json_encode(["message" => "{$fieldName} formátuma érvénytelen. YYYY-MM-DD formátumot használj."]);
        exit;
    }
    
    // Ellenőrzi, hogy létező dátum-e
    $parts = explode('-', $date);
    if (!checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
        http_response_code(400);
        echo json_encode(["message" => "{$fieldName} nem létező dátum."]);
        exit;
    }
    
    return true;
}

/**
 * ID paraméter validálás
 * Ellenőrzi, hogy az ID pozitív egész szám-e
 */
function validateId($id, $fieldName = "ID") {
    if (!is_numeric($id) || $id <= 0 || $id != (int)$id) {
        http_response_code(400);
        echo json_encode(["message" => "{$fieldName} érvénytelen. Pozitív egész számnak kell lennie."]);
        exit;
    }
    return (int)$id;
}
?>