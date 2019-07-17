<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/13/19
 * Time: 1:52 PM
 */

class privateModel extends Model
{
    function __construct()
    {
        parent::__construct();
        //echo "am in private model";
    }

    function validate($username = '', $password = '')
    {
        //logging the accountant
        $query1 = "SELECT `userID`,`firstname`,`surname`,`role` FROM `loan_db_user` WHERE
                  `username` = :username AND `password` = MD5(:password) AND `role` = 'a'";
        $sth1 = $this->db->connector->prepare("$query1");
        $sth1->execute(array(
            ':username' => $username,
            ':password' => $password
        ));
        $accountant = $sth1->fetch(PDO::FETCH_ASSOC);
        if(!empty($accountant)){
            $session = $accountant['firstname'] . " " . $accountant['surname'];
            Session::init();
            header('location:/accounts/home');
            Session::set('loggedIn', $session);
            Session::set('loggedID', $accountant['userID']);
            return;
        }


        $query = "SELECT `userID`,`firstname`,`surname`,`role` FROM `loan_db_user` WHERE
                  `username` = :username AND `password` = MD5(:password) AND `role` = 'u'";
        $sth = $this->db->connector->prepare("$query");
        $sth->execute(array(
            ':username' => $username,
            ':password' => $password
        ));

        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($data)) {
            $session = $data['firstname'] . " " . $data['surname'];
            Session::init();
            header('location:/private/home');
            Session::set('loggedIn', $session);
            Session::set('loggedID', $data['userID']);
        } else {
            //header('location:/admin/');
            $adm = new privateController();
            privateController::$validCredentials = "Invalid Credentials!";
            $adm->index();
        }


    }

    function client($details = [], $action = 1)
    {

        if ($action == 1) {
            //creating a client if action is 1
            $zero = 0;
            try {
                $query = "INSERT INTO client VALUES (:id,:nid,:gid,:fname,:maiden,:sname,:gender,:marital,:dob,:sib,:home,:cell);";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':id' => $zero,
                    ':nid' => $details[0],
                    ':gid' => $details[1],
                    ':fname' => $details[2],
                    ':maiden' => $details[3],
                    ':sname' => $details[4],
                    ':gender' => $details[5],
                    ':marital' => $details[6],
                    ':dob' => $details[7],
                    ':sib' => $details[8],
                    ':home' => $details[9],
                    ':cell' => $details[10],
                ))) {
                    privateController::$message = "Client Registered Successfully";
                }

            } catch (Exception $e) {
                privateController::$error = $e->getMessage();
            }
        } elseif ($action == 2) {
            //Retrieving all clients
            try {
                $sql = "SELECT  a.*, b.name AS gname FROM client a, client_group b WHERE  a.groupID = b.groupID ORDER BY groupID";
                $stmt = $this->db->connector->prepare($sql);
                $stmt->execute();
                $clients = $stmt->fetchAll();
                return $clients;

            } catch (Exception $e) {
                echo $e->getMessage();
                return '';
            }
        }

        return '';
    }

    function group($action = 1, $Data = [])
    {
        if ($action == 1) {
            //Insertion
            try {
                $query = "INSERT INTO client_group VALUES (:id,:name,:chair,:cell,:vchair,:vcell,:zone)";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':id' => 0,
                    ':name' => $Data[0],
                    ':chair' => $Data[1],
                    ':cell' => $Data[2],
                    ':vchair' => $Data[3],
                    ':vcell' => '',
                    ':zone' => $Data[4],

                ))) {
                    $group = $Data[0];
                    privateController::$message = "Group $group Registered Successfully";
                    return true;
                }
            } catch (Exception $e) {
                privateController::$error = $e->getMessage();
                return false;
            }
        }
        if ($action == 2) {
            try {
                $query = "SELECT a.*, b.name AS zname FROM client_group a,zone b WHERE b.zoneID = a.zoneID";
                $stmt = $this->db->connector->prepare($query);
                $stmt->execute();
                return $stmt->fetchAll();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        return '';
    }

    function checkClient($id = '')
    {
        try {
            $query = "SELECT loanID FROM temp_loan WHERE clientID = :id";
            $stmt = $this->db->connector->prepare($query);
            $stmt->execute(array(
                ':id' => $id,
            ));

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function createLoan($client = '', $amount = '', $date = '', $officer = '', $onset = '', $transactionDate = '')
    {
        require_once CONFIG . 'calculator.php';

        //retrieving client details
        $client_data = $this->searchClient($client);


        //retrieving all holidays  for schedule
        $query = "SELECT `date` FROM Pholiday";
        $stmt = $this->db->connector->prepare($query);
        $stmt->execute();
        $holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $schedule = $this->getSchedule($onset, $holidays);

        //calculating the loan values
        $loan_data = calculator($amount, 30);

        //merging all the info
        $all_data = array("clientID" => $client_data['clientID'], "fname" => $client_data['firstname'], "maiden" => $client_data['maiden'], "sname" => $client_data['surname'], "onset" => $schedule[0], "last" => $schedule[17], "amt" => $amount,
            "daily" => $loan_data[0], "total" => $loan_data[1], "interest" => $loan_data[2], "officer" => $officer, "loanDate" => $date, "transactionDate" => $transactionDate);

        return $all_data;

    }

    function searchClient($index = '')
    {
        try {
            $query = "SELECT clientID, clientNID,firstname,maiden,surname FROM `client` WHERE clientID = :id";
            $stmt = $this->db->connector->prepare($query);
            $stmt->execute(array(
                ':id' => $index
            ));

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo $e->getMessage();
            return '';
        }
        return $result;
    }

    function getSchedule($onset = '', $holidays = [])
    {

        //getting the days from admin
        require_once MODEL . 'adminModel.php';
        $adminModel = new adminModel();
        $collection_days = $adminModel->collectionDay('', '', 3);
        $weekdays = array();

        //parsing the collection days array
        foreach ($collection_days as $col_day) {
            array_push($weekdays, $col_day['day']);
        }
        $schedule = array();
        $counter = 0;
        for ($days = 0; $days < 60; $days++) {
            if ($counter < 18) {
                $new_date = strtotime($onset . "+ $days days");
                $check = date('D', $new_date);
                $check_full = date('Y-m-d', $new_date);

                if (in_array($check, $weekdays) && !in_array($check_full, $holidays)) {
                    array_push($schedule, date("Y-m-d", $new_date));
                    $counter++;
                }
            }
        }

        return $schedule;

    }

    function saveLoan($loan = [])
    {
        $counter = 0;
        $seen = 'N';
        try {
            $query = "INSERT INTO temp_loan value (?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $this->db->connector->prepare($query);
            $stmt->bindParam(1, $counter);
            $stmt->bindParam(2, $loan[0]);
            $stmt->bindParam(3, $loan[1]);
            $stmt->bindParam(4, $loan[2]);
            $stmt->bindParam(5, $loan[3]);
            $stmt->bindParam(6, $loan[4]);
            $stmt->bindParam(7, $loan[5]);
            $stmt->bindParam(8, $loan[6]);
            $stmt->bindParam(9, $loan[7]);
            $stmt->bindParam(10, $loan[8]);
            $stmt->bindParam(11, $loan[9]);
            $stmt->bindParam(12, $seen);

            if ($stmt->execute()) {
                privateController::$message = "loan saved, please wait for approval ";
            }
        } catch (Exception $e) {
            privateController::$error = $e->getMessage();
        }
        return '';
    }

    function setupLoans()
    {

        //retrieving all the loans
        try {
            $query = "SELECT loanID,balance,onsetPayment FROM loan_sb";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()) {
                $loans = $stmt->fetchAll();
                //deactivating loans
                foreach ($loans as $loan) {
                    //checking if the payment date is already to activate the loans
                    $onset = $loan['onsetPayment'];
                    $onsetDate = (new DateTime($onset))->format('Y-m-d');
                    $today = (new DateTime(date('Y-m-d')))->format('Y-m-d');

                    if (strtotime($onsetDate) > strtotime($today) || $loan['balance'] < 1) {
                        //deactivating the loans
                        try {
                            $query = "UPDATE loan_sb SET status = '0' WHERE loanID = :id";
                            $stmt = $this->db->connector->prepare($query);
                            if ($stmt->execute(array(
                                ':id' => $loan['loanID'],
                            ))) {

                            }
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                    } elseif (strtotime($onsetDate) <= strtotime($today) || $loan['balance'] > 1) {
                        //activating the loans
                        try {
                            $query = "UPDATE loan_sb SET status = '1' WHERE loanID = :id";
                            $stmt = $this->db->connector->prepare($query);
                            if ($stmt->execute(array(
                                ':id' => $loan['loanID'],
                            ))) {

                            }
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                    }

                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

    function manageDefaultArrears()
    {

        //Saving  the totals arrears to total arrears for a loan
        try {
            $query = "SELECT loanID FROM loan_sb WHERE status = '1'";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()) {

                $loans = $stmt->fetchAll();
                //adding all the arrears totals for loan
                $totalArrears = [];
                foreach ($loans as $loan) {

                    $sql = "SELECT SUM(amount) AS Total FROM def_arrears_sb WHERE loanID = :id AND seen = '0'";
                    $stmt1 = $this->db->connector->prepare($sql);
                    $id = $loan['loanID'];
                    if ($stmt1->execute(array(
                        ':id' => $loan['loanID'],
                    ))) {
                        $Total = $stmt1->fetch(PDO::FETCH_ASSOC);
                        $totalArrears[$id] = $Total['Total'];

                        //updating the amount to zero after fetching it
                        $query1 = "UPDATE def_arrears_sb SET seen = '1' WHERE loanID = $id";
                        $stmt2 = $this->db->connector->prepare($query1);
                        $stmt2->execute();
                    }
                }

                //Inserting into the arrears total
                foreach ($totalArrears as $key => $amount) {

                    $sql = "SELECT total FROM arrears_total_sb WHERE  loanID = $key";
                    $stmt = $this->db->connector->prepare($sql);
                    if ($stmt->execute()) {
                        $total = $stmt->fetch(PDO::FETCH_ASSOC);
                        $newTotal = $total['total'] + $amount;
                        //updating the loan to set new total
                        $sql5 = "UPDATE arrears_total_sb SET total = $newTotal WHERE loanID = $key";
                        $stmt = $this->db->connector->prepare($sql5);
                        $stmt->execute();
                    }
                }


            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    function payment($loan = '', $date = '', $client = '', $action = 1)
    {

        //getting all repayment history of a particular day for a particular loan
        if ($action == 1) {
            try {
                $sql = "SELECT SUM(amount) AS Total FROM payment_sb WHERE date = :date ";
                $stmt = $this->db->connector->prepare($sql);
                if ($stmt->execute(array(
                    ':date' => $date,
                ))) {
                    $result = $stmt->fetchAll()[0]['Total'];
                    return $result;
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

    }

    // retrieves all active loans and calculate the installment of the loan
    function collection($loanDetails = [])
    {
        try {
            //$query = "SELECT * FROM loan_sb WHERE status = '1'";
            $query = "SELECT a.*, b.firstname, b.surname, c.name AS gname, d.total AS totalArrears FROM loan_sb a, client b, client_group c, arrears_total_sb d WHERE a.clientID = b.clientID AND d.loanID = a.loanID AND b.groupID = c.groupID AND a.status = '1'";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()) {
                $loanDetails = $stmt->fetchAll();
                $loans_all = $loanDetails;
                //inserting into def arrears the daily for that day
                foreach ($loans_all as $loan) {
                    $loanID = $loan['loanID'];
                    $daily = $loan['dailyPayment'];
                    $installment = $this->getInstallment($loanID);
                    if ($installment > 0) {

                        //checking if the installment is already added
                        {
                            $check = "SELECT loanID FROM def_arrears_sb WHERE loanID = $loanID and installment = $installment";
                            $stmt = $this->db->connector->prepare($check);
                            if ($stmt->execute()) {
                                $result = $stmt->fetchAll();
                                if (empty($result)) {
                                    //insertion now

                                    $sql1 = "INSERT INTO def_arrears_sb VALUES (:id,:loanID,:amount,:date,:inst,:seen)";
                                    $stmt1 = $this->db->connector->prepare($sql1);
                                    if ($stmt1->execute(array(
                                        ':id' => 0,
                                        ':loanID' => $loanID,
                                        ':amount' => $daily,
                                        ':date' => date('Y-m-d'),
                                        ':inst' => $installment,
                                        ':seen' => '0',
                                    ))) {

                                    } else {
                                        //
                                    }
                                }
                            }
                        }

                    }
                }
                return $loanDetails;


            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return $loanDetails;
        }
    }

    function getInstallment($id = '')
    {
        try {
            $query = "SELECT onsetPayment FROM loan_sb WHERE loanID = :id";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute(array(
                ':id' => $id,
            ))) {
                $array = $stmt->fetch(PDO::FETCH_ASSOC);
                $onset = $array['onsetPayment'];
                //processing the installment
                //getting the days from admin
                require_once MODEL . 'adminModel.php';
                $adminModel = new adminModel();
                $collection_days = $adminModel->collectionDay('', '', 3);
                $weekdays = array();

                //getting all the holidays
                //retrieving all holidays  for schedule
                $query = "SELECT `date` FROM Pholiday";
                $stmt = $this->db->connector->prepare($query);
                $stmt->execute();
                $holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $schedule = $this->getSchedule($onset, $holidays);

                $date = date('Y-m-d');
                $counter = 0;
                $installment = 0;

                foreach ($schedule as $day) {
                    $counter++;
                    if ($day == $date) {
                        $installment = $counter;
                    }
                }
                return $installment;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    function arrears($loan = '', $amount = 0.00, $date = '', $inst = 0)
    {
        try {
            $query = "INSERT INTO arrear_sb VALUES (:id,:loan,:amount,:date,:exp,:inst)";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute(array(
                ':id' => 0,
                ':loan' => $loan,
                ':amount' => $amount,
                ':date' => $date,
                ':exp' => "",
                ':inst' => $inst
            ))) {

            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    function advance($loan = '', $amount = 0.00, $date = '', $inst = 0)
    {
        try {
            $query = "INSERT INTO advance_sb VALUES (:id,:loan,:amount,:date,:inst)";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute(array(
                ':id' => 0,
                ':loan' => $loan,
                ':amount' => $amount,
                ':date' => $date,
                ':inst' => $inst
            ))) {

            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    function payLoan($loan, $amount, $date, $inst)
    {
        try {
            $query = "SELECT loanID, amountPaid, balance,totalDue FROM loan_sb WHERE loanID = $loan";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()) {
                $allLoans = $stmt->fetchAll();
                foreach ($allLoans as $loanData) {
                    $amountPaid = $loanData['amountPaid'];
                    $balance = $loanData['balance'];
                    $totalDue = $loanData['totalDue'];

                    //processing the payments
                    $amountPaid = $amountPaid + $amount;
                    $balance = $totalDue - $amountPaid;

                    $sql = "UPDATE loan_sb SET amountPaid = :paid, balance = :bal WHERE loanID = :id";
                    $stmt1 = $this->db->connector->prepare($sql);
                    if ($stmt1->execute(array(
                        ':paid' => $amountPaid,
                        ':bal' => $balance,
                        ':id' => $loanData['loanID'],
                    ))) {

                        try {
                            //subtracting from the arrears
                            $query2 = "SELECT total FROM arrears_total_sb WHERE loanID = :id";
                            $stmt3 = $this->db->connector->prepare($query2);
                            if ($stmt3->execute(array(
                                ':id' => $loanData['loanID'],
                            ))) {

                                $arrears = $stmt3->fetchAll();
                                $newArrears = $arrears[0]['total'] - $amount;
                                $id = $loanData['loanID'];
                                $sql3 = "UPDATE arrears_total_sb SET total = $newArrears WHERE loanID = $id";
                                $stmt4 = $this->db->connector->prepare($sql3);
                                if ($stmt4->execute()) {
                                }
                            }
                            //recording payment
                            $sql2 = "INSERT INTO payment_sb VALUES(:id,:loan,:collector,:amount,:date,:time,:inst,:adv,:arr)";
                            $stmt2 = $this->db->connector->prepare($sql2);
                            if ($stmt2->execute(array(
                                ':id' => 0,
                                ':loan' => $loanData['loanID'],
                                ':collector' => Session::get('loggedIn'),
                                ':amount' => $amount,
                                ':date' => date('Y-m-d'),
                                ':time' => date('H-i-s'),
                                ':inst' => $inst,
                                ':adv' => 0.00,
                                ':arr' => 0.00,
                            ))) {
                                privateController::$message = "Loan " . $loan . " Paid and recorded ";
                            }
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                }

            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    function findClient($index = '')
    {
        try {
            $output = '';
            $query = "SELECT clientID, client.* FROM client WHERE firstname LIKE '%" . $index . "%' OR maiden LIKE '%" . $index . "%' OR surname LIKE '%" . $index . "%' OR clientNID LIKE '%" . $index . "%'  ";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()) {
                $results = $stmt->fetchAll(PDO::FETCH_UNIQUE);
                if (!empty($results)) {
                    $output .= '<h5 style="text-align: center; position:relative; color: #1c7430"> Search results </h5><hr>';
                    $output .= '<div class="table-responsive">
                                   <table class="table">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Maiden</th>
                                        <th>Surname</th>
                                        <th>Group</th>
                                        <th>Cell</th>
                                        <th></th>
                                        
                                     </tr>';

                    foreach ($results as $result) {
                        $output .= '
                                    <tr>
                                        <td>' . $result["clientNID"] . '</td>
                                        <td>' . $result["firstname"] . '</td>
                                        <td>' . $result["maiden"] . '</td>
                                        <td>' . $result["surname"] . '</td>
                                        <td>' . self::getGroup($result["groupID"]) . '</td>
                                        <td>' . $result["cell"] . '</td>
                                        <td>
                                          <div class="dropdown">
                                                <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="menu1" data-toggle="dropdown">actions
                                                <span class="caret"></span></button>
                                                <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                                                  <li role="presentation" style="max-width: 20px">
                                                    <form action="/private/client" method="post">
                                                        <input type="hidden" name="clientID" value="'.$result['clientID'].''.'">
                                                        <input type="submit" name="edit-client" class="lightbtn" value="edit">
                                                    </form>
                                                  </li>
                                                  <li role="presentation" style="max-width: 20px">
                                                    <form action="/private/client" method="post">
                                                        <input type="hidden" name="clientID" value="'.$result['clientID'].''.'">
                                                        <input type="submit" name="view-client" class="lightbtn" value="view ">
                                                    </form>
                                                  </li>
                                                </ul>
                                            </div>
                                        </td>
                                     </tr>
                        ';
                    }
                } else {
                    $output = "<h5 style='background-color: whitesmoke; color: indianred; text-align: center'> no results found ! </h5> <hr>";
                }

                echo $output;
            }
        } catch (Exception $e) {
            print json_encode($e->getMessage());
        }
    }

    function getGroup($id=''){
        try{
            $query = "SELECT * FROM client_group WHERE groupID = $id";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()){
                $data = $stmt->fetchAll();
                $name = $data[0]['name'];
                return $name;
            }
        }catch (Exception $e){
            return $e->getMessage();
        }
    }
    function editGroup($id='',$Data=[],$action=1){
        try{
            if ($action == 1){
                $query = "SELECT * FROM client_group WHERE groupID = $id";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute()){
                    $data = $stmt->fetchAll();
                    return $data;
                }
            }elseif ($action == 2){
                $query = "UPDATE client_group SET name=:name, chairID=:chair,chair_no=:cell,vchairID =:vchair,vice_no=:cell2, zoneID=:zone WHERE groupID = $id";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':name' => $Data[0],
                    ':chair'=>$Data[1],
                    ':cell'=>$Data[2],
                    ':vchair'=> $Data[3],
                    ':cell2'=>$Data[4],
                    ':zone'=>$Data[5]
                ))){
                    header('location:/private/group');
                }
            }
        }catch (Exception $e){
            return $e->getMessage();
        }
    }
    function viewClient($id = ''){
        try{
            $Data = array();
            //getting the client Details
            $query = "SELECT a.firstname,a.maiden,a.surname,b.name AS name FROM client a, client_group b WHERE a.groupID = b.groupID AND a.clientID = $id";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()){
                $client = $stmt->fetchAll();
            }

            $query = "SELECT * FROM payment_sb WHERE loanID = (SELECT loanID FROM loan_sb WHERE clientID = $id)";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()){
                $rawPayments = $stmt->fetchAll();
                //var_dump($rawPayments);
            }

            //getting the arrears for client
            $query = "SELECT * FROM arrear_sb where loanID = (SELECT loanID FROM loan_sb WHERE clientID = $id)";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()){
                $arrears = $stmt->fetchAll();

            }
            $Data['repayment'] = $rawPayments;
            $Data['arrears']= $arrears;
            $Data['clients'] = $client;
            return $Data;
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }
}