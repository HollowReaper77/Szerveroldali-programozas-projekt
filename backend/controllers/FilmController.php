<?php

require_once __DIR__ . '/../models/film.php';

class FilmController {

    private $db;
    private $filmModel;

    public function __construct($db) {
        $this->db = $db;
        $this->filmModel = new Film($db);
    }

    // -----------------------------------------------------------
    // GET /films      (összes film - pagination)
    // -----------------------------------------------------------
    public function getAllFilms() {
        // Pagination paraméterek
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        
        // Validálás
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 20;
        
        $offset = ($page - 1) * $limit;
        
        // Összes film száma
        $total = $this->filmModel->count();
        $totalPages = ceil($total / $limit);
        
        // Ellenőrizd, hogy a page ne legyen nagyobb mint a maximális oldalszám
        if ($totalPages > 0 && $page > $totalPages) {
            http_response_code(400);
            echo json_encode(["message" => "Az oldalszám túl nagy. Maximum {$totalPages} oldal létezik."]);
            return;
        }
        
        // Filmek lekérdezése
        $result = $this->filmModel->read($limit, $offset);
        $num = $result->rowCount();

        if ($num > 0) {
            $films = [];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $films[] = $this->formatFilmRow($row);
            }

            http_response_code(200);
            echo json_encode([
                "filmek" => $films,
                "count" => $total
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Nincs egyetlen film sem az adatbázisban."]);
        }
    }

    // -----------------------------------------------------------
    // GET /films/{id}    (egy film)
    // -----------------------------------------------------------
    public function getFilm($id) {
        $id = validateId($id, "Film ID");
        
        $this->filmModel->film_id = $id;
        $stmt = $this->filmModel->read_single();

        if ($stmt && $stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                "film" => $this->formatFilmFromModel()
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "A film nem található."]);
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

    private function formatFilmRow(array $row): array {
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
            "megnezve_db" => isset($row['megnezve_db']) ? (int)$row['megnezve_db'] : 0
        ];
    }

    private function formatFilmFromModel(): array {
        return [
            "film_id" => (int)$this->filmModel->film_id,
            "cim" => $this->filmModel->cim,
            "idotartam" => $this->filmModel->idotartam,
            "poszter_url" => $this->filmModel->poszter_url,
            "leiras" => $this->filmModel->leiras,
            "kiadasi_ev" => $this->filmModel->kiadasi_ev,
            "rendezok" => $this->splitList($this->filmModel->rendezok_lista ?? null),
            "szineszek" => $this->splitList($this->filmModel->szineszek_lista ?? null),
            "orszagok" => $this->splitList($this->filmModel->orszagok_lista ?? null),
            "orszag_idk" => $this->parseIdList($this->filmModel->orszag_idk_lista ?? null),
            "megnezve_db" => (int)($this->filmModel->megnezve_db ?? 0)
        ];
    }

    // -----------------------------------------------------------
    // POST /films      (új film)
    // -----------------------------------------------------------
    public function createFilm() {
        // Jogosultság ellenőrzés - csak admin és moderátor
        if (!requireRole('moderator')) {
            return;
        }

        $data = getJsonInput();

        // Kötelező mezők ellenőrzése
        if (
            !isset($data['cim']) ||
            !isset($data['idotartam']) ||
            !isset($data['leiras']) ||
            !isset($data['kiadasi_ev'])
        ) {
            http_response_code(400);
            echo json_encode(["message" => "Hiányzó mezők."]);
            return;
        }

        // Validálás helper függvényekkel
        validateLength($data['cim'], "Cím", 1, 255);
        validateNumber($data['idotartam'], "Időtartam", 1, 999);
        validateNumber($data['kiadasi_ev'], "Kiadási év", 1888, date('Y') + 5);
        validateLength($data['leiras'], "Leírás", 0, 2000);

        $this->filmModel->cim = $data['cim'];
        $this->filmModel->idotartam = $data['idotartam'];
        $this->filmModel->leiras = $data['leiras'];
        $this->filmModel->kiadasi_ev = $data['kiadasi_ev'];

        $posterUrl = isset($data['poszter_url']) ? trim((string)$data['poszter_url']) : '';
        if ($posterUrl === '') {
            $this->filmModel->poszter_url = null;
        } else {
            validateUrl($posterUrl, "Poszter URL");
            $this->filmModel->poszter_url = $posterUrl;
        }

        $countryIds = $this->extractCountryIds($data);

        try {
            if ($this->filmModel->create()) {
                if ($countryIds !== null) {
                    $this->syncFilmCountries($this->filmModel->film_id, $countryIds);
                }
                http_response_code(201);
                echo json_encode([
                    "message"     => "Film sikeresen létrehozva.",
                    "film_id"     => $this->filmModel->film_id,
                    "cim"         => $this->filmModel->cim,
                    "idotartam"   => $this->filmModel->idotartam,
                    "poszter_url" => $this->filmModel->poszter_url,
                    "leiras"      => $this->filmModel->leiras,
                    "kiadasi_ev"  => $this->filmModel->kiadasi_ev
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba a film létrehozása közben."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------
    // PUT /films/{id}    (film frissítése)
    // -----------------------------------------------------------
    public function updateFilm($id) {
        // Jogosultság ellenőrzés - csak admin és moderátor
        if (!requireRole('moderator')) {
            return;
        }

        $id = validateId($id, "Film ID");
        $data = getJsonInput();

        // Ellenőrizd, hogy létezik-e a film és töltsd be az adatokat
        $this->filmModel->film_id = $id;
        $stmt = $this->filmModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["message" => "A film nem található."]);
            return;
        }

        // Ha van cím, validáld és frissítsd
        if (isset($data['cim'])) {
            validateLength($data['cim'], "Cím", 1, 255);
            $this->filmModel->cim = $data['cim'];
        }
        // különben megtartjuk a jelenlegi értéket (már be van töltve read_single()-ből)

        // Ha van időtartam, validáld és frissítsd
        if (isset($data['idotartam'])) {
            validateNumber($data['idotartam'], "Időtartam", 1, 999);
            $this->filmModel->idotartam = $data['idotartam'];
        }

        // Ha van poszter URL, validáld és frissítsd (üres string esetén törölhető)
        if (array_key_exists('poszter_url', $data)) {
            $posterUrl = trim((string)$data['poszter_url']);
            if ($posterUrl === '') {
                $this->filmModel->poszter_url = null;
            } else {
                validateUrl($posterUrl, "Poszter URL");
                $this->filmModel->poszter_url = $posterUrl;
            }
        }

        // Ha van leírás, validáld és frissítsd
        if (isset($data['leiras'])) {
            validateLength($data['leiras'], "Leírás", 0, 2000);
            $this->filmModel->leiras = $data['leiras'];
        }

        // Ha van kiadási év, validáld és frissítsd
        if (isset($data['kiadasi_ev'])) {
            validateNumber($data['kiadasi_ev'], "Kiadási év", 1888, date('Y') + 5);
            $this->filmModel->kiadasi_ev = $data['kiadasi_ev'];
        }

        $countryIds = $this->extractCountryIds($data);

        try {
            if ($this->filmModel->update()) {
                if ($countryIds !== null) {
                    $this->syncFilmCountries($this->filmModel->film_id, $countryIds);
                }
                http_response_code(200);
                echo json_encode(["message" => "Film sikeresen frissítve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba a film frissítése közben."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }

    private function extractCountryIds(array $data): ?array {
        if (!array_key_exists('orszagok', $data)) {
            return null;
        }

        $value = $data['orszagok'];

        if ($value === null) {
            return [];
        }

        if (!is_array($value)) {
            http_response_code(400);
            echo json_encode(["message" => "Az 'orszagok' mező tömbként küldendő."]);
            exit;
        }

        $ids = [];
        foreach ($value as $countryId) {
            validateNumber($countryId, "Ország ID", 1);
            $ids[] = (int)$countryId;
        }

        return array_values(array_unique($ids));
    }

    private function syncFilmCountries(int $filmId, array $countryIds): void {
        try {
            $this->db->beginTransaction();

            $deleteStmt = $this->db->prepare("DELETE FROM film_orszagok WHERE film_id = :film_id");
            $deleteStmt->execute([':film_id' => $filmId]);

            if (!empty($countryIds)) {
                $insertStmt = $this->db->prepare("INSERT INTO film_orszagok (film_id, orszag_id) VALUES (:film_id, :orszag_id)");
                foreach ($countryIds as $countryId) {
                    $insertStmt->execute([
                        ':film_id' => $filmId,
                        ':orszag_id' => $countryId
                    ]);
                }
            }

            $this->db->commit();
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // -----------------------------------------------------------
    // DELETE /films/{id}
    // -----------------------------------------------------------
    public function deleteFilm($id) {
        // Jogosultság ellenőrzés - csak admin és moderátor
        if (!requireRole('moderator')) {
            return;
        }

        $id = validateId($id, "Film ID");
        
        // Ellenőrizd, hogy létezik-e a film
        $this->filmModel->film_id = $id;
        $stmt = $this->filmModel->read_single();
        
        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["error" => "A film nem található."]);
            return;
        }

        try {
            if ($this->filmModel->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Film törölve."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Hiba a film törlése közben."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Adatbázis hiba: " . $e->getMessage()]);
        }
    }
}
?>