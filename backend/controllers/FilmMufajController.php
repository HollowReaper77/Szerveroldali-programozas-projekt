
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
        $filmId = validateId($filmId, "Film ID");
        
        $this->model->film_id = $filmId;
        $result = $this->model->getGenresByFilm();

        if ($result->rowCount() > 0) {
            $genres = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $genres[] = $row;
            }

            http_response_code(200);
            echo json_encode(["mufajok" => $genres]);
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
        $genreId = validateId($genreId, "Műfaj ID");
        
        $this->model->mufaj_id = $genreId;
        $result = $this->model->getFilmsByGenre();

        if ($result->rowCount() > 0) {
            $films = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $films[] = $row;
            }

            http_response_code(200);
            echo json_encode(["filmek" => $films]);
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
        $data = getJsonInput();

        if (!isset($data['film_id']) || !isset($data['mufaj_id'])) {
            http_response_code(400);
            echo json_encode(["message" => "A film_id és a mufaj_id mezők kötelezőek."]);
            return;
        }

        validateNumber($data['film_id'], "Film ID", 1);
        validateNumber($data['mufaj_id'], "Műfaj ID", 1);

        // Ellenőrizd, hogy a film létezik-e
        $filmCheck = $this->db->prepare("SELECT film_id FROM film WHERE film_id = ?");
        $filmCheck->execute([$data['film_id']]);
        if ($filmCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A megadott film nem található."]);
            return;
        }

        // Ellenőrizd, hogy a műfaj létezik-e
        $genreCheck = $this->db->prepare("SELECT mufaj_id FROM mufajok WHERE mufaj_id = ?");
        $genreCheck->execute([$data['mufaj_id']]);
        if ($genreCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A megadott műfaj nem található."]);
            return;
        }

        $this->model->film_id = $data['film_id'];
        $this->model->mufaj_id = $data['mufaj_id'];

        try {
            if ($this->model->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Műfaj sikeresen hozzáadva a filmhez."]);
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
    // DELETE /film-genres vagy /film-genres/film/{film_id}/genre/{genre_id}
    // Műfaj eltávolítása egy filmből
    // -----------------------------------------------------------
    public function removeGenreFromFilm($filmId = null, $genreId = null) {
        // Ha URL paraméterek vannak, használd azokat
        if ($filmId !== null && $genreId !== null) {
            $filmId = validateId($filmId, "Film ID");
            $genreId = validateId($genreId, "Műfaj ID");
            
            $this->model->film_id = $filmId;
            $this->model->mufaj_id = $genreId;
        } else {
            // Különben JSON body-ból olvasd
            $data = getJsonInput();

            if (!isset($data['film_id']) || !isset($data['mufaj_id'])) {
                http_response_code(400);
                echo json_encode(["message" => "A film_id és a mufaj_id mezők kötelezőek a törléshez."]);
                return;
            }

            validateNumber($data['film_id'], "Film ID", 1);
            validateNumber($data['mufaj_id'], "Műfaj ID", 1);

            $this->model->film_id = $data['film_id'];
            $this->model->mufaj_id = $data['mufaj_id'];
        }

        try {
            if ($this->model->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Műfaj eltávolítva a filmből."]);
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