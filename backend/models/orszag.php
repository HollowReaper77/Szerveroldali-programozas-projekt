<?php

class Orszag {
    private $conn;
    private $table = "orszagok";

    public $orszag_id;
    public $nev;

    public function __construct($dbConn){
        $this->conn = $dbConn;
    }

    // READ ALL
    public function read(){
        $query = "SELECT orszag_id, nev
                  FROM {$this->table}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // READ ONE
    public function read_single(){
        $query = "SELECT orszag_id, nev
                  FROM {$this->table}
                  WHERE orszag_id = ?
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->orszag_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->orszag_id = $row['orszag_id'];
            $this->nev       = $row['nev'];
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
                  WHERE orszag_id = :orszag_id";

        $stmt = $this->conn->prepare($query);

        $this->nev = htmlspecialchars(strip_tags($this->nev));

        $stmt->bindParam(':nev', $this->nev);
        $stmt->bindParam(':orszag_id', $this->orszag_id);

        return $stmt->execute();
    }

    // DELETE
    public function delete(){
        $query = "DELETE FROM {$this->table}
                  WHERE orszag_id = :orszag_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orszag_id', $this->orszag_id);

        return $stmt->execute();
    }
}

?>
