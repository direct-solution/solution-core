<?php

namespace SolutionMvc\Core;

use SolutionMvc\Controller\ErrorController;

class Application {
    

    /** @var null The controller */
    private $url_controller = null;

    /** @var null The method (of the above controller), often also named "action" */
    private $url_action = null;

    /** @var array URL parameters */
    private $url_params = array();

    function getUrl_controller() {
        return $this->url_controller;
    }

    function getUrl_action() {
        return $this->url_action;
    }

    function getUrl_params() {
        return $this->url_params;
    }

    function setUrl_controller($url_controller) {
        $this->url_controller = $url_controller;
    }

    function setUrl_action($url_action) {
        $this->url_action = $url_action;
    }

    function setUrl_params($url_params) {
        $this->url_params = $url_params;
    }

    /**
     * "Start" the application:
     * Analyze the URL elements and calls the according controller/method or the fallback
     */
    public function __construct() {

        // Check for Options request, if true send back 200. Options headers fuck everything up by attempting to process a blank packet.
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            return http_response_code(200);
        }
        // create array with URL parts in $url
        $this->splitUrl();
        $error = new ErrorController();

        // check for controller: no controller given ? then load start-page
        if (!$this->url_controller) {

            //require APP . 'controller/home.php';
            $page = new \SolutionMvc\Controller\HomeController();
            $page->indexAction();
        } elseif (file_exists(APP . 'Controller/' . $this->url_controller . 'Controller.php')) {
            // here we did check for controller: does such a controller exist ?
            // if so, then load this file and create this controller
            // example: if controller would be "car", then this line would translate into: $this->car = new car();
            require APP . 'Controller/' . $this->url_controller . 'Controller.php';

            // Hacking together to allow namespace loading of class.
            $base = "\\SolutionMvc\\Controller\\" . $this->url_controller . "Controller";

            $this->url_controller = new $base;

            // check for method: does such a method exist in the controller ?
            if (method_exists($this->url_controller, $this->url_action)) {

                if (!empty($this->url_params)) {
                    // Call the method and pass arguments to it
                    // eg localhost/Controller/Action/param1/param2 etc
                    call_user_func_array(array($this->url_controller, $this->url_action), $this->url_params);
                } else {
                    // If no parameters are given, just call the method without parameters, like $this->home->method();
                    $this->url_controller->{$this->url_action}();
                }
            } else {
                if (strlen($this->url_action) == 0) {
                    // no action defined: call the default index() method of a selected controller
                    $this->url_controller->indexAction();
                } else {
                    //Something failed so direct to error controller
//                    header('location: ' . URL . 'error');                    
                    $error->errorType404Action("OIOFIJ");
                }
            }
        } else {
            $error->errorType404Action("OIOFIJ");
        }
    }

    /**
     * Get and split the URL
     */
    private function splitUrl() {
        if (isset($_GET['url'])) {

            // split URL
            $url = trim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);

            $controller = isset($url[0]) ? ucfirst(strtolower($url[0])) : null;
            $action = isset($url[1]) ? ucfirst(strtolower($url[1])) . "Action" : null;



            // Put URL parts into according properties
            // By the way, the syntax here is just a short form of if/else, called "Ternary Operators"
            // @see http://davidwalsh.name/php-shorthand-if-else-ternary-operators
            $this->url_controller = isset($controller) ? $controller : null;
            $this->url_action = isset($action) ? $action : null;
//            die($this->url_action);
            // Remove controller and action from the split URL
            unset($url[0], $url[1], $action, $controller);

            // Rebase array keys and store the URL params
            $this->url_params = array_values($url);

            // for debugging. uncomment this if you have problems with the URL
            //echo 'Controller: ' . $this->url_controller . '<br>';
            //echo 'Action: ' . $this->url_action . '<br>';
            //echo 'Parameters: ' . print_r($this->url_params, true) . '<br>';
        }
    }

}
