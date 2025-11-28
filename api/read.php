<?php
    //headers

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    // előkészíteni az api-t
    include_once('../core/initialize.php');


    // a film előkészítése
    $film = new Film($db);

    // query használata
    $result = $film->read();

    //sorok számának lekérdezése
    $num = $result->rowCount();

    if($num > 0){
        $film_arr = array();
        $film_arr['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $film_item = array(
                'id' => $film_id,
                'cim' => $cim,
                'idotartam' => $idotartam,
                'poszter_url' => $poszter_url,
                'leiras' => $leiras,
                'kiadas_ev' => $kiadas_ev
            );
            array_push($film_arr['data'.$film_item]);
        }
        echo json_encode($film_item);
    }else{
        echo json_encode(array('message' => 'Nem található film.'));
    }

?>