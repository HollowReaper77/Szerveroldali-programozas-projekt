<?php


class Film{
    //db tulajdonságok
    private $conn;
    private $table = "film";

    //film tulajdonságok
    // TODO: PONTOSÍTANI MINDEN OSZTÁLYBAN A LEKÉRDEZÉSEK MEZŐIT
    
    public $film_id;
    public $cim;
    public $idotartam;
    public $poszter_url;
    public $leiras;
    public $kiadas_ev;

    public function __construct($dbConn) {
        $this->conn = $dbConn;
    }

    public function read(){

        $query = "SELECT `film_id`,
        `cim`,
        `idotartam`,
        `poszter_url`,
        `leiras`,
        `kiadasi_ev`
         FROM `film`";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

    public function read_single(){
        $query = "SELECT `film_id`,
        `cim`,
        `idotartam`,
        `poszter_url`,
        `leiras`,
        `kiadasi_ev` 
        FROM ". $this->table.
        " WHERE film_id = ? LIMIT 1";

        
        
        $stmt = $this->conn->prepare($query);
        $stmt-> bindParam(1, $this->film_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->film_id =$row['film_id'];
        $this->cim =$row['cím'];
        $this->idotartam =$row['idotartam'];
        $this->poszter_url =$row['poszter_url'];
        $this->leiras =$row['leiras'];
        $this->kiadas_ev =$row['kiadasev'];

        return $stmt;






    }
}


?>