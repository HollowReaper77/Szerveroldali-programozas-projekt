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

    // READ ALL
    public function read(){
        $query = "SELECT szinesz_id, nev, szuletesi_datum, bio
                  FROM {$this->table}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
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
            $this->szinesz_id      = $row['szinesz_id'];
            $this->nev             = $row['nev'];
            $this->szuletesi_datum = $row['szuletesi_datum'];
            $this->bio             = $row['bio'];
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

        $stmt->bindParam(':nev', $this->nev);
        $stmt->bindParam(':szuletesi_datum', $this->szuletesi_datum);
        $stmt->bindParam(':bio', $this->bio);

        return $stmt->execute();
    }

    // UPDATE
    public function update(){
        $query = "UPDATE {$this->table}
                  SET nev = :nev,
                      szuletesi_datum = :szuletesi_datum,
                      bio = :bio
                  WHERE szinesz_id = :szinesz_id";

        $stmt = $this->conn->prepare($query);

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
