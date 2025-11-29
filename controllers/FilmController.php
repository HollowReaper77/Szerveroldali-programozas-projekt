<?php

require_once __DIR__ . '/../core/Film.php';

class FilmController {

    private $db;
    private $filmModel;

    public function __construct($db) {
        $this->db = $db;
        $this->filmModel = new Film($db);
    }

    // -----------------------------------------------------------
    // GET /films      (összes film)
    // -----------------------------------------------------------
    public function getAllFilms() {
        $result = $this->filmModel->read();
        $num = $result->rowCount();

        if ($num > 0) {
            $films = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $films[] = $row;
            }

            http_response_code(200);
            echo json_encode($films);
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
        $data = json_decode(file_get_contents("php://input"));

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
        $data = json_decode(file_get_contents("php://input"));

        $this->filmModel->film_id = $id;

        $this->filmModel->cim = $data->cim ?? null;
        $this->filmModel->idotartam = $data->idotartam ?? null;
        $this->filmModel->poszter_url = $data->poszter_url ?? null;
        $this->filmModel->leiras = $data->leiras ?? null;
        $this->filmModel->kiadasi_ev = $data->kiadasi_ev ?? null;

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
        $this->filmModel->film_id = $id;

        if ($this->filmModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Film törölve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba a film törlése közben."]);
        }
    }
}
