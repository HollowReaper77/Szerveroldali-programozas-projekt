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

    public function create(){
        $query = 'INSERT INTO ' .$this->table. 'SET film_id = : film_id, cim = : cim, idotartam = : idotartam, poszt_url = : poszt_url, leiras = : leiras, kiadas_ev = : kiadas_ev';
        $stmt = $this->conn->prepare($query);

        $this->film_id =        htmlspecialchars(strip_tags($this->film_id));
        $this->cim =            htmlspecialchars(strip_tags($this->cim));
        $this->idotartam =      htmlspecialchars(strip_tags($this->idotartam));
        $this->poszter_url =    htmlspecialchars(strip_tags($this->poszter_url));
        $this->leiras =         htmlspecialchars(strip_tags($this->leiras));
        $this->kiadas_ev =      htmlspecialchars(strip_tags($this->kiadas_ev));


        $stmt->bindParam(':film_id', $this->film_id);
        $stmt->bindParam(':cim', $this->cim);
        $stmt->bindParam(':idotartam', $this->idotartam);
        $stmt->bindParam(':poszter_url', $this->poszter_url);
        $stmt->bindParam(':leiras', $this->leiras);
        $stmt->bindParam(':kiadas_ev', $this->kiadas_ev);

        if($stmt->execute()){
            return true;
        }

        printf("Error %s \n", $stmt->error);
        return false;

    }



}


?>