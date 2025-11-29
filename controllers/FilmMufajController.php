
<?php

require_once __DIR__ . '/../models/film_mufaj.php';

class FilmMufajController {

    private $db;
    private $model;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new FilmMufaj($db);
    }

    // -----------------------------------------------------------
    // GET /film-genres/film/{film_id}
    // Egy filmhez tartozó műfajok
    // -----------------------------------------------------------
    public function getGenresByFilm($filmId) {
        $this->model->film_id = $filmId;
        $result = $this->model->getGenresByFilm();

        if ($result->rowCount() > 0) {
            $genres = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $genres[] = $row;
            }

            http_response_code(200);
            echo json_encode($genres);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Ehhez a filmhez nincs műfaj társítva."]);
        }
    }

    // -----------------------------------------------------------
    // GET /film-genres/genre/{genre_id}
    // Egy műfajhoz tartozó filmek
    // -----------------------------------------------------------
    public function getFilmsByGenre($genreId) {
        $this->model->mufaj_id = $genreId;
        $result = $this->model->getFilmsByGenre();

        if ($result->rowCount() > 0) {
            $films = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $films[] = $row;
            }

            http_response_code(200);
            echo json_encode($films);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Ehhez a műfajhoz nincs film társítva."]);
        }
    }

    // -----------------------------------------------------------
    // POST /film-genres
    // Műfaj hozzáadása filmhez
    // -----------------------------------------------------------
    public function addGenreToFilm() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->film_id) || !isset($data->mufaj_id)) {
            http_response_code(400);
            echo json_encode(["message" => "A film_id és a mufaj_id mezők kötelezőek."]);
            return;
        }

        $this->model->film_id = $data->film_id;
        $this->model->mufaj_id = $data->mufaj_id;

        if ($this->model->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Műfaj sikeresen hozzáadva a filmhez."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt a hozzárendelés során."]);
        }
    }

    // -----------------------------------------------------------
    // DELETE /film-genres
    // Műfaj eltávolítása egy filmből
    // -----------------------------------------------------------
    public function removeGenreFromFilm() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->film_id) || !isset($data->mufaj_id)) {
            http_response_code(400);
            echo json_encode(["message" => "A film_id és a mufaj_id mezők kötelezőek a törléshez."]);
            return;
        }

        $this->model->film_id = $data->film_id;
        $this->model->mufaj_id = $data->mufaj_id;

        if ($this->model->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Műfaj eltávolítva a filmből."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt az eltávolítás során."]);
        }
    }
}
?>