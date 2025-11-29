<?php

class FilmMufaj {
    private $conn;
    private $table = "film_mufaj";

    public $film_id;
    public $mufaj_id;

    public function __construct($dbConn){
        $this->conn = $dbConn;
    }

    // GET all genres of a film
    public function getGenresByFilm(){
        $query = "SELECT m.mufaj_id, m.nev
                  FROM film_mufaj fm
                  JOIN mufajok m ON fm.mufaj_id = m.mufaj_id
                  WHERE fm.film_id = :film_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':film_id', $this->film_id);
        $stmt->execute();

        return $stmt;
    }

    // GET all films of a genre
    public function getFilmsByGenre(){
        $query = "SELECT f.film_id, f.cim, f.idotartam, f.poszter_url, f.leiras, f.kiadasi_ev
                  FROM film_mufaj fm
                  JOIN film f ON fm.film_id = f.film_id
                  WHERE fm.mufaj_id = :mufaj_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mufaj_id', $this->mufaj_id);
        $stmt->execute();

        return $stmt;
    }

    // ADD genre to film
    public function create(){
        $query = "INSERT INTO {$this->table}
                  SET film_id = :film_id,
                      mufaj_id = :mufaj_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':film_id', $this->film_id);
        $stmt->bindParam(':mufaj_id', $this->mufaj_id);

        return $stmt->execute();
    }

    // REMOVE genre from film
    public function delete(){
        $query = "DELETE FROM {$this->table}
                  WHERE film_id = :film_id
                    AND mufaj_id = :mufaj_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':film_id', $this->film_id);
        $stmt->bindParam(':mufaj_id', $this->mufaj_id);

        return $stmt->execute();
    }
}

?>
