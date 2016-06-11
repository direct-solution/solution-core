<?php
SolutionCore\Controllers;
/**
 * Class Error
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */
class ErrorController extends \SolutionCore\Controllers\Controller
{
    public function __construct() {
    }
    
    public function errorType404Action(){
        
        require APP . 'View/_templates/header.php';
        require APP . 'View/error/404.php';
        require APP . 'View/_templates/footer.php';
    }
}
