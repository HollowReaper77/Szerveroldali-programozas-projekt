<?php
require_once __DIR__ . '/../models/rendezo.php';

class RendezoController {

    private $db;
    private $directorModel;

    public function __construct($db) {
        $this->db = $db;
        $this->directorModel = new Rendezo($db);
    }

    // -----------------------------------------------------------
    // GET /directors   (összes rendező)
    // -----------------------------------------------------------
    public function getAllDirectors() {
        $result = $this->directorModel->read();
        $num = $result->rowCount();

        if ($num > 0) {
            $directors = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $directors[] = $row;
            }

            http_response_code(200);
            echo json_encode($directors);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Nincs egyetlen rendező sem az adatbázisban."]);
        }
    }

    // -----------------------------------------------------------
    // GET /directors/{id}
    // -----------------------------------------------------------
    public function getDirector($id) {
        $id = validateId($id, "Rendező ID");
        
        $this->directorModel->rendezo_id = $id;
        $stmt = $this->directorModel->read_single();

        if ($stmt && $stmt->rowCount() > 0) {
            http_response_code(200);

            echo json_encode([
                "rendezo_id"       => $this->directorModel->rendezo_id,
                "nev"              => $this->directorModel->nev,
                "szuletesi_datum"  => $this->directorModel->szuletesi_datum,
                "bio"              => $this->directorModel->bio
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "A rendező nem található."]);
        }
    }

    // -----------------------------------------------------------
    // POST /directors   (új rendező)
    // -----------------------------------------------------------
    public function createDirector() {
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

        $this->directorModel->nev = $data['nev'];
        $this->directorModel->szuletesi_datum = $data['szuletesi_datum'];
        $this->directorModel->bio = $data['bio'] ?? null;

        // Bio validálás, ha van
        if (isset($data['bio'])) {
            validateLength($data['bio'], "Bio", 0, 5000);
        }

        try {
            if ($this->directorModel->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Rendező sikeresen létrehozva."]);
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
    // PUT /directors/{id}
    // -----------------------------------------------------------
    public function updateDirector($id) {
        requireRole('moderator');
        $id = validateId($id, "Rendező ID");
        $data = getJsonInput();

        // Ellenőrizd, hogy létezik-e és töltsd be az adatokat
        $this->directorModel->rendezo_id = $id;
        $stmt = $this->directorModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A rendező nem található."]);
            return;
        }

        // Validálás, ha vannak változások
        if (isset($data['nev'])) {
            validateLength($data['nev'], "Név", 1, 255);
            $this->directorModel->nev = $data['nev'];
        }

        if (isset($data['szuletesi_datum'])) {
            validateDate($data['szuletesi_datum'], "Születési dátum");
            $this->directorModel->szuletesi_datum = $data['szuletesi_datum'];
        }

        if (isset($data['bio'])) {
            validateLength($data['bio'], "Bio", 0, 5000);
            $this->directorModel->bio = $data['bio'];
        }

        try {
            if ($this->directorModel->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Rendező sikeresen frissítve."]);
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
    // DELETE /directors/{id}
    // -----------------------------------------------------------
    public function deleteDirector($id) {
        requireRole('moderator');
        $id = validateId($id, "Rendező ID");
        
        // Ellenőrizd, hogy létezik-e
        $this->directorModel->rendezo_id = $id;
        $stmt = $this->directorModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A rendező nem található."]);
            return;
        }

        try {
            if ($this->directorModel->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Rendező törölve."]);
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