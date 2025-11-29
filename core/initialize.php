<?php
    defined('DS') ? null: define('DS', DIRECTORY_SEPARATOR);

    defined('SITE_ROOT') ? null: define('SITE_ROOT', DS. 'wamp64'.DS.'www'.DS.'film');
    //wamp64/www/phprest/includes
    defined('INC_PATH') ? null : define('INC_PATH', SITE_ROOT.DS.'includes');
    defined('CORE_PATH') ? null : define('CORE_PATH', SITE_ROOT.DS.'includes');

    // betölti a configurációs fájtl elsőnek
    require_once(INC_PATH.DS."config.php");

    //fő (core) osztályok
    require_once(CORE_PATH.DS."post.php");
    
?>