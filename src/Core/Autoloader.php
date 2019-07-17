<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/9/19
 * Time: 8:45 PM
 */

class Autoloader
{
    /**
     * loader
     * @param string accepts a class name
     */

    static function loader($class){
        //echo "am in loader <br>";
        /*name the file according to the file pattern */
        $filename = ucfirst($class) . '.php';

        //set the default file location to the current directory
        $file = __DIR__ . DIRECTORY_SEPARATOR . $filename;

        /*create an array of include paths*/
        $paths = explode(PATH_SEPARATOR,get_include_path());

        /*searching for the particular file in various directory */

        foreach ($paths as $path):
            $file = $path.$filename;
            if (file_exists($file))
                break;
            endforeach;

            if(include $file){

            }
    }
}
