<?php
/* TODO:
    eldönteni, hogy megfelelnek-e teljesen az elnevezések,
    a szerepel-e benne helyet átírtam előre szereplo-re az osztály nevét
*/
class Szereplo {
    private $conn;
    private $table = "szerepel_benne";

    public $film_id;
    public $szinesz_id;

    public function __construct($dbConn){
        $this->conn = $dbConn;
    }

    // GET all actors of a film
    public function getActorsByFilm(){
        $query = "SELECT s.szinesz_id, s.nev, s.szuletesi_datum, s.bio
                  FROM szerepel_benne sb
                  JOIN szineszek s ON sb.szinesz_id = s.szinesz_id
                  WHERE sb.film_id = :film_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':film_id', $this->film_id);
        $stmt->execute();

        return $stmt;
    }

    // GET all films of an actor
    public function getFilmsByActor(){
        $query = "SELECT f.film_id, f.cim, f.idotartam, f.poszter_url, f.leiras, f.kiadasi_ev
                  FROM szerepel_benne sb
                  JOIN film f ON sb.film_id = f.film_id
                  WHERE sb.szinesz_id = :szinesz_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':szinesz_id', $this->szinesz_id);
        $stmt->execute();

        return $stmt;
    }

    // ADD actor to film
    public function create(){
        $query = "INSERT INTO {$this->table}
                  SET film_id = :film_id,
                      szinesz_id = :szinesz_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':film_id', $this->film_id);
        $stmt->bindParam(':szinesz_id', $this->szinesz_id);

        return $stmt->execute();
    }

    // REMOVE actor from film
    public function delete(){
        $query = "DELETE FROM {$this->table}
                  WHERE film_id = :film_id
                    AND szinesz_id = :szinesz_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':film_id', $this->film_id);
        $stmt->bindParam(':szinesz_id', $this->szinesz_id);

        return $stmt->execute();
    }
}

?>
