<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/9/19
 * Time: 8:35 PM
 */

defined("BASE")          ||       define("BASE", __DIR__);
defined("ROOT")          ||       define('ROOT' , dirname(__DIR__) . DIRECTORY_SEPARATOR);
defined("CONFIG")        ||       define("CONFIG" , ROOT . 'config'. DIRECTORY_SEPARATOR);
defined("WEBKIT")        ||       define("WEBKIT" , ROOT . "WebKit" . DIRECTORY_SEPARATOR);
defined("APP")           ||       define("APP" , ROOT. 'src' . DIRECTORY_SEPARATOR);
defined("CORE")          ||       define("CORE" , APP . 'Core' . DIRECTORY_SEPARATOR);
defined("DATA")          ||       define("DATA" , APP . 'Data' . DIRECTORY_SEPARATOR);
defined("MODEL")         ||       define("MODEL" , APP . 'Model' . DIRECTORY_SEPARATOR);
defined("VIEW")          ||       define("VIEW" , APP . 'View' . DIRECTORY_SEPARATOR);
defined("CONTROLLER")    ||       define("CONTROLLER" , APP . 'Controller' .  DIRECTORY_SEPARATOR);
defined("TEMP")          ||       define("TEMP" , APP . 'Template' .  DIRECTORY_SEPARATOR);
defined("DS")            ||       define("DS", DIRECTORY_SEPARATOR);
defined('DATETIME')      ||       define('DATETIME', date('Y-m-d H:i:s'));
/**
 * @ var array $modules Keeps address paths
 */

$modules = [ROOT , APP , WEBKIT , CONFIG , CORE , DATA , MODEL , VIEW , CONTROLLER];

set_include_path(get_include_path().PATH_SEPARATOR.implode(PATH_SEPARATOR , $modules));

 require_once(CORE.'Autoloader.php');
 spl_autoload_register('Autoloader::loader');
 //var_dump( spl_autoload_register('Autoloader::loader'));


//require_once(CORE.'Application.php');
//require_once (CORE.'Database.php');
//require_once (CORE.'Model.php');
//require_once (CORE.'View.php');
//require_once (CONTROLLER.'adminController.php');
//require_once (CONTROLLER.'privateController.php');

$app = new Application();
//$trial = new Database();