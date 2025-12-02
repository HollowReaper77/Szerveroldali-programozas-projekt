<?php

require_once __DIR__ . '/../models/megnezett_film.php';

class MegnezettFilmController {
    private $db;
    private $model;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new MegnezettFilm($db);
    }

    public function getWatchedFilms() {
        requireAuth();

        $userId = getCurrentUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(["message" => "Be kell jelentkezned."]);
            return;
        }

        $includeInactive = isset($_GET['includeAll']) && $_GET['includeAll'] === '1';

        try {
            $stmt = $this->model->getByUser($userId, $includeInactive);
            $records = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $records[] = $this->formatRecord($row);
            }

            http_response_code(200);
            echo json_encode([
                "watched" => $records,
                "include_inactive" => $includeInactive
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    public function updateStatus($filmIdParam) {
        requireAuth();

        $filmId = validateId($filmIdParam, "Film ID");
        $data = getJsonInput();

        if (!array_key_exists('megnezve_e', $data)) {
            http_response_code(400);
            echo json_encode(["message" => "Hiányzik a 'megnezve_e' mező."]);
            return;
        }

        $isWatched = filter_var($data['megnezve_e'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isWatched === null) {
            http_response_code(400);
            echo json_encode(["message" => "A 'megnezve_e' mező értéke érvénytelen."]);
            return;
        }

        $note = isset($data['megjegyzes']) ? trim((string)$data['megjegyzes']) : null;
        if ($note !== null && $note !== '') {
            validateLength($note, "Megjegyzés", 0, 500);
        } else {
            $note = null;
        }

        if (!$this->filmExists($filmId)) {
            http_response_code(404);
            echo json_encode(["message" => "A film nem található."]);
            return;
        }

        try {
            $userId = getCurrentUserId();
            $this->model->upsertStatus($userId, $filmId, $isWatched, $note);
            $record = $this->model->getRecord($userId, $filmId);

            http_response_code(200);
            echo json_encode([
                "message" => $isWatched ? "Film megjelölve megnézettként." : "Megnézett jelölés visszavonva.",
                "record" => $record ? $this->formatRecord($record) : null
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    private function splitList(?string $value): array {
        if (empty($value)) {
            return [];
        }
        $parts = array_filter(array_map('trim', explode(',', $value)));
        return array_values($parts);
    }

    private function parseIdList(?string $value): array {
        if (empty($value)) {
            return [];
        }
        $parts = $this->splitList($value);
        return array_map('intval', $parts);
    }

    private function formatRecord(array $row): array {
        return [
            "film_id" => (int)$row['film_id'],
            "cim" => $row['cim'],
            "idotartam" => isset($row['idotartam']) ? (int)$row['idotartam'] : null,
            "poszter_url" => $row['poszter_url'],
            "leiras" => $row['leiras'],
            "kiadasi_ev" => isset($row['kiadasi_ev']) ? (int)$row['kiadasi_ev'] : null,
            "rendezok" => $this->splitList($row['rendezok'] ?? null),
            "szineszek" => $this->splitList($row['szineszek'] ?? null),
            "orszagok" => $this->splitList($row['orszagok'] ?? null),
            "orszag_idk" => $this->parseIdList($row['orszag_ids'] ?? null),
            "megnezve_e" => isset($row['megnezve_e']) ? (bool)$row['megnezve_e'] : false,
            "hozzaadas_datuma" => $row['hozzaadas_datuma'],
            "megjegyzes" => $row['megjegyzes']
        ];
    }

    private function filmExists(int $filmId): bool {
        $stmt = $this->db->prepare('SELECT 1 FROM film WHERE film_id = :film_id LIMIT 1');
        $stmt->bindValue(':film_id', $filmId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }
}

?>
