<?php

class Mufaj {
    private $conn;
    private $table = "mufajok";

    public $mufaj_id;
    public $nev;

    public function __construct($dbConn){
        $this->conn = $dbConn;
    }

    // READ ALL
    public function read(){
        $query = "SELECT mufaj_id, nev
                  FROM {$this->table}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // READ ONE
    public function read_single(){
        $query = "SELECT mufaj_id, nev
                  FROM {$this->table}
                  WHERE mufaj_id = ?
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->mufaj_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->mufaj_id = $row['mufaj_id'];
            $this->nev      = $row['nev'];
        }

        return $stmt;
    }

    // CREATE
    public function create(){
        $query = "INSERT INTO {$this->table}
                  SET nev = :nev";

        $stmt = $this->conn->prepare($query);

        $this->nev = htmlspecialchars(strip_tags($this->nev));

        $stmt->bindParam(':nev', $this->nev);

        return $stmt->execute();
    }

    // UPDATE
    public function update(){
        $query = "UPDATE {$this->table}
                  SET nev = :nev
                  WHERE mufaj_id = :mufaj_id";

        $stmt = $this->conn->prepare($query);

        $this->nev = htmlspecialchars(strip_tags($this->nev));

        $stmt->bindParam(':nev', $this->nev);
        $stmt->bindParam(':mufaj_id', $this->mufaj_id);

        return $stmt->execute();
    }

    // DELETE
    public function delete(){
        $query = "DELETE FROM {$this->table}
                  WHERE mufaj_id = :mufaj_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mufaj_id', $this->mufaj_id);

        return $stmt->execute();
    }
}

?>
