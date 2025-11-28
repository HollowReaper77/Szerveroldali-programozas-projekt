<?php
    //headers

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Acces-Control-Allow-Methods: DELETE');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization,X-Requested-With');

    // előkészíteni az api-t
    include_once('../core/initialize.php');


    // a film előkészítése
    $film = new Film($dbConn);


    $data = json_decode(file_get_contents("php://input"));

    $film->film_id = $data->film_id;


    if($film->delete()){
        echo json_encode(
            array('message' => 'Film törölve.')
        );
    }else{
        echo json_encode(
            array('message' => 'A film nem lett törölve.')
        );
    }
?>