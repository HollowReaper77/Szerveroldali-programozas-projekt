<?php
    //headers

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    // előkészíteni az api-t
    include_once('../core/initialize.php');


    // a film előkészítése
    $film = new Film($dbConn);

    $film->film_id = isset($_GET['id']) ? $_GET['id'] : die();
    $film->read_single();

    $film_arr = array(
        'film_id' => $film->film_id,
        'cim' => $film->cim,
        'idotartam' => $film->idotartam,
        'poszter_url' => $film->poszter_url,
        'leiras' => $film->leiras,
        'kiadas_ev' => $film->kiadasi_ev,
    );

    print_r(json_encode($film_arr));

?>