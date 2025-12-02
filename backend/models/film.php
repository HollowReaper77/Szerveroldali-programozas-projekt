<?php

class Film{
    private $conn;
    private $table = "film";
    
    public $film_id;
    public $cim;
    public $idotartam;
    public $poszter_url;
    public $leiras;
    public $kiadasi_ev;
    public $rendezok_lista;
    public $szineszek_lista;

    public function __construct($dbConn) {
        $this->conn = $dbConn;
    }

    // READ ALL - pagination támogatással
    public function read($limit = 20, $offset = 0){
        $query = "SELECT f.film_id, f.cim, f.idotartam, f.poszter_url, f.leiras, f.kiadasi_ev,
                 (SELECT GROUP_CONCAT(DISTINCT r.nev ORDER BY r.nev SEPARATOR ', ')
                  FROM film_rendezok fr
                  JOIN rendezok r ON fr.rendezo_id = r.rendezo_id
                  WHERE fr.film_id = f.film_id) AS rendezok,
                 (SELECT GROUP_CONCAT(DISTINCT sz.nev ORDER BY sz.nev SEPARATOR ', ')
                  FROM film_szineszek fs
                  JOIN szineszek sz ON fs.szinesz_id = sz.szinesz_id
                  WHERE fs.film_id = f.film_id) AS szineszek
              FROM {$this->table} f
              ORDER BY f.kiadasi_ev DESC, f.cim ASC
              LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // COUNT - összes film száma
    public function count() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // READ SINGLE
    public function read_single(){
        $query = "SELECT f.film_id, f.cim, f.idotartam, f.poszter_url, f.leiras, f.kiadasi_ev,
                 (SELECT GROUP_CONCAT(DISTINCT r.nev ORDER BY r.nev SEPARATOR ', ')
                  FROM film_rendezok fr
                  JOIN rendezok r ON fr.rendezo_id = r.rendezo_id
                  WHERE fr.film_id = f.film_id) AS rendezok,
                 (SELECT GROUP_CONCAT(DISTINCT sz.nev ORDER BY sz.nev SEPARATOR ', ')
                  FROM film_szineszek fs
                  JOIN szineszek sz ON fs.szinesz_id = sz.szinesz_id
                  WHERE fs.film_id = f.film_id) AS szineszek
              FROM {$this->table} f
              WHERE f.film_id = ? 
              LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->film_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->film_id = $row['film_id'];
            $this->cim = $row['cim'];
            $this->idotartam = $row['idotartam'];
            $this->poszter_url = $row['poszter_url'];
            $this->leiras = $row['leiras'];
            $this->kiadasi_ev = $row['kiadasi_ev'];
            $this->rendezok_lista = $row['rendezok'] ?? null;
            $this->szineszek_lista = $row['szineszek'] ?? null;
        }

        return $stmt;
    }

    // CREATE
    public function create(){
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET cim = :cim, 
                      idotartam = :idotartam, 
                      poszter_url = :poszter_url, 
                      leiras = :leiras, 
                      kiadasi_ev = :kiadasi_ev';
        
        $stmt = $this->conn->prepare($query);

        $this->cim = htmlspecialchars(strip_tags($this->cim));
        $this->idotartam = htmlspecialchars(strip_tags($this->idotartam));
        $this->poszter_url = $this->sanitizeNullable($this->poszter_url);
        $this->leiras = htmlspecialchars(strip_tags($this->leiras));
        $this->kiadasi_ev = htmlspecialchars(strip_tags($this->kiadasi_ev));

        $stmt->bindParam(':cim', $this->cim);
        $stmt->bindParam(':idotartam', $this->idotartam);
        if ($this->poszter_url === null) {
            $stmt->bindValue(':poszter_url', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':poszter_url', $this->poszter_url);
        }
        $stmt->bindParam(':leiras', $this->leiras);
        $stmt->bindParam(':kiadasi_ev', $this->kiadasi_ev);

        if($stmt->execute()){
            $this->film_id = (int)$this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // UPDATE
    public function update(){
        $query = 'UPDATE ' . $this->table . ' 
                  SET cim = :cim, 
                      idotartam = :idotartam, 
                      poszter_url = :poszter_url, 
                      leiras = :leiras, 
                      kiadasi_ev = :kiadasi_ev
                  WHERE film_id = :film_id';
        
        $stmt = $this->conn->prepare($query);

        $this->cim = htmlspecialchars(strip_tags($this->cim));
        $this->idotartam = htmlspecialchars(strip_tags($this->idotartam));
        $this->poszter_url = $this->sanitizeNullable($this->poszter_url);
        $this->leiras = htmlspecialchars(strip_tags($this->leiras));
        $this->kiadasi_ev = htmlspecialchars(strip_tags($this->kiadasi_ev));
        $this->film_id = htmlspecialchars(strip_tags($this->film_id));

        $stmt->bindParam(':film_id', $this->film_id);
        $stmt->bindParam(':cim', $this->cim);
        $stmt->bindParam(':idotartam', $this->idotartam);
        if ($this->poszter_url === null) {
            $stmt->bindValue(':poszter_url', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':poszter_url', $this->poszter_url);
        }
        $stmt->bindParam(':leiras', $this->leiras);
        $stmt->bindParam(':kiadasi_ev', $this->kiadasi_ev);

        if($stmt->execute()){
            return true;
        }

        return false;
    }

    // DELETE
    public function delete(){
        $query = 'DELETE FROM ' . $this->table . ' WHERE film_id = :film_id';

        $stmt = $this->conn->prepare($query);

        $this->film_id = htmlspecialchars(strip_tags($this->film_id));
        $stmt->bindParam(':film_id', $this->film_id);

        if($stmt->execute()){
            return true;
        }

        return false;
    }

        private function sanitizeNullable($value) {
            if ($value === null || $value === '') {
                return null;
            }
            return htmlspecialchars(strip_tags($value));
        }
}

?>