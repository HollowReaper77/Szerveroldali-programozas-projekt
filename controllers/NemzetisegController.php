<?php

require_once __DIR__ . '/../core/orszag.php';

class NemzetisegController {

    private $db;
    private $countryModel;

    public function __construct($db) {
        $this->db = $db;
        $this->countryModel = new Orszag($db);
    }

    // -----------------------------------------------------------
    // GET /countries (összes ország)
    // -----------------------------------------------------------
    public function getAllCountries() {
        $result = $this->countryModel->read();
        $num = $result->rowCount();

        if ($num > 0) {
            $countries = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $countries[] = $row;
            }

            http_response_code(200);
            echo json_encode($countries);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Nincs egyetlen ország sem az adatbázisban."]);
        }
    }

    // -----------------------------------------------------------
    // GET /countries/{id}
    // -----------------------------------------------------------
    public function getCountry($id) {
        $this->countryModel->orszag_id = $id;
        $stmt = $this->countryModel->read_single();

        if ($stmt && $stmt->rowCount() > 0) {
            http_response_code(200);

            echo json_encode([
                "orszag_id" => $this->countryModel->orszag_id,
                "nev"       => $this->countryModel->nev
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Az ország nem található."]);
        }
    }

    // -----------------------------------------------------------
    // POST /countries  (új ország)
    // -----------------------------------------------------------
    public function createCountry() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->nev)) {
            http_response_code(400);
            echo json_encode(["message" => "A 'nev' mező kötelező."]);
            return;
        }

        $this->countryModel->nev = $data->nev;

        if ($this->countryModel->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Ország sikeresen létrehozva."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt a létrehozás során."]);
        }
    }

    // -----------------------------------------------------------
    // PUT /countries/{id}
    // -----------------------------------------------------------
    public function updateCountry($id) {
        $data = json_decode(file_get_contents("php://input"));

        $this->countryModel->orszag_id = $id;
        $this->countryModel->nev = $data->nev ?? null;

        if ($this->countryModel->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Ország sikeresen frissítve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt a frissítés során."]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /countries/{id}
    // -----------------------------------------------------------
    public function deleteCountry($id) {
        $this->countryModel->orszag_id = $id;

        if ($this->countryModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Ország törölve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt a törlés során."]);
        }
    }
}
