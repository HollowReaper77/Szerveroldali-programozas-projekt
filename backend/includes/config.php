<?php
    define("DBTYPE","mysql");
    define("DBHOST","localhost");
    define("DBNAME","film");
    define("DBCHARSET","utf8mb4");
    define("DBUSER","root");
    define("DBPASSWORD","");
    
    define("APP_NAME", "CinemaTár");

    try {
        $dbConn = new PDO(DBTYPE.":host=".DBHOST.";dbname=".DBNAME.";charset=".DBCHARSET,DBUSER,DBPASSWORD);
        $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $dbConn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbConn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        
        // Karakterkódolás explicit beállítása
        $dbConn->exec("SET NAMES utf8mb4");
    } catch (PDOException $e){
        http_response_code(500);
        echo json_encode(["message" => "Adatbázis kapcsolódási hiba: {$e->getMessage()}"]);
        exit;
    }
?>