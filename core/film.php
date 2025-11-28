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
    public $poszter_ul;
    public $leiras;
    public $kiadas_ev;

    public function __construct($dbConn) {
        $this->conn = $dbConn;
    }

    public function read(){

        $query = "SELECT `film_id`,`cim`,`idotartam`,`poszter_url`,`leiras`,`kiadasi_ev` FROM `film`";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

    public function read_single(){
        $query = "SELECT `film_id`,`cim`,`idotartam`,`poszter_url`,`leiras`,`kiadasi_ev` FROM `film` WHERE film_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }
}


?>