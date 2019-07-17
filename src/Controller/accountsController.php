<?php


class accountsController extends Controller
{

    public static $message='';
    public static $error='';
    function __construct()
    {
        Session::init();
    }

    function home(){
        $this->view('accounts/home','');
        $this->view->render();
    }
    function expense(){
        //Generate Expenses data
        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            if (isset($_POST['save-expense'])){
                $date = $_POST['date'];
                $amount  = $_POST['amount'];
                $desc  = $_POST['desc'];

                $Data = array();
                array_push($Data,$date,$amount,$desc);
                $this->requireModel()->expense($Data,1);
                header('location:/accounts/expense');
            }
        }
        $this->view('accounts/expenses','');
        $this->view->render();
    }
    function findExpense(){
        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            if (isset($_POST['index'])){
                $index = $_POST['index'];
                $Data = array();
                array_push($Data,$index);
                self::requireModel()->expense($Data,2);
            }
        }
    }
    function credit(){
        $this->view('accounts/credit','');
        $this->view->render();
    }

    function requireModel(){
        require_once MODEL.'accountsModel.php';
        $accModel = new accountsModel();
        return $accModel;
    }

}