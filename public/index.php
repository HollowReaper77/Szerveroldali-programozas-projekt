<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// autoload
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/FilmController.php';
require_once __DIR__ . '/../controllers/SzineszController.php';
require_once __DIR__ . '/../controllers/MufajController.php';
require_once __DIR__ . '/../controllers/NemzetisegController.php';
require_once __DIR__ . '/../controllers/SzereploController.php';
require_once __DIR__ . '/../controllers/FilmMufajController.php';

$database = new Database();
$db = $database->connect();

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$urlParts = explode('/', $url);

$method = $_SERVER['REQUEST_METHOD'];

// ---- ROUTER ---- //

switch ($urlParts[0]) {

    // -----------------------------------------
    // FILMEK
    // -----------------------------------------
    case "films":
        $controller = new FilmController($db);
        
        if ($method === 'GET') {
            if (isset($urlParts[1])) {
                // /films/{id}
                $controller->getFilm($urlParts[1]);
            } else {
                // /films
                $controller->getAllFilms();
            }
        }

        if ($method === 'POST') {
            $controller->createFilm();
        }

        if ($method === 'PUT') {
            $controller->updateFilm($urlParts[1]);
        }

        if ($method === 'DELETE') {
            $controller->deleteFilm($urlParts[1]);
        }

        break;

    // -----------------------------------------
    // SZÍNÉSZEK
    // -----------------------------------------
    case "actors":
        $controller = new ActorController($db);

        if ($method === 'GET') {
            if (isset($urlParts[1])) {
                $controller->getActor($urlParts[1]);
            } else {
                $controller->getAllActors();
            }
        }

        if ($method === 'POST') {
            $controller->createActor();
        }

        if ($method === 'PUT') {
            $controller->updateActor($urlParts[1]);
        }

        if ($method === 'DELETE') {
            $controller->deleteActor($urlParts[1]);
        }

        break;

    // -----------------------------------------
    // MŰFAJOK
    // -----------------------------------------
    case "genres":
        $controller = new GenreController($db);

        if ($method === 'GET') {
            if (isset($urlParts[1])) {
                $controller->getGenre($urlParts[1]);
            } else {
                $controller->getAllGenres();
            }
        }

        if ($method === 'POST') {
            $controller->createGenre();
        }

        if ($method === 'PUT') {
            $controller->updateGenre($urlParts[1]);
        }

        if ($method === 'DELETE') {
            $controller->deleteGenre($urlParts[1]);
        }

        break;

    // -----------------------------------------
    // ORSZÁGOK
    // -----------------------------------------
    case "countries":
        $controller = new CountryController($db);

        if ($method === 'GET') {
            if (isset($urlParts[1])) {
                $controller->getCountry($urlParts[1]);
            } else {
                $controller->getAllCountries();
            }
        }

        if ($method === 'POST') {
            $controller->createCountry();
        }

        if ($method === 'PUT') {
            $controller->updateCountry($urlParts[1]);
        }

        if ($method === 'DELETE') {
            $controller->deleteCountry($urlParts[1]);
        }

        break;

    // -----------------------------------------
    // FILM–SZÍNÉSZ kapcsolat
    // -----------------------------------------
    case "film-actors":
        $controller = new CastController($db);

        if ($method === 'GET') {
            // /film-actors/film/{id}
            if ($urlParts[1] === "film") {
                $controller->getActorsByFilm($urlParts[2]);
            }
            // /film-actors/actor/{id}
            if ($urlParts[1] === "actor") {
                $controller->getFilmsByActor($urlParts[2]);
            }
        }

        if ($method === 'POST') {
            $controller->addActorToFilm();
        }

        if ($method === 'DELETE') {
            $controller->removeActorFromFilm();
        }

        break;

    // -----------------------------------------
    // FILM–MŰFAJ kapcsolat
    // -----------------------------------------
    case "film-genres":
        $controller = new FilmGenreController($db);

        if ($method === 'GET') {
            if ($urlParts[1] === "film") {
                $controller->getGenresByFilm($urlParts[2]);
            }
            if ($urlParts[1] === "genre") {
                $controller->getFilmsByGenre($urlParts[2]);
            }
        }

        if ($method === 'POST') {
            $controller->addGenreToFilm();
        }

        if ($method === 'DELETE') {
            $controller->removeGenreFromFilm();
        }

        break;

    // -----------------------------------------
    // DEFAULT: 404
    // -----------------------------------------
    default:
        http_response_code(404);
        echo json_encode(["message" => "Endpoint not found"]);
        break;
}
