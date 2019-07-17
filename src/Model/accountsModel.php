<?php


class accountsModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    function expense($data=[],$action=1){

        try{
            if ($action == 1){
                $query = "INSERT INTO expenses VALUES  (:id,:date,:amount,:desc,:signatory,:state)";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute(array(
                    ':id'=>0,
                    ':date'=>$data[0],
                    ':amount'=> $data[1],
                    ':desc'=> $data[2],
                    ':signatory'=> Session::get("loggedIn"),
                    ':state' => 'A'
                ))){
                    accountsController::$message="New Expense Recorded";
                }
            }elseif ($action == 2){
                //searching all the expenses
                $date = $data[0];
                $output="";
                $query = "SELECT expenseID, expenses.* FROM expenses WHERE date LIKE '%". $date."%'";
                $stmt = $this->db->connector->prepare($query);
                if ($stmt->execute()){
                    $results = $stmt->fetchAll(PDO::FETCH_UNIQUE);
                    $output .= '<h5 style="text-align: center; position:relative; color: #1c7430"> Search results </h5><hr>';
                    $output .= '<div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th>No</th>
                                            <th>Date</th>
                                            <th>Expense</th>
                                            <th>Amount</th>
                                            <th>Signatory</th>
                                        </tr>
                                    

                               ';
                    $counter = 1;
                    foreach ($results as $result){
                        $output .= '
                            <tr>
                                <td>'.$counter.'</td>
                                <td>' . $result["date"] . '</td>
                                <td>' . $result["description"] . '</td>
                                <td>' . $result["amount"] . '</td>
                                <td>' . $result["signatory"] . '</td>
                                
                            </tr>
                            
                        ';
                        $counter++;
                    }
                    echo $output;
                }
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }
}