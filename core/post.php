<?php


class Post{
    //db tulajdonságok
    private $conn;
    private $table = "film";

    //post tulajdonságok
    // TODO: PONTOSÍTANI MINDEN OSZTÁLYBAN A LEKÉRDEZÉSEK MEZŐIT
    
    public $film_id;
    public $cim;
    public $idotartam;
    public $poszter_ul;
    public $leiras;
    public $kiadas_ev;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read(){

        $query = "SELECT `film_id`,`cim`,`idotartam`,`poszter_url`,`leiras`,`kiadasi_ev` FROM `film`";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }


}


?>