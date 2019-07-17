<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/13/19
 * Time: 1:13 PM
 */

class View
{
    public $view_file = '';
    public  $view_data = '';

    public function __construct($view_file,$view_data)
    {
        $this->view_file = $view_file;
        $this->view_data = $view_data;
    }

    public function render(){
        if (file_exists(VIEW . $this->view_file . '.phtml')){
            $file = TEMP . 'header.phtml';
            require_once ($file);
            include_once  VIEW . $this->view_file . '.phtml';
            require_once TEMP.'footer.phtml';

        }
        else{
            echo "The file is not there";
        }
    }

    public function getAction(){
        return (explode('/',$this->view_file)[1]);
    }
}