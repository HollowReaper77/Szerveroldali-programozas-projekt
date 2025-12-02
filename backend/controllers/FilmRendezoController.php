<?php

require_once __DIR__ . '/../models/film_rendezo.php';

class FilmRendezoController {

    private $db;
    private $model;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new FilmRendezo($db);
    }

    public function getDirectorsByFilm($filmId) {
        $filmId = validateId($filmId, "Film ID");

        $this->model->film_id = $filmId;
        $result = $this->model->getDirectorsByFilm();

        if ($result->rowCount() > 0) {
            $directors = [];
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $directors[] = $row;
            }

            http_response_code(200);
            echo json_encode($directors);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Ehhez a filmhez nincs rendező társítva."]);
        }
    }

    public function getFilmsByDirector($directorId) {
        $directorId = validateId($directorId, "Rendező ID");

        $this->model->rendezo_id = $directorId;
        $result = $this->model->getFilmsByDirector();

        if ($result->rowCount() > 0) {
            $films = [];
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $films[] = $row;
            }

            http_response_code(200);
            echo json_encode($films);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Ez a rendező nincs filmhez társítva."]);
        }
    }

    public function addDirectorToFilm() {
        requireRole('moderator');
        $data = getJsonInput();

        if (!isset($data['film_id']) || !isset($data['rendezo_id'])) {
            http_response_code(400);
            echo json_encode(["message" => "A film_id és rendezo_id mezők kötelezőek."]);
            return;
        }

        validateNumber($data['film_id'], "Film ID", 1);
        validateNumber($data['rendezo_id'], "Rendező ID", 1);

        $filmCheck = $this->db->prepare("SELECT film_id FROM film WHERE film_id = ?");
        $filmCheck->execute([$data['film_id']]);
        if ($filmCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A megadott film nem található."]);
            return;
        }

        $directorCheck = $this->db->prepare("SELECT rendezo_id FROM rendezok WHERE rendezo_id = ?");
        $directorCheck->execute([$data['rendezo_id']]);
        if ($directorCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A megadott rendező nem található."]);
            return;
        }

        $this->model->film_id = $data['film_id'];
        $this->model->rendezo_id = $data['rendezo_id'];

        try {
            if ($this->model->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Rendező sikeresen hozzáadva a filmhez."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba történt a hozzárendelés során. Lehet, hogy már létezik ez a kapcsolat."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    public function removeDirectorFromFilm($filmId = null, $directorId = null) {
        requireRole('moderator');

        if ($filmId !== null && $directorId !== null) {
            $filmId = validateId($filmId, "Film ID");
            $directorId = validateId($directorId, "Rendező ID");
            $this->model->film_id = $filmId;
            $this->model->rendezo_id = $directorId;
        } else {
            $data = getJsonInput();

            if (!isset($data['film_id']) || !isset($data['rendezo_id'])) {
                http_response_code(400);
                echo json_encode(["message" => "A film_id és rendezo_id mezők kötelezőek a törléshez."]);
                return;
            }

            validateNumber($data['film_id'], "Film ID", 1);
            validateNumber($data['rendezo_id'], "Rendező ID", 1);

            $this->model->film_id = $data['film_id'];
            $this->model->rendezo_id = $data['rendezo_id'];
        }

        try {
            if ($this->model->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Rendező eltávolítva a filmből."]);
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
