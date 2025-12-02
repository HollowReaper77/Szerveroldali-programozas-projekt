<?php

class FilmRendezo {
    private $conn;
    private $table = "film_rendezok";

    public $film_id;
    public $rendezo_id;

    public function __construct($dbConn) {
        $this->conn = $dbConn;
    }

    public function getDirectorsByFilm() {
        $query = "SELECT r.rendezo_id, r.nev, r.szuletesi_datum, r.bio
                  FROM {$this->table} fr
                  JOIN rendezok r ON fr.rendezo_id = r.rendezo_id
                  WHERE fr.film_id = :film_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':film_id', $this->film_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getFilmsByDirector() {
        $query = "SELECT f.film_id, f.cim, f.idotartam, f.poszter_url, f.leiras, f.kiadasi_ev
                  FROM {$this->table} fr
                  JOIN film f ON fr.film_id = f.film_id
                  WHERE fr.rendezo_id = :rendezo_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rendezo_id', $this->rendezo_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO {$this->table}
                  SET film_id = :film_id,
                      rendezo_id = :rendezo_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':film_id', $this->film_id, PDO::PARAM_INT);
        $stmt->bindParam(':rendezo_id', $this->rendezo_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM {$this->table}
                  WHERE film_id = :film_id
                    AND rendezo_id = :rendezo_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':film_id', $this->film_id, PDO::PARAM_INT);
        $stmt->bindParam(':rendezo_id', $this->rendezo_id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

?>
