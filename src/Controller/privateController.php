<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/12/19
 * Time: 1:52 PM
 */

class privateController extends Controller
{
    public static $validCredentials='';
    public static $message;
    public static $error;
    public static $arrayContainer = array();
    public static $expectation=0;
    public static $paid=0;


    function __construct()
    {
        Session::init();
    }

    public function index($id ='',$name=''){
        //echo "Hello in private-Contr <br>";

        /* processing the model */
       // $this->model = new Model();

        /* processing the view */
        $this->view('private/index',[
            'name' => $name,
            'id' => $id
        ]);
        $this->view->render();

    }

    function login(){
        $username = $_POST['username'];
        $password = $_POST['password'];

        //processing the model;
        require_once MODEL.'privateModel.php';
        $this->model = new privateModel();
        $this->model->validate($username,$password);
    }

    function logout(){
        Session::set('loggedIn',"");
        Session::destroy();
        header('location:/private');
    }

    function home(){
        self::checkSession();
        /* processing the view */
        $this->view('private/home','');
        $this->view->render();
    }
    function collection(){

        //deactivating loans
        self::checkSession();
        $this->requireModel()->collection();
        $this->requireModel()->setupLoans();
        $this->requireModel()->manageDefaultArrears();

        self::requireModel()->payment();

        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (isset($_POST['pay_now'])){
                $loanID = $_POST['loanID'];
                $daily  = $_POST['exp'];
                $amount = $_POST['amount'];
                $installment = $_POST['installment'];

                if ($amount > $daily){
                    //recording advances
                    $advance = $amount - $daily;
                    self::requireModel()->advance($loanID,$advance,date('Y-m-d'),$installment);

                }elseif ($amount < $daily){
                    //recording the arrears
                    $arrears = $daily - $amount;
                    self::requireModel()->arrears($loanID,$arrears,date('Y-m-d'),$installment);

                }

                //processing the payment
                self::requireModel()->payLoan($loanID,$amount,date('Y-m-d'),$installment);
                header('location: /private/collection');
            }
        }


        $this->view('private/collection',  $this->requireModel()->collection());
        $this->view->render();
    }
    function registration(){
        self::checkSession();
        require_once MODEL.'privateModel.php';
        $privateModel = new privateModel();
        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            $Data = array();
            if (isset($_POST['add_client'])){

                //getting the form data
                $id         = $_POST['national_id'];
                $first_name = $_POST['first_name'];
                $surname    = $_POST['surname'];
                $maiden     = $_POST['maiden'];
                $group      = $_POST['group'];
                $gender     = $_POST['gender'];
                $dob        = $_POST['dob'];
                $marital    = $_POST['marital'];
                $children   = $_POST['children'];
                $home       = $_POST['home'];
                $cell       = $_POST['phone'];

                array_push($Data,$id,$group,$first_name,$maiden,$surname,$gender,$marital,$dob,$children,$home,$cell);
                $privateModel->client($Data,1);
            }
            if (isset($_POST['upload'])){

                //processing the cvs file
                $csv = array();

                // check there are no errors
                if($_FILES['clients']['error'] == 0){
                    $name = $_FILES['clients']['name'];
                    $ext = strtolower(end(explode('.', $_FILES['clients']['name'])));
                    $type = $_FILES['clients']['type'];
                    $tmpName = $_FILES['clients']['tmp_name'];

                    // check the file is a csv
                    if($ext === 'csv'){
                        if(($handle = fopen($tmpName, 'r')) !== FALSE) {
                            // necessary if a large csv file
                            set_time_limit(0);

                            $row = 0;

                            while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                                // number of fields in the csv
                                $col_count = count($data);

                                // get the values from the csv
                                $csv[$row]['natID'] = $data[0];
                                $csv[$row]['groupID'] = $data[1];
                                $csv[$row]['fname'] = $data[2];
                                $csv[$row]['maiden'] = $data[4];
                                $csv[$row]['sname'] = $data[3];
                                $csv[$row]['gender'] = $data[5];
                                $csv[$row]['marital'] = $data[6];
                                $csv[$row]['dob'] = $data[7];
                                $csv[$row]['sibling'] = $data[8];
                                $csv[$row]['home'] = $data[9];
                                $csv[$row]['Cell'] = $data[10];

                                // inc the row
                                $row++;
                            }
                            fclose($handle);
                        }
                    }
                }

               foreach ($csv as $array){
                   $Values_only =array_values($array);

                   //Registering
                   $privateModel->client($Values_only,1);
//                   print_r($Values_only);echo "<br><br>";
               }
            }
            if (isset($_POST['exp-client-temp'])) {

                header("Content-Disposition: attachment; filename='Clients_Template.csv");
                header("Content-Type: text/csv");

                $Details [] = array("natID" => "National ID*", "gID" => "Group ID*", "fname" => "First name*", "sname" => "Surname*", "maiden" => "Maiden", "gender" => "Gender(M,F)*", "marital" => "Marital(sin,mar,div)*", "dob" => "DOB(Y-M-D)*", "sib" => "Children(no)", "home" => "Home Add*", "cell" => "Cell");
                $df = fopen("php://output", 'w');
                foreach ($Details as $key => $value){
                    fputcsv($df,$value);
                }

                fclose($df);
                exit();
            }

        }

        $this->view('private/registration','');
        $this->view->render();
    }
    function group(){
        self::checkSession();
        require_once MODEL.'privateModel.php';
        $this->model = new privateModel();
        $groups = $this->model->group(2,'');
        if ($_SERVER['REQUEST_METHOD']=="POST"){
            $container = array();
            if (isset($_POST['add-group'])){
                $name   = $_POST['gname'];
                $chair  = $_POST['chair'];
                $cell   = $_POST['cell'];
                $vice   = $_POST['vice'];
                $zone   = $_POST['zone'];

                array_push($container,$name,$chair,$cell,$vice,$zone);
                $this->model->group(1,$container);
                $groups = $this->model->group(2,'');
            }
            if (isset($_POST['edit-group'])){
                $id = $_POST['groupID'];

                //getting the zones
                require_once MODEL.'adminModel.php';
                $adm =  new adminModel();
                $zones = $adm->zone('','',2);
                $Data = array();
                $Data['group']=self::requireModel()->editGroup($id,'',1);
                $Data['zone']=$zones;

                $this->view('private/editGroup',$Data);
                $this->view->render();
                return;
            }
            if (isset($_POST['gchange'])){

                $id = $_POST['groupID'];
                $name = $_POST['name'];
                $chair = $_POST['chair'];
                $chairNO = $_POST['chairNO'];
                $vice = $_POST['vice'];
                $viceNO = $_POST['viceNO'];
                $zone = $_POST['zone'];

                //removing the blackets
                $chair = self::standardize($chair);
                $chairNO = self::standardize($chairNO);
                $vice = self::standardize($vice);
                $viceNO = self::standardize($viceNO);

                $Data = array();
                array_push($Data,$name,$chair,$chairNO,$vice,$viceNO,$zone);
                self::requireModel()->editGroup($id,$Data,2);
            }
        }
        $this->view('private/group',$groups);
        $this->view->render();
    }
    function par(){
        self::checkSession();
        $this->view('private/par','');
        $this->view->render();
    }
    function pchange(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            # code...
            if (isset($_POST['pchange'])) {
                $password = $_POST['pass'];
                $id = $_POST['userID'];

                require_once MODEL.'adminModel.php';
                self::reLogin();
                $admModel = new adminModel();
                $admModel->passwordChange($password,$id);
                return;
            }
        }
        self::checkSession();
        $this->view('private/pchange','');
        $this->view->render();
    }
    function disbursement(){
        self::checkSession();
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (isset($_POST['disburse'])){
                $client = $_POST['client'];
                $officer = $_POST['officer'];
                $amount = $_POST['amounts'];
                $date = $_POST['date'];
                $onset = $_POST['rdate'];
                $transaction_time = $_POST['trans-date'];

                if (! self::requireModel() ->checkClient($client)){
                    self::$arrayContainer =self::requireModel()->createLoan($client,$amount,$date,$officer,$onset,$transaction_time);
                }else{
                   self::$error = "The Client has pending loans, please contact the admin ";
                }
            }
            if (isset($_POST['save-loan'])){
                $loan       = array();
                $client     = $_POST['ClientID'];
                $amount     = $_POST['amount'];
                $daily      = $_POST['daily'];
                $total      = $_POST['total'];
                $interest   = $_POST['total-interest'];
                $onset      =  $_POST['start-date'];
                $last       = $_POST['last'];
                $officer    = $_POST['officer'];
                $loanDate   = $_POST['loan-date'];
                $transactionDate  = $_POST['transaction-date'];

                array_push($loan,$client,$officer,$amount,$daily,$total,$interest,$loanDate,$transactionDate,$onset,$last);
                self::requireModel()->saveLoan($loan);

                $this->view('private/disbursement',self::requireModel()->client([],2));
                $this->view->render();
                return;
            }
        }
        $this->view('private/disbursement',self::requireModel()->client([],2));
        $this->view->render();
    }
    public static function charts(){
        //header('Content-Type: application/json');

        $paid = self::requireModel()->payment('',date('Y-m-d'),'',1);

        $expectation = 0;
        //dailyExpectation
        $loans = self::requireModel()->collection();
        foreach ($loans as $loan){
            $expectation += $loan['dailyPayment'];
        }

        $today = array();
        $today['expectation'] = round($expectation,2);
        $today['paid'] = $paid;
        $today['balance'] = round(($expectation - $paid),2);
        if ($today['balance'] < 0)
            $today['balance'] = 0;

        print json_encode($today);
    }

    function checkSession(){
        $active_session = Session::get('loggedIn');
        if (isset($active_session)){

        }
        else{
            $private = new privateController();
            $private->index('','');
            exit();
        }
    }
    function requireModel(){
        require_once MODEL.'privateModel.php';
        $privateModel = new privateModel();
        return $privateModel;
    }
    function reLogin(){
        require_once TEMP.'header.phtml';
        require_once TEMP.'top-bar.phtml';
        Session::destroy();
        ?>
            <div class="container" style="margin-top: 20px">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h5 class="panel-title" style="text-align: center">Please you need to login again</h5>
                    </div>
                    <div class="panel-body">
                        <p style="text-align: center"><a href="/private/index">Click here to login!!</a></p>
                    </div>
                </div>
            </div>
        <?php
    }
    function views(){
        self::checkSession();
        $this->view('private/view','');
        $this->view->render();

    }
    function client(){
        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            if (isset($_POST['view-client'])){
                $clientID = $_POST['clientID'];
                //getting the repayment History and the arrears history
                $this->view('private/viewClient',self::requireModel()->viewClient($clientID));
                $this->view->render();
                return;
            } if (isset($_POST['edit-client'])){
                //getting all the client groups
                $id = $_POST['clientID'];
                require_once MODEL.'privateModel.php';
                $privateModel = new privateModel();
                $groups = $privateModel->group(2,[]);
                array_push($groups,$id);
                $this->view('private/editClient',$groups);
                $this->view->render();
                return;
            }   if (isset($_POST['dchange'])){
                $name = $_POST['name'];
                $surname = $_POST['surname'];
                $maiden = $_POST['maiden'];
                $group = $_POST['group'];
                $client = $_POST['userID'];

                $Data = array();
                array_push($Data,$name,$maiden,$surname,$group,$client);
                require_once MODEL.'adminModel.php';
                $adm = new adminModel();
                $adm->client('',2,$Data);

                $this->view('private/view','');
                $this->view->render();
            }
        }
    }
    function findClient(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (isset($_POST['index'])){
                $id = $_POST['index'];

                self::requireModel()->findClient($id);
            }
        }

    }
    function standardize($string =""){
        $new = str_split($string);
        $checked="";
        foreach ($new as $char){
            if ($char != "("){
                $checked .= "$char";
            }else{
                return $checked;
                break;
            }
        }
    }

}