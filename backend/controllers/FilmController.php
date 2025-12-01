<?php

require_once __DIR__ . '/../models/film.php';

class FilmController {

    private $db;
    private $filmModel;

    public function __construct($db) {
        $this->db = $db;
        $this->filmModel = new Film($db);
    }

    // -----------------------------------------------------------
    // GET /films      (összes film - pagination)
    // -----------------------------------------------------------
    public function getAllFilms() {
        // Pagination paraméterek
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        
        // Validálás
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 20;
        
        $offset = ($page - 1) * $limit;
        
        // Összes film száma
        $total = $this->filmModel->count();
        $totalPages = ceil($total / $limit);
        
        // Ellenőrizd, hogy a page ne legyen nagyobb mint a maximális oldalszám
        if ($totalPages > 0 && $page > $totalPages) {
            http_response_code(400);
            echo json_encode(["message" => "Az oldalszám túl nagy. Maximum {$totalPages} oldal létezik."]);
            return;
        }
        
        // Filmek lekérdezése
        $result = $this->filmModel->read($limit, $offset);
        $num = $result->rowCount();

        if ($num > 0) {
            $films = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $films[] = $row;
            }

            http_response_code(200);
            echo json_encode([
                "filmek" => $films,
                "count" => $total
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Nincs egyetlen film sem az adatbázisban."]);
        }
    }

    // -----------------------------------------------------------
    // GET /films/{id}    (egy film)
    // -----------------------------------------------------------
    public function getFilm($id) {
        $id = validateId($id, "Film ID");
        
        $this->filmModel->film_id = $id;
        $stmt = $this->filmModel->read_single();

        if ($stmt && $stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                "film" => [
                    "film_id" => $this->filmModel->film_id,
                    "cim" => $this->filmModel->cim,
                    "idotartam" => $this->filmModel->idotartam,
                    "poszter_url" => $this->filmModel->poszter_url,
                    "leiras" => $this->filmModel->leiras,
                    "kiadasi_ev" => $this->filmModel->kiadasi_ev
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "A film nem található."]);
        }
    }

    // -----------------------------------------------------------
    // POST /films      (új film)
    // -----------------------------------------------------------
    public function createFilm() {
        // Jogosultság ellenőrzés - csak admin és moderátor
        if (!requireRole('moderator')) {
            return;
        }

        $data = getJsonInput();

        // Kötelező mezők ellenőrzése
        if (
            !isset($data['cim']) ||
            !isset($data['idotartam']) ||
            !isset($data['poszter_url']) ||
            !isset($data['leiras']) ||
            !isset($data['kiadasi_ev'])
        ) {
            http_response_code(400);
            echo json_encode(["message" => "Hiányzó mezők."]);
            return;
        }

        // Validálás helper függvényekkel
        validateLength($data['cim'], "Cím", 1, 255);
        validateNumber($data['idotartam'], "Időtartam", 1, 999);
        validateNumber($data['kiadasi_ev'], "Kiadási év", 1888, date('Y') + 5);
        validateUrl($data['poszter_url'], "Poszter URL");
        validateLength($data['leiras'], "Leírás", 0, 2000);

        $this->filmModel->cim = $data['cim'];
        $this->filmModel->idotartam = $data['idotartam'];
        $this->filmModel->poszter_url = $data['poszter_url'];
        $this->filmModel->leiras = $data['leiras'];
        $this->filmModel->kiadasi_ev = $data['kiadasi_ev'];

        try {
            if ($this->filmModel->create()) {
                http_response_code(201);
                echo json_encode([
                    "message"     => "Film sikeresen létrehozva.",
                    "film_id"     => $this->filmModel->film_id,
                    "cim"         => $this->filmModel->cim,
                    "idotartam"   => $this->filmModel->idotartam,
                    "poszter_url" => $this->filmModel->poszter_url,
                    "leiras"      => $this->filmModel->leiras,
                    "kiadasi_ev"  => $this->filmModel->kiadasi_ev
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba a film létrehozása közben."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------
    // PUT /films/{id}    (film frissítése)
    // -----------------------------------------------------------
    public function updateFilm($id) {
        // Jogosultság ellenőrzés - csak admin és moderátor
        if (!requireRole('moderator')) {
            return;
        }

        $id = validateId($id, "Film ID");
        $data = getJsonInput();

        // Ellenőrizd, hogy létezik-e a film és töltsd be az adatokat
        $this->filmModel->film_id = $id;
        $stmt = $this->filmModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A film nem található."]);
            return;
        }

        // Ha van cím, validáld és frissítsd
        if (isset($data['cim'])) {
            validateLength($data['cim'], "Cím", 1, 255);
            $this->filmModel->cim = $data['cim'];
        }
        // különben megtartjuk a jelenlegi értéket (már be van töltve read_single()-ből)

        // Ha van időtartam, validáld és frissítsd
        if (isset($data['idotartam'])) {
            validateNumber($data['idotartam'], "Időtartam", 1, 999);
            $this->filmModel->idotartam = $data['idotartam'];
        }

        // Ha van poszter URL, validáld és frissítsd
        if (isset($data['poszter_url'])) {
            validateUrl($data['poszter_url'], "Poszter URL");
            $this->filmModel->poszter_url = $data['poszter_url'];
        }

        // Ha van leírás, validáld és frissítsd
        if (isset($data['leiras'])) {
            validateLength($data['leiras'], "Leírás", 0, 2000);
            $this->filmModel->leiras = $data['leiras'];
        }

        // Ha van kiadási év, validáld és frissítsd
        if (isset($data['kiadasi_ev'])) {
            validateNumber($data['kiadasi_ev'], "Kiadási év", 1888, date('Y') + 5);
            $this->filmModel->kiadasi_ev = $data['kiadasi_ev'];
        }

        try {
            if ($this->filmModel->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Film sikeresen frissítve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba a film frissítése közben."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /films/{id}
    // -----------------------------------------------------------
    public function deleteFilm($id) {
        // Jogosultság ellenőrzés - csak admin és moderátor
        if (!requireRole('moderator')) {
            return;
        }

        $id = validateId($id, "Film ID");
        
        // Ellenőrizd, hogy létezik-e a film
        $this->filmModel->film_id = $id;
        $stmt = $this->filmModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["error" => "A film nem található."]);
            return;
        }

        try {
            if ($this->filmModel->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Film törölve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba a film törlése közben."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }
}
?>