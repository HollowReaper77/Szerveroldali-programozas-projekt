<?php
    //headers

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Acces-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization,X-Requested-With');

    // előkészíteni az api-t
    include_once('../core/initialize.php');


    // a film előkészítése
    $film = new Film($dbConn);


    $data = json_decode(file_get_contents("php://input"));

    $film->cim = $data->cim;
    $film->idotartam = $data->idotartam;
    $film->poszter_url = $data->poszter_url;
    $film->leiras = $data->leiras;
    $film->kiadasi_ev = $data->kiadasi_ev;


    if($film->create()){
        echo json_encode(
            array('message' => 'Film létrehozva.')
        );
    }else{
        echo json_encode(
            array('message' => 'A film nem lett létrehozva.')
        );
    }
?>