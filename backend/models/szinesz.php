<?php

class Szinesz {
    private $conn;
    private $table = "szineszek";

    public $szinesz_id;
    public $nev;
    public $szuletesi_datum;
    public $bio;

    public function __construct($dbConn){
        $this->conn = $dbConn;
    }

    // READ ALL - pagination támogatással
    public function read($limit = 50, $offset = 0){
        $query = "SELECT szinesz_id, nev, szuletesi_datum, bio
                  FROM {$this->table}
                  ORDER BY nev ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // COUNT - összes színész száma
    public function count() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // READ ONE
    public function read_single(){
        $query = "SELECT szinesz_id, nev, szuletesi_datum, bio
                  FROM {$this->table}
                  WHERE szinesz_id = ?
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->szinesz_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->szinesz_id = $row['szinesz_id'];
            $this->nev = $row['nev'];
            $this->szuletesi_datum = $row['szuletesi_datum'];
            $this->bio = $row['bio'];
        }

        return $stmt;
    }

    // CREATE
    public function create(){
        $query = "INSERT INTO {$this->table}
                  SET nev = :nev,
                      szuletesi_datum = :szuletesi_datum,
                      bio = :bio";

        $stmt = $this->conn->prepare($query);

        $this->nev = htmlspecialchars(strip_tags($this->nev));
        $this->szuletesi_datum = htmlspecialchars(strip_tags($this->szuletesi_datum));
        $this->bio = htmlspecialchars(strip_tags($this->bio));

        $stmt->bindParam(':nev', $this->nev);
        $stmt->bindParam(':szuletesi_datum', $this->szuletesi_datum);
        $stmt->bindParam(':bio', $this->bio);

        if($stmt->execute()) {
            $this->szinesz_id = (int)$this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // UPDATE
    public function update(){
        $query = "UPDATE {$this->table}
                  SET nev = :nev,
                      szuletesi_datum = :szuletesi_datum,
                      bio = :bio
                  WHERE szinesz_id = :szinesz_id";

        $stmt = $this->conn->prepare($query);

        $this->nev = htmlspecialchars(strip_tags($this->nev));
        $this->szuletesi_datum = htmlspecialchars(strip_tags($this->szuletesi_datum));
        $this->bio = htmlspecialchars(strip_tags($this->bio));

        $stmt->bindParam(':nev', $this->nev);
        $stmt->bindParam(':szuletesi_datum', $this->szuletesi_datum);
        $stmt->bindParam(':bio', $this->bio);
        $stmt->bindParam(':szinesz_id', $this->szinesz_id);

        return $stmt->execute();
    }

    // DELETE
    public function delete(){
        $query = "DELETE FROM {$this->table}
                  WHERE szinesz_id = :szinesz_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':szinesz_id', $this->szinesz_id);

        return $stmt->execute();
    }
}

?>