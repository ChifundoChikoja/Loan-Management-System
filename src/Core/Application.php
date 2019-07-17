<?php
/**
 * Created by PhpStorm.
 * User: coutinho
 * Date: 2/9/19
 * Time: 9:28 PM
 */


class Application
{
	protected $controller = '';
	protected $action = '';
	protected $params = [];

	public function __construct(){
		$this->parseUrl();
		$this->processUrl();
		//var_dump($this->controller);
	}

    protected function parseUrl(){
        $request = trim($_SERVER['REQUEST_URI'],'/');
        if (!empty($request)) {
        	# code...
        	$url = explode('/', $request);

        	$this->controller = isset($url[0])? $url[0].'Controller' : 'privateController';
        	$this->action = isset($url[1])? $url[1] : 'index';

        	//unsetting the @controller and action 

        	unset($url[0],$url[1]);

        	$this->params = !empty($url)? array_values($url) : [];
        }else{
            $this->controller = 'privateController';
            $this->action = 'index';
        }

    }

    public function processUrl(){
	    if (file_exists(CONTROLLER . $this->controller . '.php')){
	        require_once CONTROLLER . $this->controller . '.php';
	        $this->controller = new $this->controller();

            if (method_exists($this->controller,$this->action)){
                call_user_func_array([$this->controller,$this->action] , $this->params);
            }
            else{
                //redirecting to home if bad url
                //require_once CONTROLLER.'privateController.php';
                $this->controller = new privateController();
                $this->controller->index();
            }
        }else{
            require_once CONTROLLER . 'privateController.php';
            $this->controller = new privateController();
            $this->controller->index();
        }
}


}