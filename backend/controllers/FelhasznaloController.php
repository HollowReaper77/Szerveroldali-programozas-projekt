<?php
class FelhasznaloController {
    private $db;
    private $felhasznalo;

    public function __construct($db) {
        $this->db = $db;
        $this->felhasznalo = new Felhasznalo($db);
    }

    // Regisztráció
    public function register() {
        $data = getJsonInput();

        // Validáció
        if (empty($data['felhasznalonev']) || empty($data['email']) || empty($data['jelszo'])) {
            http_response_code(400);
            echo json_encode(["message" => "Felhasználónév, email és jelszó kötelező."]);
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
        $this->felhasznalo->email = $data['email'];
        if ($this->felhasznalo->emailExists()) {
            http_response_code(409);
            echo json_encode(["message" => "Ez az email cím már regisztrálva van."]);
            return;
        }

        // Felhasználónév létezésének ellenőrzése
        $this->felhasznalo->felhasznalonev = $data['felhasznalonev'];
        if ($this->felhasznalo->usernameExists()) {
            http_response_code(409);
            echo json_encode(["message" => "Ez a felhasználónév már foglalt."]);
            return;
        }

        // Felhasználó létrehozása
        $this->felhasznalo->jelszo = $data['jelszo'];
        $this->felhasznalo->profilkep_url = $data['profilkep_url'] ?? null;
        $this->felhasznalo->jogosultsag = 'user'; // Alapértelmezett jogosultság

        try {
            if ($this->felhasznalo->create()) {
                // Session létrehozása
                $_SESSION['user_id'] = $this->felhasznalo->felhasznalo_id;
                $_SESSION['username'] = $this->felhasznalo->felhasznalonev;
                $_SESSION['user_email'] = $this->felhasznalo->email;
                $_SESSION['user_role'] = $this->felhasznalo->jogosultsag;

                http_response_code(201);
                echo json_encode([
                    "message" => "Sikeres regisztráció.",
                    "felhasznalo" => [
                        "id" => $this->felhasznalo->felhasznalo_id,
                        "felhasznalonev" => $this->felhasznalo->felhasznalonev,
                        "email" => $this->felhasznalo->email,
                        "jogosultsag" => $this->felhasznalo->jogosultsag
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
        $this->felhasznalo->email = $data['email'];

        try {
            if ($this->felhasznalo->findByEmail()) {
                // Aktív státusz ellenőrzése
                if ($this->felhasznalo->aktiv != 1) {
                    http_response_code(403);
                    echo json_encode(["message" => "Ez a fiók inaktív."]);
                    return;
                }

                // Jelszó ellenőrzése
                if ($this->felhasznalo->verifyPassword($data['jelszo'])) {
                    // Session létrehozása
                    $_SESSION['user_id'] = $this->felhasznalo->felhasznalo_id;
                    $_SESSION['username'] = $this->felhasznalo->felhasznalonev;
                    $_SESSION['user_email'] = $this->felhasznalo->email;
                    $_SESSION['user_role'] = $this->felhasznalo->jogosultsag;

                    http_response_code(200);
                    echo json_encode([
                        "message" => "Sikeres bejelentkezés.",
                        "felhasznalo" => [
                            "id" => $this->felhasznalo->felhasznalo_id,
                            "felhasznalonev" => $this->felhasznalo->felhasznalonev,
                            "email" => $this->felhasznalo->email,
                            "profilkep_url" => $this->felhasznalo->profilkep_url,
                            "jogosultsag" => $this->felhasznalo->jogosultsag
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
        session_unset();
        session_destroy();

        http_response_code(200);
        echo json_encode(["message" => "Sikeres kijelentkezés."]);
    }

    // Aktuális felhasználó profiljának lekérése
    public function getProfile() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Nincs bejelentkezve."
            ]);
            return;
        }

        $this->felhasznalo->felhasznalo_id = $_SESSION['user_id'];

        try {
            if ($this->felhasznalo->read_single()) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => [
                        "user" => [
                            "id" => $this->felhasznalo->felhasznalo_id,
                            "felhasznalonev" => $this->felhasznalo->felhasznalonev,
                            "email" => $this->felhasznalo->email,
                            "profilkep_url" => $this->felhasznalo->profilkep_url,
                            "szerepkor" => $this->felhasznalo->jogosultsag,
                            "regisztracio_ideje" => $this->felhasznalo->regisztracio_ideje,
                        ]
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "message" => "Felhasználó nem található."
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Adatbázis hiba: " . $e->getMessage()
            ]);
        }
    }

    // Profil frissítése
    public function updateProfile() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["message" => "Nincs bejelentkezve."]);
            return;
        }

        $data = getJsonInput();

        $this->felhasznalo->felhasznalo_id = $_SESSION['user_id'];
        $this->felhasznalo->felhasznalonev = $data['felhasznalonev'] ?? '';
        $this->felhasznalo->email = $data['email'] ?? '';
        $this->felhasznalo->profilkep_url = $data['profilkep_url'] ?? '';

        // Validáció
        if (empty($this->felhasznalo->felhasznalonev) || empty($this->felhasznalo->email)) {
            http_response_code(400);
            echo json_encode(["message" => "Felhasználónév és email kötelező."]);
            return;
        }

        // Email formátum ellenőrzése
        if (!filter_var($this->felhasznalo->email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["message" => "Érvénytelen email formátum."]);
            return;
        }

        try {
            if ($this->felhasznalo->update()) {
                // Session frissítése
                $_SESSION['username'] = $this->felhasznalo->felhasznalonev;
                $_SESSION['user_email'] = $this->felhasznalo->email;

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

        $this->felhasznalo->felhasznalo_id = $_SESSION['user_id'];

        try {
            // Jelenlegi felhasználó betöltése
            $this->felhasznalo->email = $_SESSION['user_email'];
            
            if ($this->felhasznalo->findByEmail()) {
                // Régi jelszó ellenőrzése
                if ($this->felhasznalo->verifyPassword($data['regi_jelszo'])) {
                    // Új jelszó beállítása
                    $this->felhasznalo->jelszo = $data['uj_jelszo'];
                    
                    if ($this->felhasznalo->updatePassword()) {
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

    // Összes felhasználó listázása (ADMIN)
    public function getAllUsers() {
        // Role ellenőrzés
        if (!requireRole('admin')) {
            return;
        }

        try {
            $stmt = $this->felhasznalo->read();
            $users = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = [
                    "id" => $row['felhasznalo_id'],
                    "felhasznalonev" => $row['felhasznalonev'],
                    "email" => $row['email'],
                    "jogosultsag" => $row['jogosultsag'],
                    "regisztracio_ideje" => $row['regisztracio_ideje'],
                    "aktiv" => $row['aktiv']
                ];
            }

            http_response_code(200);
            echo json_encode(["felhasznalok" => $users]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // Szerepkör módosítása (ADMIN)
    public function updateUserRole($user_id) {
        // Role ellenőrzés
        if (!requireRole('admin')) {
            return;
        }

        $data = getJsonInput();

        if (empty($data['jogosultsag'])) {
            http_response_code(400);
            echo json_encode(["message" => "Jogosultság kötelező."]);
            return;
        }

        // Jogosultság validáció
        $valid_roles = ['user', 'moderator', 'admin'];
        if (!in_array($data['jogosultsag'], $valid_roles)) {
            http_response_code(400);
            echo json_encode(["message" => "Érvénytelen jogosultság. Lehetséges értékek: user, moderator, admin"]);
            return;
        }

        $this->felhasznalo->felhasznalo_id = $user_id;
        $this->felhasznalo->jogosultsag = $data['jogosultsag'];

        try {
            if ($this->felhasznalo->updateRole()) {
                http_response_code(200);
                echo json_encode(["message" => "Jogosultság sikeresen frissítve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "A jogosultság frissítése sikertelen."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // Felhasználó törlése (ADMIN)
    public function deleteUser($user_id) {
        // Role ellenőrzés
        if (!requireRole('admin')) {
            return;
        }

        // Saját fiók törlésének megakadályozása
        if ($_SESSION['user_id'] == $user_id) {
            http_response_code(400);
            echo json_encode(["message" => "Nem törölheted a saját fiókodat."]);
            return;
        }

        $this->felhasznalo->felhasznalo_id = $user_id;

        try {
            if ($this->felhasznalo->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Felhasználó sikeresen törölve (inaktiválva)."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "A felhasználó törlése sikertelen."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }
}
?>
