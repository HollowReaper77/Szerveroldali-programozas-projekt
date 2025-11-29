<?php

require_once __DIR__ . '/../core/szereplo.php';

class SzereploController {

    private $db;
    private $castModel;

    public function __construct($db) {
        $this->db = $db;
        $this->castModel = new Szereplo($db);  // <-- jó modell
    }

    // -----------------------------------------------------------
    // GET /film-actors/film/{film_id}
    // Színészek listája egy filmhez
    // -----------------------------------------------------------
    public function getActorsByFilm($filmId) {
        $this->castModel->film_id = $filmId;
        $result = $this->castModel->getActorsByFilm();   // <-- javítva!

        if ($result->rowCount() > 0) {
            $actors = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $actors[] = $row;
            }

            http_response_code(200);
            echo json_encode($actors);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Ehhez a filmhez nincs színész társítva."]);
        }
    }

    // -----------------------------------------------------------
    // GET /film-actors/actor/{actor_id}
    // Filmek listája egy színészhez
    // -----------------------------------------------------------
    public function getFilmsByActor($actorId) {
        $this->castModel->szinesz_id = $actorId;
        $result = $this->castModel->getFilmsByActor();   // <-- javítva!

        if ($result->rowCount() > 0) {
            $films = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $films[] = $row;
            }

            http_response_code(200);
            echo json_encode($films);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Ez a színész nincs egyetlen filmhez sem társítva."]);
        }
    }

    // -----------------------------------------------------------
    // POST /film-actors
    // Színész hozzáadása egy filmhez
    // -----------------------------------------------------------
    public function addActorToFilm() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->film_id) || !isset($data->szinesz_id)) {
            http_response_code(400);
            echo json_encode(["message" => "A film_id és szinesz_id mezők kötelezőek."]);
            return;
        }

        $this->castModel->film_id = $data->film_id;
        $this->castModel->szinesz_id = $data->szinesz_id;

        if ($this->castModel->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Színész sikeresen hozzáadva a filmhez."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt a hozzárendelés során."]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /film-actors
    // Színész eltávolítása egy filmből
    // -----------------------------------------------------------
    public function removeActorFromFilm() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->film_id) || !isset($data->szinesz_id)) {
            http_response_code(400);
            echo json_encode(["message" => "A film_id és szinesz_id mezők kötelezőek a törléshez."]);
            return;
        }

        $this->castModel->film_id = $data->film_id;
        $this->castModel->szinesz_id = $data->szinesz_id;

        if ($this->castModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Színész eltávolítva a filmből."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt az eltávolítás során."]);
        }
    }
}
?>