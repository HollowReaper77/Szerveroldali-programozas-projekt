<?php

require_once __DIR__ . '/../models/szereplo.php';

class SzereploController {

    private $db;
    private $castModel;

    public function __construct($db) {
        $this->db = $db;
        $this->castModel = new Szereplo($db);
    }

    // -----------------------------------------------------------
    // GET /film-actors/film/{film_id}
    // Színészek listája egy filmhez
    // -----------------------------------------------------------
    public function getActorsByFilm($filmId) {
        $filmId = validateId($filmId, "Film ID");
        
        $this->castModel->film_id = $filmId;
        $result = $this->castModel->getActorsByFilm();

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
        $actorId = validateId($actorId, "Színész ID");
        
        $this->castModel->szinesz_id = $actorId;
        $result = $this->castModel->getFilmsByActor();

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
        $data = getJsonInput();

        if (!isset($data->film_id) || !isset($data->szinesz_id)) {
            http_response_code(400);
            echo json_encode(["message" => "A film_id és szinesz_id mezők kötelezőek."]);
            return;
        }

        // Validálás - számok legyenek
        validateNumber($data->film_id, "Film ID", 1);
        validateNumber($data->szinesz_id, "Színész ID", 1);

        // Ellenőrizd, hogy a film létezik-e
        $filmCheck = $this->db->prepare("SELECT film_id FROM film WHERE film_id = ?");
        $filmCheck->execute([$data->film_id]);
        if ($filmCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A megadott film nem található."]);
            return;
        }

        // Ellenőrizd, hogy a színész létezik-e
        $actorCheck = $this->db->prepare("SELECT szinesz_id FROM szineszek WHERE szinesz_id = ?");
        $actorCheck->execute([$data->szinesz_id]);
        if ($actorCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A megadott színész nem található."]);
            return;
        }

        $this->castModel->film_id = $data->film_id;
        $this->castModel->szinesz_id = $data->szinesz_id;

        try {
            if ($this->castModel->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Színész sikeresen hozzáadva a filmhez."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba történt a hozzárendelés során. (Lehet, hogy már hozzá van rendelve?)"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /film-actors
    // Színész eltávolítása egy filmből
    // -----------------------------------------------------------
    public function removeActorFromFilm() {
        $data = getJsonInput();

        if (!isset($data->film_id) || !isset($data->szinesz_id)) {
            http_response_code(400);
            echo json_encode(["message" => "A film_id és szinesz_id mezők kötelezőek a törléshez."]);
            return;
        }

        validateNumber($data->film_id, "Film ID", 1);
        validateNumber($data->szinesz_id, "Színész ID", 1);

        $this->castModel->film_id = $data->film_id;
        $this->castModel->szinesz_id = $data->szinesz_id;

        try {
            if ($this->castModel->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Színész eltávolítva a filmből."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "A kapcsolat nem található vagy már törölve lett."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }
}
?>