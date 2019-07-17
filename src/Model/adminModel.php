<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/13/19
 * Time: 1:52 PM
 */

class adminModel extends Model
{
    function __construct()
    {
        parent::__construct();
        //echo "am in private model";
    }

    function validate($username = '',$password=''){
        $query = "SELECT `userID`,`firstname`,`surname`,`role` FROM `loan_db_user` WHERE
                  `username` = :username AND `password` = MD5(:password) AND `role` = 'r'";
        $sth = $this->db->connector->prepare("$query");
        $sth->execute(array(
            ':username' => $username,
            ':password' => $password
        ));

        $data = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($data)){
            $session = $data['firstname']." ".$data['surname'];
            //$count = $sth->rowCount();

            Session::init();
            Session::set('loggedIn',$session);
            header('location:/admin/home');
        }else{

            //header('location:/admin/');
            $adm = new adminController();
            adminController::$validCredentials = "Invalid Credentials!";
            $adm->index();

        }


    }
    function user($id='',$Data=[],$acton =1){

        if ($acton == 1){
            try{
                //insertion

                $query  = "INSERT INTO loan_db_user VALUES (:id,:uname,MD5(:pass),:fname,:sname,:email,:role)";
                $stmt   = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':id'       => 0,
                    ':uname'    => $Data[2],
                    ':pass'     => $Data[3],
                    ':fname'    => $Data[0],
                    ':sname'    => $Data[1],
                    ':email'    => 'NA',
                    ':role'     => $Data[4],
                ))){
                    adminController::$message = "User $Data[2] Created Successfully";
                }
            }catch (Exception $e){
                adminController::$error = $e->getMessage();
            }
        }elseif ($acton == 2){
            //searching
            try{
                $query = "SELECT * FROM loan_db_user WHERE username = :id OR firstname = :id OR surname = :id OR role = :id";
                $stmt  = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':id' => $id,
                ))){
                    return $stmt->fetchAll();
                }
            }catch (Exception $e){
                return $e->getMessage();
            }
        }elseif ($acton == 3){
            //retrieve user details given an ID
            try{
                $query = "SELECT * FROM loan_db_user WHERE userID = (:id)";
                $stmt  = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':id' => $id,
                ))){
                    return $stmt->fetchAll();
                }
            }catch (Exception $e){
                return $e->getMessage();
            }
        }
        return '';
    }
    function passwordChange($pass ='',$id = ''){
        try{
            $query = "UPDATE loan_db_user SET password = MD5(:pass) WHERE userID = :id";
            $stmt  =  $this->db->connector->prepare($query);
            if ($stmt->execute(array(
                ':pass' => $pass,
                ':id'   => $id
            ))){
                adminController::$message = "Password information changed for user id: $id";
                return true;
            }
        }catch (Exception $e){
            adminController::$error = "Error in changing pass for use id: $id ".$e->getMessage();
            return false;
        }
    }
    function zone($Data=[],$index='',$action=1){
        if ($action == 1){
            $a = 0;
            $zone = $Data;
            try{
                $query  = "INSERT INTO zone VALUES (?,?,?,?,?)";
                $stmt   = $this->db->connector->prepare($query);
                $stmt->bindParam(1,$a);
                $stmt->bindParam(2,$zone[0]);
                $stmt->bindParam(3,$zone[1]);
                $stmt->bindParam(4,$zone[3]);
                $stmt->bindParam(5,$zone[2]);
                if($stmt->execute()){
                    adminController::$message = "Zone $Data[0] added";
                }
            }catch (Exception $e){
                adminController::$error =  $e->getMessage();
            }
        }
        if ($action==2){
            try{
                // retrieving all the zones
                $query  = "SELECT * FROM zone";
                $stmt   =  $this->db->connector->prepare($query);
                $stmt->execute();
                return $stmt->fetchAll();
            }catch (Exception $e){
                echo $e->getMessage();
            }

        }elseif ($action == 3){
            try{
                $query  = "DELETE  FROM  zone WHERE zoneID = :index";
                $stmt   = $this->db->connector->prepare($query);
                $stmt   ->execute(array(
                    ':index' => $index
                ));

            }catch(Exception $e){
                echo $e->getMessage();
            }

        }
        elseif ($action == 4){
            try{
                // retrieving all the zones
                $query  = "SELECT * FROM zone WHERE zoneID = $index";
                $stmt   =  $this->db->connector->prepare($query);
                $stmt->execute();

                return $stmt->fetchAll()[0];

            }catch (Exception $e){
                echo $e->getMessage();
            }
        }
        return '';
    }
    function deleteUser($id=''){
        try{
            $query = "DELETE FROM loan_db_user WHERE userID = :id";
            $stmt = $this->db->connector->prepare($query);
            $stmt->execute(array(
                ':id'=>$id,
            ));
        }catch (Exception $e){
            echo  $e->getMessage();
        }
    }
    function holiday($date='',$desc='',$id='',$action =1){
        if ($action == 1){
            $a = 0;
            //Insertion oh Holidays
            try{
                $query = "INSERT INTO Pholiday value (?,?,?)";
                $stmt = $this->db->connector->prepare($query);
                $stmt->bindParam(1,$a);
                $stmt->bindParam(2,$date);
                $stmt->bindParam(3,$desc);

                if ($stmt->execute()){
                    adminController::$message = "Holiday on $date Saved Successfully";
                }
            }catch (Exception $e){
                adminController::$error = $e->getMessage();
            }
        }elseif ($action == 2){
            //Retrieve all the  Holidays
            try {
                $query = "SELECT * FROM Pholiday ORDER BY date ";
                $stmt = $this->db->connector->prepare($query);
                $stmt->execute();
                return $stmt->fetchAll();
            }catch (Exception $e){
                return "";
            }
        }elseif ($action == 3){
            //Delete Holidays
            try{
                $sql = "DELETE FROM Pholiday WHERE HolidayID =:id";
                $stmt = $this->db->connector->prepare($sql);
                $stmt->execute(array(
                    ':id' => $id,
                ));
            }catch (Exception $e){
                echo $e->getMessage();
            }
        }

        return '';
    }
    function collectionDay($day='',$id='',$action=1){

        if ($action == 1){
            //Insertions
            try{
                $query = "INSERT INTO collectionDays VALUES (:id,:day)";
                $stmt  = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':id'   =>0,
                    ':day'  =>$day,
                ))){
                    adminController::$message = "Collection Day: $day added";
                }
            }catch (Exception $e){
                adminController::$error = $e->getMessage();
            }
        }elseif ($action == 2){
            //Delete Day
            $query = "DELETE FROM collectionDays WHERE dayID = (:id)";
            $stmt  = $this->db->connector->prepare($query);
            $stmt->execute(array(
                ':id' => $id,
            ));
        }elseif ($action == 3){
            // Retrieve all Days

            $query = "SELECT * FROM collectionDays";
            $stmt  = $this->db->connector->prepare($query);
            if ($stmt->execute()){
                return $stmt->fetchAll();
            }
        }
    }
    function getPendingLoans(){
        try{
//            $query = "SELECT * FROM temp_loan WHERE `seen` =:seen ";
            $query = "SELECT a.*, b.firstname,b.surname, c.name AS gname FROM temp_loan a, client b , client_group c WHERE a.clientID = b.clientID AND b.groupID = c.groupID AND a.seen = 'N'";
            $stmt = $this->db->connector->prepare($query);
            $stmt->execute(array(
                ':seen' => "N",
            ));
            $pending = $stmt->fetchAll();
            if (!empty($pending)){
                return $pending;
            }else{
                return '';
            }
        }catch (Exception $e){
            return '';
        }
    }
    function approveLoan($id){
        try{
            $query = "SELECT * FROM temp_loan WHERE loanID = :id";
            $stmt  =  $this->db->connector->prepare($query);
            if ($stmt->execute(array(
                ':id' => $id
            ))){
                $loanData = $stmt->fetchAll()[0];
                if (!empty($loanData)){
                    //Saving loan
                    try{
                        $sql    = "INSERT INTO loan_sb values (:id,:client,:officer,:dateTaken,:onset,:last,:amount,:totalDue,:daily,:amtPaid,:balance,:stat)";
                        $stmt1  =  $this->db->connector->prepare($sql);
                        if ($stmt1->execute(array(
                            ':id' => 0,
                            ':client' => $loanData['clientID'],
                            ':officer'=> $loanData['officer'],
                            ':dateTaken' => $loanData['date_taken'],
                            ':onset' => $loanData['onset'],
                            ':last' => $loanData['last_payment'],
                            ':amount' => $loanData['amount'],
                            ':totalDue'=>$loanData['total'],
                            ':daily' => $loanData['installment'],
                            ':amtPaid'=> 0.00,
                            ':balance'=> $loanData['total'],
                            ':stat' => '1',
                        ))){

                            //Creating Total arrears entry for the loan
                            try{
                                $sql1 = "INSERT INTO arrears_total_sb VALUES (:id,:loan,:tot)";
                                $sql1 = $this->db->connector->prepare($sql1);
                                if ($sql1 ->execute(array(
                                    ':id' => 0,
                                    ':loan' => $loanData['loanID'],
                                    ':tot' => 0,
                                ))){
                                    //Deleting From Temporally;
                                    self::removeTempLoan($loanData['loanID']);
                                    $client = $loanData['loanID'];
                                    adminController::$message = "Loan saved Successsfully ID: $client";
                                }
                            }catch (Exception $e){
                                echo $e->getMessage();
                            }

                        }
                    }catch (Exception $e){
                        echo $e->getMessage();
                    }
                }
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }
    function removeTempLoan($id){
        try{
            $query = "DELETE FROM temp_loan WHERE loanID = :id";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute(array(
                ':id' => $id,
            ))){
                return true;
            }
        }catch (Exception $e){
            echo $e->getMessage();
            return false;
        }

        return '';
    }
    function checkClient($client =''){
        try{
            $query = "SELECT clientID FROM  loan_sb WHERE clientID = :client AND balance > 1";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute(array(
                ':client' => $client,
            ))){
                if (empty($stmt->fetchAll())){
                    return true;
                }else{
                    adminController::$error = "The client has unsettled loans please settle them!!!";
                    return false;
                }
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
        return '';
    }
    function update($Data =[],$action = 1){

        //updating zone
        if ($action == 1){
            try{
                $query = "UPDATE zone SET name = :name,location = :loc,zone_chair=:digit,Chair_Name =:chair WHERE zoneID = :id";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':name' => $Data[1],
                    ':loc'  => $Data[2],
                    ':digit'  => $Data[4],
                    ':chair'  => $Data[3],
                    ':id'    => $Data[0],
                ))){
                    $zoneName = $Data[1];
                    adminController::$message = "Zone $zoneName Updated";
                }
            }catch (Exception $e){
                adminController::$error = "Error ". $e->getMessage();
            }
        }
    }
    function client($id='',$action=1,$data=[]){
        if ($action == 1){
            $query = "SELECT * FROM client WHERE clientID = $id";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()){
                //returning the data
                $clientData = $stmt->fetchAll();
                if (!empty($clientData)){
                    return $clientData;
                }else{
                    return "Cannot Find The Data";
                }

            }else{
                return "Cannot Find The data";
            }
        }
        if ($action == 2){
            //updating the data
            $query = "UPDATE client SET firstname =:fname, maiden=:mname, surname=:sname,groupID=:gid WHERE clientID =:cid";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute(array(
                ':fname' => $data[0],
                ':mname' => $data[1],
                ':sname' => $data[2],
                ':gid'   => $data[3],
                ':cid'   => $data[4]
            ))){
                adminController::$message = "Details For ".$data[0]." changed successfully";
            }else{
                adminController::$error = "Could not Update Details for ".$data[0];
            }
        }

        return '';
    }
    function quickSummary(){

        try{

            $summary = array();
            //clients
            $query = "SELECT COUNT(*) AS TOTAL FROM client GROUP BY gender";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()){
                $results = $stmt->fetchAll();
                    $summary['male'] = $results[0]['TOTAL'];
                    $summary['female'] = $results[1]['TOTAL'];
                    $summary['totalClients']= $summary['male'] + $summary['female'];

            }

            // groups
            $query = "SELECT COUNT(groupID) AS TOTAL FROM client_group";
            $stmt = $this->db->connector->prepare($query);
            if ($stmt->execute()){
                $summary['totalGroups'] = $stmt->fetch(PDO::FETCH_ASSOC)['TOTAL'];
            }
            //active loans
            $query = "SELECT COUNT(loanID) AS TOTAL FROM loan_sb";
            $stmt= $this->db->connector->prepare($query);
            if ($stmt->execute()){
                $summary['totalLoans'] = $stmt->fetch(PDO::FETCH_ASSOC)['TOTAL'];
            }

            return $summary;
        }catch (Exception $e){
            echo $e->getMessage();
        }
        return '';
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
                                                    <form action="/admin/client" method="post">
                                                        <input type="hidden" name="clientID" value="'.$result['clientID'].''.'">
                                                        <input type="submit" name="edit-client" class="lightbtn" value="edit">
                                                    </form>
                                                  </li>
                                                  <li role="presentation" style="max-width: 20px">
                                                    <form action="/admin/client" method="post">
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
    function message($Data=[],$action=''){
        try{
            if ($action == 1){
                //message insertion

                $query = "INSERT INTO message values (:id,:date,:title,:body,:thumb,:publisher,:type,:active)";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':id'=> 0,
                    ':date' => $Data[0],
                    ':title'=>$Data[3],
                    ':body'=>$Data[4],
                    ':thumb'=>'my path',
                    ':publisher'=>$Data[1],
                    ':type'=>$Data[2],
                    'active'=>'1',
                ))){
                   adminController::$message = " A message published successfully ";
                }else{
                    adminController::$error ="There was an error in publishing the message";
                }
            }elseif ($action == 2){
                $query = "SELECT * FROM message WHERE active = '1'";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute()){
                    $messages = $stmt->fetchAll();
                    return $messages;
                }
            }elseif ($action == 3){
                $id = $Data[0];
                var_dump($id);
                $query = "DELETE FROM message WHERE messageID = :id";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute(array(':id'=> $id))){
                    adminController::$message = "Message Deleted Successfully";
                }else{
                    adminController::$error = "Error in deleting message";
                }
            }elseif ($action == 4){
                $query = "SELECT * FROM message WHERE messageID = :id";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute(array(':id'=> $Data[0]))){
                    $messages = $stmt->fetchAll();
                    return $messages;
                }
            }elseif ($action == 5){
                //updating

                $query = "UPDATE message SET title = :title, body = :body";
                $stmt= $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':title' => $Data[0],
                    'body' => $Data[1],
                ))){
                    adminController::$message = "Updated Successfully";
                }else{
                    adminController::$error="Failed to update";
                }
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

}