<?php

namespace SolutionCore\Controllers;

use Symfony\Component\Yaml\Parser,
    Firebase\JWT\JWT;

/**
 * Description of Security
 *
 * @author doug
 */
class Security {

    private $yaml;
    protected $jwt;
    protected $config;

    public function __construct() {
        //Json Web Token, Using this instead of sessions. It's stateless, prettier and the modern way.
        //On successfull login, we build an array of Auth data, expiration date, etc. Then serialize it and send it to the front end.
        //This packet then gets sent backwards and forwards. If the unique key is ever altered then the auth will fail.
        //Check https://github.com/firebase/php-jwt or for more info on the backend. And
        //https://github.com/auth0/angular-jwt for the front. And https://jwt.io/ for more general info.
        $this->jwt = new JWT();
        //YAML, because it's nicer to look at than a PHP array or XML.
        $this->yaml = new Parser();
        $this->config = $this->yaml->parse(file_get_contents("../Application/Config/Config.yml"));
    }

    public function encodePassword($password) {
        return md5($this->config['key']['password'] . $password);
    }

    public function checkPassword($passwordGiven, $passwordActual) {
        if ($this->encodePassword($passwordGiven) === $passwordActual) {
            return true;
        } else {
            return false;
        }
    }

    public function EncodeSecurityToken($user) {       
        $key = $this->config['key']['secret_key'];
        $time = \time();
        $token = array(
            "iss" => "https://portal.solutionhost.co.uk",
            "user" => array(
                "id" =>  $user['id'],
                "username" => $user['username'],
                "client" => ltrim($user['client'], 0),
                "level" => "admin",
            ),
            //Created at "now"
            "iat" => $time,
            //Not before now - 10 incase of slight variance.
            "nbf" => $time,
            //Set maximum Expiry time currently half hour, this gets refreshed everytime they make a call to the backend.
            "exp" => $time + (60 * 30)
//            "exp" => $time + (60 * 60)
        );
        return $this->jwt->encode($token, $key);
    }
    

    public function DecodeSecurityToken($token) {
        try {
            $auth = $this->jwt->decode($token, $this->config['key']['secret_key'], array('HS256'));
            return $auth;
        } catch (\Exception $e) {
            return print "Uh oh you ain't allowed here..." . $e;
        }
    }

}
