<?php

class Velemeny {
    private $conn;
    private $table = "velemenyek";

    public $velemeny_id;
    public $letrehozas_ideje;
    public $komment;
    public $ertekeles;
    public $film_id;
    public $felhasznalo_id;

    public function __construct($dbConn) {
        $this->conn = $dbConn;
    }

    public function getByFilm($filmId) {
        $query = "SELECT v.velemeny_id, v.letrehozas_ideje, v.komment, v.ertekeles, v.film_id, v.felhasznalo_id, f.felhasznalonev
                  FROM {$this->table} v
                  LEFT JOIN felhasznalo f ON v.felhasznalo_id = f.felhasznalo_id
                  WHERE v.film_id = :film_id
                  ORDER BY v.letrehozas_ideje DESC, v.velemeny_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':film_id', $filmId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO {$this->table} (letrehozas_ideje, komment, ertekeles, film_id, felhasznalo_id)
                  VALUES (:letrehozas_ideje, :komment, :ertekeles, :film_id, :felhasznalo_id)";

        $stmt = $this->conn->prepare($query);

        $this->komment = htmlspecialchars(strip_tags($this->komment));
        $this->ertekeles = htmlspecialchars(strip_tags($this->ertekeles));
        $this->film_id = htmlspecialchars(strip_tags($this->film_id));
        $this->felhasznalo_id = htmlspecialchars(strip_tags($this->felhasznalo_id));

        $stmt->bindParam(':letrehozas_ideje', $this->letrehozas_ideje);
        $stmt->bindParam(':komment', $this->komment);
        $stmt->bindParam(':ertekeles', $this->ertekeles);
        $stmt->bindParam(':film_id', $this->film_id);
        $stmt->bindParam(':felhasznalo_id', $this->felhasznalo_id);

        if ($stmt->execute()) {
            $this->velemeny_id = (int)$this->conn->lastInsertId();
            return true;
        }

        return false;
    }
}

?>
