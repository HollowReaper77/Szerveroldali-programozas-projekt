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
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->nev) || !isset($data->szuletesi_datum)) {
            http_response_code(400);
            echo json_encode(["message" => "A 'nev' és 'szuletesi_datum' mezők kötelezőek."]);
            return;
        }

        $this->directorModel->nev = $data->nev;
        $this->directorModel->szuletesi_datum = $data->szuletesi_datum;
        $this->directorModel->bio = $data->bio ?? null;

        if ($this->directorModel->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Rendező sikeresen létrehozva."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt a létrehozás során."]);
        }
    }

    // -----------------------------------------------------------
    // PUT /directors/{id}
    // -----------------------------------------------------------
    public function updateDirector($id) {
        $data = json_decode(file_get_contents("php://input"));

        $this->directorModel->rendezo_id = $id;

        $this->directorModel->nev = $data->nev ?? null;
        $this->directorModel->szuletesi_datum = $data->szuletesi_datum ?? null;
        $this->directorModel->bio = $data->bio ?? null;

        if ($this->directorModel->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Rendező sikeresen frissítve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba a frissítés során."]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /directors/{id}
    // -----------------------------------------------------------
    public function deleteDirector($id) {
        $this->directorModel->rendezo_id = $id;

        if ($this->directorModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Rendező törölve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba a törlés során."]);
        }
    }
}