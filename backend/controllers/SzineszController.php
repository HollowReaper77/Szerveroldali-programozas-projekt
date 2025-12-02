<?php

require_once __DIR__ . '/../models/szinesz.php';

class SzineszController {

    private $db;
    private $szinesz;

    public function __construct($db) {
        $this->db = $db;
        $this->szinesz = new Szinesz($db);
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
        
        $total = $this->szinesz->count();
        $totalPages = ceil($total / $limit);
        
        // Ellenőrizd, hogy a page ne legyen nagyobb mint a maximális oldalszám
        if ($totalPages > 0 && $page > $totalPages) {
            http_response_code(400);
            echo json_encode(["message" => "Az oldalszám túl nagy. Maximum {$totalPages} oldal létezik."]);
            return;
        }
        
        $result = $this->szinesz->read($limit, $offset);
        $num = $result->rowCount();

        if ($num > 0) {
            $szineszek = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $szineszek[] = $row;
            }

            http_response_code(200);
            echo json_encode([
                "szineszek" => $szineszek,
                "count" => $total
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
        
        $this->szinesz->szinesz_id = $id;
        $stmt = $this->szinesz->read_single();

        if ($stmt && $stmt->rowCount() > 0) {
            http_response_code(200);

            echo json_encode([
                "szinesz" => [
                    "szinesz_id"       => $this->szinesz->szinesz_id,
                    "nev"              => $this->szinesz->nev,
                    "szuletesi_datum"  => $this->szinesz->szuletesi_datum,
                    "bio"              => $this->szinesz->bio
                ]
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
        requireRole('moderator');
        $data = getJsonInput();

        // Kötelező mezők
        if (!isset($data['nev']) || !isset($data['szuletesi_datum'])) {
            http_response_code(400);
            echo json_encode(["message" => "A 'nev' és 'szuletesi_datum' mezők kötelezőek."]);
            return;
        }

        // Validálás
        validateLength($data['nev'], "Név", 1, 255);
        validateDate($data['szuletesi_datum'], "Születési dátum");

        $this->szinesz->nev = $data['nev'];
        $this->szinesz->szuletesi_datum = $data['szuletesi_datum'];
        $this->szinesz->bio = $data['bio'] ?? null;

        // Bio validálás, ha van
        if (isset($data['bio'])) {
            validateLength($data['bio'], "Bio", 0, 5000);
        }

        try {
            if ($this->szinesz->create()) {
                http_response_code(201);
                echo json_encode([
                    "message"         => "Színész sikeresen létrehozva.",
                    "szinesz_id"      => $this->szinesz->szinesz_id,
                    "nev"             => $this->szinesz->nev,
                    "szuletesi_datum" => $this->szinesz->szuletesi_datum,
                    "bio"             => $this->szinesz->bio
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba történt a létrehozás során."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------
    // PUT /actors/{id}
    // -----------------------------------------------------------
    public function updateActor($id) {
        requireRole('moderator');
        $id = validateId($id, "Színész ID");
        $data = getJsonInput();

        // Ellenőrizd, hogy létezik-e és töltsd be az adatokat
        $this->szinesz->szinesz_id = $id;
        $stmt = $this->szinesz->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A színész nem található."]);
            return;
        }

        // Validálás, ha vannak változások
        if (isset($data['nev'])) {
            validateLength($data['nev'], "Név", 1, 255);
            $this->szinesz->nev = $data['nev'];
        }

        if (isset($data['szuletesi_datum'])) {
            validateDate($data['szuletesi_datum'], "Születési dátum");
            $this->szinesz->szuletesi_datum = $data['szuletesi_datum'];
        }

        if (isset($data['bio'])) {
            validateLength($data['bio'], "Bio", 0, 5000);
            $this->szinesz->bio = $data['bio'];
        }

        try {
            if ($this->szinesz->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Színész sikeresen frissítve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba a frissítés során."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /actors/{id}
    // -----------------------------------------------------------
    public function deleteActor($id) {
        requireRole('moderator');
        $id = validateId($id, "Színész ID");
        
        // Ellenőrizd, hogy létezik-e
        $this->szinesz->szinesz_id = $id;
        $stmt = $this->szinesz->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["error" => "A színész nem található."]);
            return;
        }

        try {
            if ($this->szinesz->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Színész törölve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba a törlés során."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }
}
?>