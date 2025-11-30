<?php
// Session indítása (felhasználókezeléshez)
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Konfiguráció és adatbázis kapcsolat
require_once __DIR__ . '/../backend/includes/config.php';

// Helper függvények betöltése
require_once __DIR__ . '/../backend/includes/helpers.php';

// Controllerek betöltése
require_once __DIR__ . '/../backend/controllers/FilmController.php';
require_once __DIR__ . '/../backend/controllers/SzineszController.php';
require_once __DIR__ . '/../backend/controllers/MufajController.php';
require_once __DIR__ . '/../backend/controllers/NemzetisegController.php';
require_once __DIR__ . '/../backend/controllers/SzereploController.php';
require_once __DIR__ . '/../backend/controllers/FilmMufajController.php';
require_once __DIR__ . '/../backend/controllers/RendezoController.php';
require_once __DIR__ . '/../backend/controllers/FelhasznaloController.php';

// Modellek betöltése
require_once __DIR__ . '/../backend/models/film.php';
require_once __DIR__ . '/../backend/models/szinesz.php';
require_once __DIR__ . '/../backend/models/mufaj.php';
require_once __DIR__ . '/../backend/models/orszag.php';
require_once __DIR__ . '/../backend/models/rendezo.php';
require_once __DIR__ . '/../backend/models/szereplo.php';
require_once __DIR__ . '/../backend/models/film_mufaj.php';
require_once __DIR__ . '/../backend/models/felhasznalo.php';

// Adatbázis kapcsolat változó
$db = $dbConn;

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
                $controller->getFilm($urlParts[1]);
            } else {
                $controller->getAllFilms();
            }
        }

        if ($method === 'POST') {
            $controller->createFilm();
        }

        if ($method === 'PUT') {
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Film ID hiányzik."]);
                break;
            }
            $controller->updateFilm($urlParts[1]);
        }

        if ($method === 'DELETE') {
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Film ID hiányzik."]);
                break;
            }
            $controller->deleteFilm($urlParts[1]);
        }

        break;

    // -----------------------------------------
    // SZÍNÉSZEK
    // -----------------------------------------
    case "actors":
        $controller = new SzineszController($db);

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
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Színész ID hiányzik."]);
                break;
            }
            $controller->updateActor($urlParts[1]);
        }

        if ($method === 'DELETE') {
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Színész ID hiányzik."]);
                break;
            }
            $controller->deleteActor($urlParts[1]);
        }

        break;

    // -----------------------------------------
    // MŰFAJOK
    // -----------------------------------------
    case "genres":
        $controller = new MufajController($db);

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
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Műfaj ID hiányzik."]);
                break;
            }
            $controller->updateGenre($urlParts[1]);
        }

        if ($method === 'DELETE') {
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Műfaj ID hiányzik."]);
                break;
            }
            $controller->deleteGenre($urlParts[1]);
        }

        break;

    // -----------------------------------------
    // ORSZÁGOK
    // -----------------------------------------
    case "countries":
        $controller = new NemzetisegController($db);

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
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Ország ID hiányzik."]);
                break;
            }
            $controller->updateCountry($urlParts[1]);
        }

        if ($method === 'DELETE') {
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Ország ID hiányzik."]);
                break;
            }
            $controller->deleteCountry($urlParts[1]);
        }

        break;

    // -----------------------------------------
    // RENDEZŐK
    // -----------------------------------------
    case "directors":
        $controller = new RendezoController($db);

        if ($method === 'GET') {
            if (isset($urlParts[1])) {
                $controller->getDirector($urlParts[1]);
            } else {
                $controller->getAllDirectors();
            }
        }

        if ($method === 'POST') {
            $controller->createDirector();
        }

        if ($method === 'PUT') {
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Rendező ID hiányzik."]);
                break;
            }
            $controller->updateDirector($urlParts[1]);
        }

        if ($method === 'DELETE') {
            if (!isset($urlParts[1])) {
                http_response_code(400);
                echo json_encode(["message" => "Rendező ID hiányzik."]);
                break;
            }
            $controller->deleteDirector($urlParts[1]);
        }

        break;

    // -----------------------------------------
    // FILM–SZÍNÉSZ kapcsolat
    // -----------------------------------------
    case "film-actors":
        $controller = new SzereploController($db);

        if ($method === 'GET') {
            if (isset($urlParts[1]) && $urlParts[1] === "film" && isset($urlParts[2])) {
                $controller->getActorsByFilm($urlParts[2]);
            } elseif (isset($urlParts[1]) && $urlParts[1] === "actor" && isset($urlParts[2])) {
                $controller->getFilmsByActor($urlParts[2]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Érvénytelen endpoint vagy hiányzó ID."]);
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
        $controller = new FilmMufajController($db);

        if ($method === 'GET') {
            if (isset($urlParts[1]) && $urlParts[1] === "film" && isset($urlParts[2])) {
                $controller->getGenresByFilm($urlParts[2]);
            } elseif (isset($urlParts[1]) && $urlParts[1] === "genre" && isset($urlParts[2])) {
                $controller->getFilmsByGenre($urlParts[2]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Érvénytelen endpoint vagy hiányzó ID."]);
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
    // FELHASZNÁLÓK (Users)
    // -----------------------------------------
    case "users":
        $controller = new FelhasznaloController($db);

        if ($method === 'POST') {
            if (isset($urlParts[1])) {
                switch ($urlParts[1]) {
                    case 'register':
                        $controller->register();
                        break;
                    case 'login':
                        $controller->login();
                        break;
                    case 'logout':
                        $controller->logout();
                        break;
                    default:
                        http_response_code(400);
                        echo json_encode(["message" => "Érvénytelen endpoint."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Hiányzó action."]);
            }
        }

        if ($method === 'GET') {
            if (isset($urlParts[1]) && $urlParts[1] === 'profile') {
                $controller->getProfile();
            } elseif (isset($urlParts[1]) && $urlParts[1] === 'all') {
                // Admin: összes felhasználó listázása
                $controller->getAllUsers();
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Érvénytelen endpoint."]);
            }
        }

        if ($method === 'PUT') {
            if (isset($urlParts[1]) && $urlParts[1] === 'profile') {
                $controller->updateProfile();
            } elseif (isset($urlParts[1]) && $urlParts[1] === 'change-password') {
                $controller->changePassword();
            } elseif (isset($urlParts[1]) && $urlParts[1] === 'role' && isset($urlParts[2])) {
                // Admin: felhasználó szerepkörének módosítása
                $controller->updateUserRole($urlParts[2]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Érvénytelen endpoint."]);
            }
        }

        if ($method === 'DELETE') {
            if (isset($urlParts[1])) {
                // Admin: felhasználó törlése
                $controller->deleteUser($urlParts[1]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Felhasználó ID hiányzik."]);
            }
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
?>