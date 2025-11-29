<?php
    //headers

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: PUT');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization,X-Requested-With');

    // előkészíteni az api-t
    include_once('../models/initialize.php');


    // a film előkészítése
    $film = new Film($dbConn);


    $data = json_decode(file_get_contents("php://input"));

    $film->film_id = $data->film_id;
    $film->cim = $data->cim;
    $film->idotartam = $data->idotartam;
    $film->poszter_url = $data->poszter_url;
    $film->leiras = $data->leiras;
    $film->kiadasi_ev = $data->kiadasi_ev;


    if($film->update()){
        echo json_encode(
            array('message' => 'Film firssítve.')
        );
    }else{
        echo json_encode(
            array('message' => 'A film nem lett firssítve.')
        );
    }
?>