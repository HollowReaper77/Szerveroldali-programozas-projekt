<?php

class MegnezettFilm {
    private $conn;
    private $table = "megnezett_filmek";

    public function __construct($dbConn) {
        $this->conn = $dbConn;
    }

    private function baseSelectColumns(): string {
        return "f.film_id, f.cim, f.idotartam, f.poszter_url, f.leiras, f.kiadasi_ev,
                (SELECT GROUP_CONCAT(DISTINCT r.nev ORDER BY r.nev SEPARATOR ', ')
                 FROM film_rendezok fr
                 JOIN rendezok r ON fr.rendezo_id = r.rendezo_id
                 WHERE fr.film_id = f.film_id) AS rendezok,
                (SELECT GROUP_CONCAT(DISTINCT sz.nev ORDER BY sz.nev SEPARATOR ', ')
                 FROM film_szineszek fs
                 JOIN szineszek sz ON fs.szinesz_id = sz.szinesz_id
                 WHERE fs.film_id = f.film_id) AS szineszek,
                (SELECT GROUP_CONCAT(DISTINCT o.nev ORDER BY o.nev SEPARATOR ', ')
                 FROM film_orszagok fo
                 JOIN orszagok o ON fo.orszag_id = o.orszag_id
                 WHERE fo.film_id = f.film_id) AS orszagok,
                (SELECT GROUP_CONCAT(DISTINCT o.orszag_id ORDER BY o.nev SEPARATOR ',')
                 FROM film_orszagok fo
                 JOIN orszagok o ON fo.orszag_id = o.orszag_id
                 WHERE fo.film_id = f.film_id) AS orszag_ids";
    }

    public function getByUser(int $userId, bool $includeInactive = false) {
        $statusCondition = $includeInactive ? '' : 'AND mf.megnezve_e = 1';

        $query = "SELECT " . $this->baseSelectColumns() . ",
                         mf.megnezve_e, mf.hozzaadas_datuma, mf.megjegyzes
                  FROM {$this->table} mf
                  JOIN film f ON f.film_id = mf.film_id
                  WHERE mf.felhasznalo_id = :felhasznalo_id {$statusCondition}
                  ORDER BY
                      CASE WHEN mf.hozzaadas_datuma IS NULL THEN 1 ELSE 0 END,
                      mf.hozzaadas_datuma DESC,
                      f.cim ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':felhasznalo_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getRecord(int $userId, int $filmId) {
        $query = "SELECT " . $this->baseSelectColumns() . ",
                         mf.megnezve_e, mf.hozzaadas_datuma, mf.megjegyzes
                  FROM {$this->table} mf
                  JOIN film f ON f.film_id = mf.film_id
                  WHERE mf.felhasznalo_id = :felhasznalo_id AND mf.film_id = :film_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':felhasznalo_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':film_id', $filmId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function upsertStatus(int $userId, int $filmId, bool $isWatched, ?string $note = null): bool {
        $query = "INSERT INTO {$this->table} (felhasznalo_id, film_id, hozzaadas_datuma, megnezve_e, megjegyzes)
                  VALUES (:felhasznalo_id, :film_id, :hozzaadas_datuma, :megnezve_e, :megjegyzes)
                  ON DUPLICATE KEY UPDATE
                      megnezve_e = VALUES(megnezve_e),
                      megjegyzes = VALUES(megjegyzes),
                      hozzaadas_datuma = CASE
                          WHEN VALUES(megnezve_e) = 1 THEN VALUES(hozzaadas_datuma)
                          ELSE hozzaadas_datuma
                      END";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':felhasznalo_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':film_id', $filmId, PDO::PARAM_INT);

        if ($isWatched) {
            $stmt->bindValue(':hozzaadas_datuma', date('Y-m-d'));
        } else {
            $stmt->bindValue(':hozzaadas_datuma', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':megnezve_e', $isWatched ? 1 : 0, PDO::PARAM_INT);

        if ($note === null || $note === '') {
            $stmt->bindValue(':megjegyzes', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':megjegyzes', $note, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }
}

?>
