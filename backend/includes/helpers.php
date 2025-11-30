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

/**
 * Bejelentkezés ellenőrzése
 * Ellenőrzi, hogy a felhasználó be van-e jelentkezve
 * @return bool True ha be van jelentkezve, egyébként HTTP 401 és kilép
 */
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["message" => "Nincs bejelentkezve. Kérlek jelentkezz be."]);
        exit;
    }
    return true;
}

/**
 * Szerepkör ellenőrzése
 * Ellenőrzi, hogy a bejelentkezett felhasználó rendelkezik-e a szükséges szerepkörrel
 * @param string $required_role A szükséges szerepkör ('user', 'moderator', 'admin')
 * @return bool True ha megfelelő szerepkör, egyébként HTTP 403 és kilép
 */
function requireRole($required_role) {
    // Először ellenőrizzük, hogy be van-e jelentkezve
    requireAuth();

    $user_role = $_SESSION['user_role'] ?? 'user';

    // Admin mindent csinálhat
    if ($user_role === 'admin') {
        return true;
    }

    // Moderator mindent csinálhat, amit a user
    if ($required_role === 'user' && in_array($user_role, ['moderator', 'admin'])) {
        return true;
    }

    // Moderator jogosultság ellenőrzése
    if ($required_role === 'moderator' && $user_role === 'moderator') {
        return true;
    }

    // Ha nem felel meg a jogosultság
    if ($user_role !== $required_role) {
        http_response_code(403);
        echo json_encode([
            "message" => "Nincs jogosultságod ehhez a művelethez. Szükséges szerepkör: {$required_role}, te pedig: {$user_role}"
        ]);
        exit;
    }

    return true;
}

/**
 * Admin jogosultság ellenőrzése
 * Ellenőrzi, hogy a bejelentkezett felhasználó admin-e
 * @return bool True ha admin, egyébként HTTP 403 és kilép
 */
function requireAdmin() {
    return requireRole('admin');
}

/**
 * Moderator vagy Admin jogosultság ellenőrzése
 * Ellenőrzi, hogy a bejelentkezett felhasználó moderator vagy admin-e
 * @return bool True ha moderator vagy admin, egyébként HTTP 403 és kilép
 */
function requireModerator() {
    requireAuth();

    $user_role = $_SESSION['user_role'] ?? 'user';

    if (!in_array($user_role, ['moderator', 'admin'])) {
        http_response_code(403);
        echo json_encode([
            "message" => "Nincs jogosultságod ehhez a művelethez. Legalább moderator szerepkör szükséges."
        ]);
        exit;
    }

    return true;
}

/**
 * Aktuális felhasználó szerepkörének lekérése
 * @return string|null A felhasználó szerepköre vagy null ha nincs bejelentkezve
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Aktuális felhasználó ID-jának lekérése
 * @return int|null A felhasználó ID-ja vagy null ha nincs bejelentkezve
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Ellenőrzi, hogy a bejelentkezett felhasználó admin-e
 * @return bool True ha admin, false egyébként
 */
function isAdmin() {
    $role = getCurrentUserRole();
    return $role === 'admin';
}

/**
 * Ellenőrzi, hogy a bejelentkezett felhasználó moderator vagy admin-e
 * @return bool True ha moderator vagy admin, false egyébként
 */
function isModerator() {
    $role = getCurrentUserRole();
    return in_array($role, ['moderator', 'admin']);
}
?>