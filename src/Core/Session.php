<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/14/19
 * Time: 12:42 PM
 */

class Session
{
    public static function init(){
        session_start();
    }

    public static function set($key,$value){
        $_SESSION[$key] = $value;
    }

    public static function get($key){
        return $_SESSION[$key];
    }

    public static function destroy(){
        session_destroy();
    }
}