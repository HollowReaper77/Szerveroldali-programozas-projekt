<?php
class VelemenyController {
    private $db;
    private $velemeny;

    public function __construct($db) {
        $this->db = $db;
        $this->velemeny = new Velemeny($db);
    }

    public function getReviewsByFilm($filmId) {
        $filmId = validateId($filmId, "Film ID");

        try {
            $stmt = $this->velemeny->getByFilm($filmId);
            $reviews = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $reviews[] = [
                    "velemeny_id" => (int)$row['velemeny_id'],
                    "letrehozas_ideje" => $row['letrehozas_ideje'],
                    "komment" => $row['komment'],
                    "ertekeles" => (float)$row['ertekeles'],
                    "film_id" => (int)$row['film_id'],
                    "felhasznalo_id" => (int)$row['felhasznalo_id'],
                    "felhasznalonev" => $row['felhasznalonev'] ?? 'Ismeretlen'
                ];
            }

            http_response_code(200);
            echo json_encode(["reviews" => $reviews]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    public function createReview() {
        requireAuth();
        $data = getJsonInput();

        if (empty($data['film_id']) || empty(trim($data['komment'])) || !isset($data['ertekeles'])) {
            http_response_code(400);
            echo json_encode(["message" => "Film ID, komment és értékelés kötelező."]);
            return;
        }

        $filmId = validateId($data['film_id'], "Film ID");
        $comment = trim($data['komment']);
        validateLength($comment, "Komment", 3, 1000);

        $rating = (float)$data['ertekeles'];
        if ($rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(["message" => "Az értékelésnek 1 és 5 között kell lennie."]);
            return;
        }

        $userId = getCurrentUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(["message" => "A vélemény írásához be kell jelentkezned."]);
            return;
        }

        $this->velemeny->film_id = $filmId;
        $this->velemeny->komment = $comment;
        $this->velemeny->ertekeles = number_format($rating, 1, '.', '');
        $this->velemeny->felhasznalo_id = $userId;
        $this->velemeny->letrehozas_ideje = date('Y-m-d');

        try {
            if ($this->velemeny->create()) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Vélemény sikeresen mentve.",
                    "review" => [
                        "velemeny_id" => $this->velemeny->velemeny_id,
                        "film_id" => $filmId,
                        "felhasznalo_id" => $this->velemeny->felhasznalo_id,
                        "felhasznalonev" => $_SESSION['username'] ?? 'Ismeretlen',
                        "komment" => $comment,
                        "ertekeles" => (float)$this->velemeny->ertekeles,
                        "letrehozas_ideje" => $this->velemeny->letrehozas_ideje
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "A vélemény mentése sikertelen."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }
}
?>
