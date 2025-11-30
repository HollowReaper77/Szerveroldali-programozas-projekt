<?php

require_once __DIR__ . '/../models/mufaj.php';

class MufajController {

    private $db;
    private $genreModel;

    public function __construct($db) {
        $this->db = $db;
        $this->genreModel = new Mufaj($db);
    }

    // -----------------------------------------------------------
    // GET /genres   (összes műfaj)
    // -----------------------------------------------------------
    public function getAllGenres() {
        $result = $this->genreModel->read();
        $num = $result->rowCount();

        if ($num > 0) {
            $genres = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $genres[] = $row;
            }

            http_response_code(200);
            echo json_encode(["mufajok" => $genres]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Nincs egyetlen műfaj sem az adatbázisban."]);
        }
    }

    // -----------------------------------------------------------
    // GET /genres/{id}
    // -----------------------------------------------------------
    public function getGenre($id) {
        $id = validateId($id, "Műfaj ID");
        
        $this->genreModel->mufaj_id = $id;
        $stmt = $this->genreModel->read_single();

        if ($stmt && $stmt->rowCount() > 0) {
            http_response_code(200);

            echo json_encode([
                "mufaj" => [
                    "mufaj_id" => $this->genreModel->mufaj_id,
                    "nev"      => $this->genreModel->nev
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "A műfaj nem található."]);
        }
    }

    // -----------------------------------------------------------
    // POST /genres   (új műfaj)
    // -----------------------------------------------------------
    public function createGenre() {
        $data = getJsonInput();

        if (!isset($data['nev'])) {
            http_response_code(400);
            echo json_encode(["message" => "A 'nev' mező kötelező."]);
            return;
        }

        validateLength($data['nev'], "Név", 1, 100);

        $this->genreModel->nev = $data['nev'];

        try {
            if ($this->genreModel->create()) {
                http_response_code(201);
                echo json_encode([
                    "message"  => "Műfaj sikeresen létrehozva.",
                    "mufaj_id" => $this->genreModel->mufaj_id,
                    "nev"      => $this->genreModel->nev
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
    // PUT /genres/{id}
    // -----------------------------------------------------------
    public function updateGenre($id) {
        $id = validateId($id, "Műfaj ID");
        $data = getJsonInput();

        // Ellenőrizd, hogy létezik-e és töltsd be az adatokat
        $this->genreModel->mufaj_id = $id;
        $stmt = $this->genreModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A műfaj nem található."]);
            return;
        }

        if (isset($data['nev'])) {
            validateLength($data['nev'], "Név", 1, 100);
            $this->genreModel->nev = $data['nev'];
        }

        try {
            if ($this->genreModel->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Műfaj sikeresen frissítve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba történt a frissítés során."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /genres/{id}
    // -----------------------------------------------------------
    public function deleteGenre($id) {
        $id = validateId($id, "Műfaj ID");
        
        // Ellenőrizd, hogy létezik-e
        $this->genreModel->mufaj_id = $id;
        $stmt = $this->genreModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A műfaj nem található."]);
            return;
        }

        try {
            if ($this->genreModel->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Műfaj törölve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba történt a törlés során."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }
}
?>