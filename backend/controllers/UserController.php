<?php
class UserController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    // Regisztráció
    public function register() {
        $data = getJsonInput();

        // Validáció
        if (empty($data['nev']) || empty($data['email']) || empty($data['jelszo'])) {
            http_response_code(400);
            echo json_encode(["message" => "Név, email és jelszó kötelező."]);
            return;
        }

        // Email formátum ellenőrzése
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["message" => "Érvénytelen email formátum."]);
            return;
        }

        // Jelszó hossz ellenőrzése
        if (strlen($data['jelszo']) < 6) {
            http_response_code(400);
            echo json_encode(["message" => "A jelszónak legalább 6 karakter hosszúnak kell lennie."]);
            return;
        }

        // Email létezésének ellenőrzése
        $this->user->email = $data['email'];
        if ($this->user->emailExists()) {
            http_response_code(409);
            echo json_encode(["message" => "Ez az email cím már regisztrálva van."]);
            return;
        }

        // Felhasználó létrehozása
        $this->user->nev = $data['nev'];
        $this->user->jelszo = $data['jelszo'];
        $this->user->profilkep_url = $data['profilkep_url'] ?? null;

        try {
            if ($this->user->create()) {
                // Session létrehozása
                session_start();
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['user_name'] = $this->user->nev;
                $_SESSION['user_email'] = $this->user->email;

                http_response_code(201);
                echo json_encode([
                    "message" => "Sikeres regisztráció.",
                    "user" => [
                        "id" => $this->user->id,
                        "nev" => $this->user->nev,
                        "email" => $this->user->email
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "A regisztráció sikertelen."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // Bejelentkezés
    public function login() {
        $data = getJsonInput();

        // Validáció
        if (empty($data['email']) || empty($data['jelszo'])) {
            http_response_code(400);
            echo json_encode(["message" => "Email és jelszó kötelező."]);
            return;
        }

        // Felhasználó keresése email alapján
        $this->user->email = $data['email'];

        try {
            if ($this->user->findByEmail()) {
                // Jelszó ellenőrzése
                if ($this->user->verifyPassword($data['jelszo'])) {
                    // Session létrehozása
                    session_start();
                    $_SESSION['user_id'] = $this->user->id;
                    $_SESSION['user_name'] = $this->user->nev;
                    $_SESSION['user_email'] = $this->user->email;

                    http_response_code(200);
                    echo json_encode([
                        "message" => "Sikeres bejelentkezés.",
                        "user" => [
                            "id" => $this->user->id,
                            "nev" => $this->user->nev,
                            "email" => $this->user->email,
                            "profilkep_url" => $this->user->profilkep_url
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(["message" => "Hibás jelszó."]);
                }
            } else {
                http_response_code(404);
                echo json_encode(["message" => "A felhasználó nem található."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // Kijelentkezés
    public function logout() {
        session_start();
        session_unset();
        session_destroy();

        http_response_code(200);
        echo json_encode(["message" => "Sikeres kijelentkezés."]);
    }

    // Aktuális felhasználó profiljának lekérése
    public function getProfile() {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["message" => "Nincs bejelentkezve."]);
            return;
        }

        $this->user->id = $_SESSION['user_id'];

        try {
            if ($this->user->read_single()) {
                http_response_code(200);
                echo json_encode([
                    "user" => [
                        "id" => $this->user->id,
                        "nev" => $this->user->nev,
                        "email" => $this->user->email,
                        "profilkep_url" => $this->user->profilkep_url,
                        "letrehozva" => $this->user->letrehozva
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Felhasználó nem található."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // Profil frissítése
    public function updateProfile() {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["message" => "Nincs bejelentkezve."]);
            return;
        }

        $data = getJsonInput();

        $this->user->id = $_SESSION['user_id'];
        $this->user->nev = $data['nev'] ?? '';
        $this->user->email = $data['email'] ?? '';
        $this->user->profilkep_url = $data['profilkep_url'] ?? '';

        // Validáció
        if (empty($this->user->nev) || empty($this->user->email)) {
            http_response_code(400);
            echo json_encode(["message" => "Név és email kötelező."]);
            return;
        }

        // Email formátum ellenőrzése
        if (!filter_var($this->user->email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["message" => "Érvénytelen email formátum."]);
            return;
        }

        try {
            if ($this->user->update()) {
                // Session frissítése
                $_SESSION['user_name'] = $this->user->nev;
                $_SESSION['user_email'] = $this->user->email;

                http_response_code(200);
                echo json_encode(["message" => "Profil sikeresen frissítve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "A profil frissítése sikertelen."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // Jelszó módosítása
    public function changePassword() {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["message" => "Nincs bejelentkezve."]);
            return;
        }

        $data = getJsonInput();

        // Validáció
        if (empty($data['regi_jelszo']) || empty($data['uj_jelszo'])) {
            http_response_code(400);
            echo json_encode(["message" => "Régi és új jelszó kötelező."]);
            return;
        }

        // Új jelszó hossz ellenőrzése
        if (strlen($data['uj_jelszo']) < 6) {
            http_response_code(400);
            echo json_encode(["message" => "Az új jelszónak legalább 6 karakter hosszúnak kell lennie."]);
            return;
        }

        $this->user->id = $_SESSION['user_id'];

        try {
            // Jelenlegi felhasználó betöltése
            $this->user->email = $_SESSION['user_email'];
            
            if ($this->user->findByEmail()) {
                // Régi jelszó ellenőrzése
                if ($this->user->verifyPassword($data['regi_jelszo'])) {
                    // Új jelszó beállítása
                    $this->user->jelszo = $data['uj_jelszo'];
                    
                    if ($this->user->updatePassword()) {
                        http_response_code(200);
                        echo json_encode(["message" => "Jelszó sikeresen megváltoztatva."]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["message" => "A jelszó módosítása sikertelen."]);
                    }
                } else {
                    http_response_code(401);
                    echo json_encode(["message" => "Hibás jelenlegi jelszó."]);
                }
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Felhasználó nem található."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }
}
?>
