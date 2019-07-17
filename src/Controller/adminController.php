<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/12/19
 * Time: 1:51 PM
 */

class adminController extends Controller
{
    public static $validCredentials='';
    public static $arrayContainer = array();
    public static $error;
    public static $message;



    function __construct()
    {
       Session::init();
        // var_dump(adminController::$validCredentials);
    }

    public function index($id ='',$name=''){
       // echo "Hello in Admin-Contr <br>";

        /* processing the model */
        $this->model = new Model();

        /* processing the view */
        $this->view('admin/index',[
            'name' => $name,
            'id' => $id
        ]);
        $this->view->render();

    }

    function login(){
        //echo "am logging in as admin <br>";
        $username = $_POST['username'];
        $password = $_POST['password'];

        //processing the model;
        require_once MODEL.'adminModel.php';
        $this->model = new adminModel();
        $this->model->validate($username,$password);
    }

    function home(){

        self::checkSession();
        /* processing the view */
        //var_dump($this);
        //generating the summary

        self::requireModel()->quickSummary();
        $this->view('admin/dashboard',self::requireModel()->quickSummary());
        $this->view->render();

    }
    function preferences(){
        self::checkSession();
        $this->view('admin/preferences','');
        $this->view->render();

    }
    function administration(){
        self::checkSession();
        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            $id = $_POST['id'];
            $this->requireModel()->deleteUser($id);
            if (isset($_POST['add-user'])){
                $name = $_POST['name'];
                $surname = $_POST['surname'];
                $username = $_POST['uname'];
                $password = $_POST['password'];
                $role = $_POST['role'];

                array_push(adminController::$arrayContainer,$name,$surname,$username,$password,$role);
               $this->requireModel()->user('',self::$arrayContainer,1);
               self::$arrayContainer = '';
            }
            if (isset($_POST['search'])){
                $key = $_POST['key'];
                self::$arrayContainer = $this->requireModel()->user($key,'',2);
                $this->view('admin/administration','');
                $this->view->render();
                return;
            }if (isset($_POST['edit-user'])){
                $id = $_POST['userID'];
                $this->view('admin/pchange',$this->requireModel()->user($id,'',3));
                $this->view->render();
                return;
            }
            if (isset($_POST['pchange'])){
                $id = $_POST['userID'];
                $password = $_POST['pass'];
                var_dump($password);
                $this->requireModel()->passwordChange($password,$id);
            }
        }
        $this->view('admin/administration','');
        $this->view->render();
    }
    function setup()
    {
        self::checkSession();
        self::$arrayContainer = self::requireModel()->collectionDay('','',3);
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            //Deleting holiday via ajax
            $id = $_POST['id'];
            $this->requireModel()->holiday('','',$id,3);

            if (isset($_POST['add-holiday'])){
                $date = $_POST['date'];
                $desc = $_POST['desc'];
                $this->requireModel()->holiday($date,$desc,'',1);
            }
            if (isset($_POST['add-weekday'])){
                $day = $_POST['day'];
                $this->requireModel()->collectionDay($day,'',1);
                self::$arrayContainer = self::requireModel()->collectionDay('','',3);
            }if (isset($_POST['delete-day'])){
                $day = $_POST['day'];
                self::requireModel()->collectionDay('',$day,2);
                self::$arrayContainer = self::requireModel()->collectionDay('','',3);
            }
        }
        $this->view('admin/setup',$this->requireModel()->holiday('','','',2));
        $this->view->render();
    }
    function disbursement(){
        self::checkSession();
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (isset($_POST['approve'])){
                $id = $_POST['loanID'];
                $client = $_POST['client'];

                //checking if the client is cleared
                if (!self::requireModel()->checkClient($client)){
                    $this->view('admin/disbursement',self::requireModel()->getPendingLoans());
                    $this->view->render();
                    return;
                }else{
                    self::requireModel()->approveLoan($id);
                }

            }
            if (isset($_POST['decline'])){
                $id = $_POST['loanID'];
                self::requireModel()->removeTempLoan($id);
                self::$error = "Loan Declined";
            }
        }
        $this->view('admin/disbursement',self::requireModel()->getPendingLoans());
        $this->view->render();
    }
    function processing(){
        self::checkSession();
        if ($_SERVER['REQUEST_METHOD']=="POST"){
            if (isset($_POST['add-list'])){
                echo "am registering";
                $file = $_POST['sel-list'];

                $fileName = explode('.',$file);
                var_dump($fileName);

                if (empty($file)){
                    echo "please choose a file";
                }

                if (fopen($file,'r')){
                    echo "file opened";
                }else{
                    echo "cannot open file";
                }
            }
        }

    }
    function zone(){
        self::checkSession();
        $zoneData = array();
        $zones = self::requireModel()->zone('','',2);
        //trying ajax

        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (isset($_POST['addZone'])){
                $zone       = $_POST['zone'];
                $location   = $_POST['location'];
                $chair      = $_POST['zone-chair'];
                $contact    = $_POST['zone-chair'];

                array_push($zoneData,$zone,$location,$chair,$contact);
                self::requireModel()->zone($zoneData,'',1);
                $zones = self::requireModel()->zone('','',2);

            }
            if (isset($_POST['delete-zone'])){
                $id = $_POST['index'];
                self::requireModel()->zone('',$id,3);
                $zones = self::requireModel()->zone('','',2);
            }
            if (isset($_POST['user_id'])){
//          retrieve zone based on id
                $id = $_POST['user_id'];
                $data = $this->requireModel()->zone('',$id,4);
                echo json_encode($data);
                return;
            }
            if (isset($_POST['update-zone'])){
                    $details = array();
                    $id = $_POST['id'];
                    $name = $_POST['f1'];
                    $location = $_POST['f2'];
                    $chair = $_POST['f3'];
                    $cell = $_POST['f4'];
                    array_push($details,$id,$name,$location,$chair,$cell);
                    $this->requireModel()->update($details,1);
                    return;
                    //$this->requireModel()->zone('',26,4);
                    //$this->view('admin/zone',$zones);
                    //$this->view->render();
            }

        }
        $this->requireModel()->zone('',26,4);
        $this->view('admin/zone',$zones);
        $this->view->render();
    }
    function logout(){
        Session::set('loggedIn',"");
        Session::destroy();
        header('location:/admin');
    }
    function requireModel(){
        require_once MODEL.'adminModel.php';
        $adminModel = new adminModel();
        return $adminModel;
    }
    function checkSession(){
        $active_session = Session::get('loggedIn');
        if (isset($active_session)){

        }
        else{
            $admin = new adminController();
            $admin->index('','');
            exit();
        }
    }
    function client(){

        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            if (isset($_POST['edit-client'])){
                //getting all the client groups
                $id = $_POST['clientID'];
                require_once MODEL.'privateModel.php';
                $privateModel = new privateModel();
                $groups = $privateModel->group(2,[]);
                array_push($groups,$id);
                $this->view('admin/editClient',$groups);
                $this->view->render();
                return;
            }
            if (isset($_POST['dchange'])){
                    $name = $_POST['name'];
                    $surname = $_POST['surname'];
                    $maiden = $_POST['maiden'];
                    $group = $_POST['group'];
                    $client = $_POST['userID'];

                    $Data = array();
                    array_push($Data,$name,$maiden,$surname,$group,$client);
                    self::requireModel()->client('',2,$Data);

            }   if (isset($_POST['view-client'])){
                $clientID = $_POST['clientID'];
                //getting the repayment History and the arrears history
                $this->view('admin/viewClient',self::requireModel()->viewClient($clientID));
                $this->view->render();
                return;
            }
        }
        $this->view('admin/clients','');
        $this->view->render();
    }
    function messages(){
        self::checkSession();
        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            if (isset($_POST['publish'])){
                //getting the values

                $heading = $_POST['title'];
                $body =     $_POST['message'];
                $date = $_POST['date'];
                $publisher = $_POST['publisher'];
                $type = $_POST['type'];

                $data = array();
                array_push($data,$date,$publisher,$type,$heading,$body);
                self::requireModel()->message($data,1);
            }if (isset($_POST['delete-btn'])){
                $id = $_POST['messageID'];
                $data = array();
                array_push($data,$id);
                self::requireModel()->message($data,3);
            }if (isset($_POST['editMessage'])){
                $id = $_POST['messageID'];

                // getting the details
                $Data = array();
                array_push($Data,$id);
                $this->view('admin/editMessage',self::requireModel()->message($Data,4));
                $this->view->render();
                return;
            }
            if (isset($_POST['edit-message-btn'])){
                $title = $_POST['title'];
                $body  = $_POST['message'];

                $data = array();
                array_push($data, $title,$body);
                self::requireModel()->message($data,5);

            }
        }


        $this->view('admin/messages',self::requireModel()->message([],2));
        $this->view->render();
    }
    function findClient(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (isset($_POST['index'])){
                $id = $_POST['index'];

                self::requireModel()->findClient($id);
            }
        }

    }

}