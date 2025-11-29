<?php

class Rendezo {
    private $conn;
    private $table = "rendezok";

    public $rendezo_id;
    public $nev;
    public $szuletesi_datum;
    public $bio;

    public function __construct($dbConn){
        $this->conn = $dbConn;
    }

    // READ ALL
    public function read(){
        $query = "SELECT rendezo_id, nev, szuletesi_datum, bio
                  FROM {$this->table}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // READ ONE
    public function read_single(){
        $query = "SELECT rendezo_id, nev, szuletesi_datum, bio
                  FROM {$this->table}
                  WHERE rendezo_id = ?
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->rendezo_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->rendezo_id     = $row['rendezo_id'];
            $this->nev            = $row['nev'];
            $this->szuletesi_datum = $row['szuletesi_datum'];
            $this->bio            = $row['bio'];
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
                  WHERE rendezo_id = :rendezo_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nev', $this->nev);
        $stmt->bindParam(':szuletesi_datum', $this->szuletesi_datum);
        $stmt->bindParam(':bio', $this->bio);
        $stmt->bindParam(':rendezo_id', $this->rendezo_id);

        return $stmt->execute();
    }

    // DELETE
    public function delete(){
        $query = "DELETE FROM {$this->table}
                  WHERE rendezo_id = :rendezo_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rendezo_id', $this->rendezo_id);

        return $stmt->execute();
    }
}

?>
