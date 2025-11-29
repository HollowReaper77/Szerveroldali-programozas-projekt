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
    // GET /actors   (összes színész)
    // -----------------------------------------------------------
    public function getAllActors() {
        $result = $this->actorModel->read();
        $num = $result->rowCount();

        if ($num > 0) {
            $actors = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $actors[] = $row;
            }

            http_response_code(200);
            echo json_encode($actors);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Nincs egyetlen színész sem az adatbázisban."]);
        }
    }

    // -----------------------------------------------------------
    // GET /actors/{id}
    // -----------------------------------------------------------
    public function getActor($id) {
        $this->actorModel->szinesz_id = $id;
        $stmt = $this->actorModel->read_single();

        if ($stmt && $stmt->rowCount() > 0) {
            http_response_code(200);

            echo json_encode([
                "szinesz_id"       => $this->actorModel->szinesz_id,
                "nev"              => $this->actorModel->nev,
                "szuletesi_datum"  => $this->actorModel->szuletesi_datum
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
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->nev) || !isset($data->szuletesi_datum)) {
            http_response_code(400);
            echo json_encode(["message" => "A 'nev' és 'szuletesi_datum' mezők kötelezőek."]);
            return;
        }

        $this->actorModel->nev = $data->nev;
        $this->actorModel->szuletesi_datum = $data->szuletesi_datum;

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
        $data = json_decode(file_get_contents("php://input"));

        $this->actorModel->szinesz_id = $id;

        $this->actorModel->nev = $data->nev ?? null;
        $this->actorModel->szuletesi_datum = $data->szuletesi_datum ?? null;

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
        $this->actorModel->szinesz_id = $id;

        if ($this->actorModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Színész törölve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba a törlés során."]);
        }
    }
}
