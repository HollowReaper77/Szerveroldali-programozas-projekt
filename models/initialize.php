<?php
    defined('DS') ? null: define('DS', DIRECTORY_SEPARATOR);

    defined('SITE_ROOT') ? null: define('SITE_ROOT', DS.'wamp64'.DS.'www'.DS.'film');
    //wamp64/www/phprest/includes
    defined('INC_PATH') ? null : define('INC_PATH', SITE_ROOT.DS.'includes');
    defined('models_PATH') ? null : define('models_PATH', SITE_ROOT.DS.'models');
    //defined('MODELS_PATH') ? null : define('MODELS_PATH', SITE_ROOT.DS.'models');
    // betölti a configurációs fájtl elsőnek
    require_once(INC_PATH.DS."config.php");

    //fő (models) osztályok
    require_once(models_PATH.DS."film.php");
    require_once(models_PATH.DS."film_mufaj.php");
    require_once(models_PATH.DS."mufaj.php");
    require_once(models_PATH.DS."orszag.php");
    require_once(models_PATH.DS."rendezo.php");
    require_once(models_PATH.DS."szereplo.php");
    require_once(models_PATH.DS."szinesz.php");
?>