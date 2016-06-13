<?php

namespace SolutionCore\Controllers;

use SolutionCore\Controllers\ErrorController;

class Application {

    /** @var null The project */
    private $project = null;

    /** @var null The controller */
    private $controller = null;

    /** @var null The method (of the above controller), often also named "action" */
    private $action = null;

    /** @var array URL parameters */
    private $params = array();

    function getProject() {
        return $this->project;
    }

    function getController() {
        return $this->controller;
    }

    function getAction() {
        return $this->action;
    }

    function getParams() {
        return $this->params;
    }

    function setProject($project) {
        $this->project = $project;
    }

    function setController($controller) {
        $this->controller = $controller;
    }

    function setAction($action) {
        $this->action = $action;
    }

    function setParams($params) {
        $this->params = $params;
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
        if (!$this->controller) {

            //require APP . 'controller/home.php';
            $page = new \SolutionMvc\Controller\HomeController();
            $page->indexAction();
        } elseif (file_exists(APP . $this->project . '/Controller/' . $this->controller . 'Controller.php')) {
            // here we did check for controller: does such a controller exist ?
            // if so, then load this file and create this controller
            // example: if controller would be "car", then this line would translate into: $this->car = new car();
            require APP . $this->project . '/Controller/' . $this->controller . 'Controller.php';

            // Hacking together to allow namespace loading of class.
            $base = "\\SolutionMvc\\" . $this->project . "\\Controller\\" . $this->controller . "Controller";

            $this->controller = new $base;

            // check for method: does such a method exist in the controller ?
            if (method_exists($this->controller, $this->action)) {

                if (!empty($this->params)) {
                    // Call the method and pass arguments to it
                    // eg localhost/Controller/Action/param1/param2 etc
                    call_user_func_array(array($this->controller, $this->action), $this->params);
                } else {
                    // If no parameters are given, just call the method without parameters, like $this->home->method();
                    $this->controller->{$this->action}();
                }
            } else {
                if (strlen($this->action) == 0) {
                    // no action defined: call the default index() method of a selected controller
                    $this->controller->indexAction();
                } else {
                    //Something failed so direct to error controller
//                    header('location: ' . URL . 'error');                    
                    $error->errorType404Action("Error - No action found, and indexAction not defined.");
                }
            }
        } else {
            $error->errorType404Action("Error - Project or Controller does not exist");
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


            $project = isset($url[0]) ? ucfirst(strtolower($url[0])) : null;
            $controller = isset($url[1]) ? ucfirst(strtolower($url[1])) : null;
            $action = isset($url[2]) ? ucfirst(strtolower($url[2])) . "Action" : null;



            // Put URL parts into according properties
            // By the way, the syntax here is just a short form of if/else, called "Ternary Operators"
            // @see http://davidwalsh.name/php-shorthand-if-else-ternary-operators
            $this->project = isset($project) ? $project : null;
            $this->controller = isset($controller) ? $controller : null;
            $this->action = isset($action) ? $action : null;
//            die($this->url_action);
            // Remove controller and action from the split URL
            unset($url[0], $url[1], $url[2], $project, $action, $controller);

            // Rebase array keys and store the URL params
            $this->params = array_values($url);

            // for debugging. uncomment this if you have problems with the URL
            //echo 'Controller: ' . $this->url_controller . '<br>';
            //echo 'Action: ' . $this->url_action . '<br>';
            //echo 'Parameters: ' . print_r($this->url_params, true) . '<br>';
        }
    }

}
