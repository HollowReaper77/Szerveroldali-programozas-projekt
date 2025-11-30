<?php

require_once __DIR__ . '/../models/szinesz.php';

class SzineszController {

    private $db;
    private $actorModel;

    public function __construct($db) {
        $this->db = $db;
        $this->actorModel = new Szinesz($db);
    }

    // -----------------------------------------------------------
    // GET /actors   (összes színész - pagination)
    // -----------------------------------------------------------
    public function getAllActors() {
        // Pagination paraméterek
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 50;
        
        $offset = ($page - 1) * $limit;
        
        $result = $this->actorModel->read($limit, $offset);
        $num = $result->rowCount();
        
        $total = $this->actorModel->count();
        $totalPages = ceil($total / $limit);

        if ($num > 0) {
            $actors = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $actors[] = $row;
            }

            http_response_code(200);
            echo json_encode([
                "data" => $actors,
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
            echo json_encode(["message" => "Nincs egyetlen színész sem az adatbázisban."]);
        }
    }

    // -----------------------------------------------------------
    // GET /actors/{id}
    // -----------------------------------------------------------
    public function getActor($id) {
        $id = validateId($id, "Színész ID");
        
        $this->actorModel->szinesz_id = $id;
        $stmt = $this->actorModel->read_single();

        if ($stmt && $stmt->rowCount() > 0) {
            http_response_code(200);

            echo json_encode([
                "szinesz_id"       => $this->actorModel->szinesz_id,
                "nev"              => $this->actorModel->nev,
                "szuletesi_datum"  => $this->actorModel->szuletesi_datum,
                "bio"              => $this->actorModel->bio
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "A színész nem található."]);
        }
    }

    // -----------------------------------------------------------
    // POST /actors   (új színész)
    // -----------------------------------------------------------
    public function createActor() {
        $data = getJsonInput();

        // Kötelező mezők
        if (!isset($data->nev) || !isset($data->szuletesi_datum)) {
            http_response_code(400);
            echo json_encode(["message" => "A 'nev' és 'szuletesi_datum' mezők kötelezőek."]);
            return;
        }

        // Validálás
        validateLength($data->nev, "Név", 1, 255);
        validateDate($data->szuletesi_datum, "Születési dátum");

        $this->actorModel->nev = $data->nev;
        $this->actorModel->szuletesi_datum = $data->szuletesi_datum;
        $this->actorModel->bio = $data->bio ?? null;

        // Bio validálás, ha van
        if (isset($data->bio)) {
            validateLength($data->bio, "Bio", 0, 5000);
        }

        if ($this->actorModel->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Színész sikeresen létrehozva."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt a létrehozás során."]);
        }
    }

    // -----------------------------------------------------------
    // PUT /actors/{id}
    // -----------------------------------------------------------
    public function updateActor($id) {
        $id = validateId($id, "Színész ID");
        $data = getJsonInput();

        // Ellenőrizd, hogy létezik-e és töltsd be az adatokat
        $this->actorModel->szinesz_id = $id;
        $stmt = $this->actorModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A színész nem található."]);
            return;
        }

        // Validálás, ha vannak változások
        if (isset($data->nev)) {
            validateLength($data->nev, "Név", 1, 255);
            $this->actorModel->nev = $data->nev;
        }

        if (isset($data->szuletesi_datum)) {
            validateDate($data->szuletesi_datum, "Születési dátum");
            $this->actorModel->szuletesi_datum = $data->szuletesi_datum;
        }

        if (isset($data->bio)) {
            validateLength($data->bio, "Bio", 0, 5000);
            $this->actorModel->bio = $data->bio;
        }

        if ($this->actorModel->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Színész sikeresen frissítve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba a frissítés során."]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /actors/{id}
    // -----------------------------------------------------------
    public function deleteActor($id) {
        $id = validateId($id, "Színész ID");
        
        // Ellenőrizd, hogy létezik-e
        $this->actorModel->szinesz_id = $id;
        $stmt = $this->actorModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A színész nem található."]);
            return;
        }

        if ($this->actorModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Színész törölve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba a törlés során."]);
        }
    }
}
?>