<?php
    define("DBTYPE","mysql");
    define("DBHOST","localhost");
    define("DBNAME","filmdb_temp_name");
    define("DBCHARSET","utf8");
    define("DBUSER","root");
    define("DBPASSWORD","");
    
    define("APP_NAME", "CinemaTár");

    try {
        $dbConn = new PDO(DBTYPE.":host=".DBHOST.";dbname=".DBNAME.";charset=".DBCHARSET,DBUSER,DBPASSWORD);
        $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $dbConn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbConn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    } catch (PDOException $e){
        $error = "Adatbázis kapcsolódási hiba: {$e->getMessage()}";
    }

    //$dbConn->setAttribute(PDO::ATT);


    

?>