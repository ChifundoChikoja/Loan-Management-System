<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/12/19
 * Time: 7:48 PM
 */

class Database extends PDO
{
    public $connector;
    protected $affected_rows = '';
    protected $last_insert_id;
    protected $infected_rows_array = [];
    protected $transaction_mode = false;
    protected $transaction_done = false;
    private $error = "An Error Occured in ";

    public function __construct()
    {

        if (file_exists(CONFIG . 'config.inc')) {
            include_once CONFIG . 'config.inc';

            if (!empty(DATABASE) && count(DATABASE) === 6):
                foreach (DATABASE as $key => $value) {
                    if (isset($value)) {
                        switch ($key) {
                            case 'username';
                                $username = $value;
                                break;

                            case 'password';
                                $password = $value;
                                break;

                            case 'charset';
                                $charset = $value;
                                break;

                            case 'hostname';
                                $hostname = $value;
                                break;

                            case 'db_server';
                                $db_server = $value;
                                break;

                            case 'db_name';
                                $db_name = $value;
                                break;
                        }
                    }
                }endif;
        }

        $server = $db_server.':host='.$hostname.'; dbname='.$db_name.'; charset='.$charset;
        try {
            $this->connector = new PDO($server, $username, $password);
            // set the PDO error mode to exception
            $this->connector->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //echo "Connected successfully";
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }


    }


}