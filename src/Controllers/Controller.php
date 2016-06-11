<?php

namespace SolutionCore\Controllers;

class Controller {

    public function __construct() {
        $postdata = json_decode(file_get_contents("php://input"));
        $this->token = $this->security->DecodeSecurityToken($postdata->data);
    }

}
