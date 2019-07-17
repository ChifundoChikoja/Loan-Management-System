<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/12/19
 * Time: 7:57 PM
 */

class Controller
{
    protected $view;
    protected $model;

    public function view($viewName, $data = []){
        $this->view = new View($viewName,$data);
        return $this->view;
    }
}