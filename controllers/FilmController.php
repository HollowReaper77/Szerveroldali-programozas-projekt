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
        
        // Filmek lekérdezése
        $result = $this->filmModel->read($limit, $offset);
        $num = $result->rowCount();
        
        // Összes film száma
        $total = $this->filmModel->count();
        $totalPages = ceil($total / $limit);

        if ($num > 0) {
            $films = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $films[] = $row;
            }

            http_response_code(200);
            echo json_encode([
                "data" => $films,
                "pagination" => [
                    "current_page" => $page,
                    "per_page" => $limit,
                    "total_items" => $total,
                    "total_pages" => $totalPages,
                    "has_next" => $page < $totalPages,
                    "has_prev" => $page > 1
                ]
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
        $this->filmModel->film_id = $id;
        $stmt = $this->filmModel->read_single();

        if ($stmt && $stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                "film_id" => $this->filmModel->film_id,
                "cim" => $this->filmModel->cim,
                "idotartam" => $this->filmModel->idotartam,
                "poszter_url" => $this->filmModel->poszter_url,
                "leiras" => $this->filmModel->leiras,
                "kiadasi_ev" => $this->filmModel->kiadasi_ev
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
        $data = getJsonInput();

        // Kötelező mezők ellenőrzése
        if (
            !isset($data->cim) ||
            !isset($data->idotartam) ||
            !isset($data->poszter_url) ||
            !isset($data->leiras) ||
            !isset($data->kiadasi_ev)
        ) {
            http_response_code(400);
            echo json_encode(["message" => "Hiányzó mezők."]);
            return;
        }

        // Validálás helper függvényekkel
        validateLength($data->cim, "Cím", 1, 255);
        validateNumber($data->idotartam, "Időtartam", 1, 999);
        validateNumber($data->kiadasi_ev, "Kiadási év", 1888, date('Y') + 5);
        validateUrl($data->poszter_url, "Poszter URL");
        validateLength($data->leiras, "Leírás", 0, 2000);

        $this->filmModel->cim = $data->cim;
        $this->filmModel->idotartam = $data->idotartam;
        $this->filmModel->poszter_url = $data->poszter_url;
        $this->filmModel->leiras = $data->leiras;
        $this->filmModel->kiadasi_ev = $data->kiadasi_ev;

        if ($this->filmModel->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Film sikeresen létrehozva."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba a film létrehozása közben."]);
        }
    }

    // -----------------------------------------------------------
    // PUT /films/{id}    (film frissítése)
    // -----------------------------------------------------------
    public function updateFilm($id) {
        $data = getJsonInput();

        // Ellenőrizd, hogy létezik-e a film
        $this->filmModel->film_id = $id;
        $stmt = $this->filmModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A film nem található."]);
            return;
        }

        // Ha van cím, validáld
        if (isset($data->cim)) {
            validateLength($data->cim, "Cím", 1, 255);
            $this->filmModel->cim = $data->cim;
        }

        // Ha van időtartam, validáld
        if (isset($data->idotartam)) {
            validateNumber($data->idotartam, "Időtartam", 1, 999);
            $this->filmModel->idotartam = $data->idotartam;
        }

        // Ha van poszter URL, validáld
        if (isset($data->poszter_url)) {
            validateUrl($data->poszter_url, "Poszter URL");
            $this->filmModel->poszter_url = $data->poszter_url;
        }

        // Ha van leírás, validáld
        if (isset($data->leiras)) {
            validateLength($data->leiras, "Leírás", 0, 2000);
            $this->filmModel->leiras = $data->leiras;
        }

        // Ha van kiadási év, validáld
        if (isset($data->kiadasi_ev)) {
            validateNumber($data->kiadasi_ev, "Kiadási év", 1888, date('Y') + 5);
            $this->filmModel->kiadasi_ev = $data->kiadasi_ev;
        }

        if ($this->filmModel->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Film sikeresen frissítve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba a film frissítése közben."]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /films/{id}
    // -----------------------------------------------------------
    public function deleteFilm($id) {
        // Ellenőrizd, hogy létezik-e a film
        $this->filmModel->film_id = $id;
        $stmt = $this->filmModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A film nem található."]);
            return;
        }

        if ($this->filmModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Film törölve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba a film törlése közben."]);
        }
    }
}
?>