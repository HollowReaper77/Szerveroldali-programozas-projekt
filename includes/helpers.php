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