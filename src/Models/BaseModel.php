<?php

namespace SolutionCore\Model;

use PDO,
    Symfony\Component\Yaml\Parser;

/**
 * Description of BaseModel
 *
 * @author doug
 */
class BaseModel {

    /**
     * @var null Database Connection
     */
    public $db = null;
    public $orm = null;

    /** @var string */
    private $tableName;
    private $config;
    /**
     * @var null Model
     */
    public $model = null;

    /**
     * Whenever controller is created, open a database connection too and load "the model".
     */
    public function __construct() {
        $this->yaml = new Parser();
        $this->config = $this->yaml->parse(file_get_contents("../Application/Config/Config.yml"));
        $this->db = $this->config['db'];
        $this->openDatabaseConnection();
        $this->tableName = $this->tableNameByClass(get_class($this));
    }

    /**
     * Open the database connection with the credentials from application/config/config.php
     */
    private function openDatabaseConnection() {
        // set the (optional) options of the PDO connection. in this case, we set the fetch mode to
        // "objects", which means all results will be objects, like this: $result->user_name !
        // For example, fetch mode FETCH_ASSOC would return results like this: $result["user_name] !
        // @see http://www.php.net/manual/en/pdostatement.fetch.php
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

        // generate a database connection, using the PDO connector
        // @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
        $this->db = new PDO($this->db['type'] . ':host=' . $this->db['host'] . ';dbname=' . $this->db['name'] . ';charset=' . $this->db['charset'], $this->db['user'], $this->db['pass'], $options);
       // $this->db = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, $options);

        $this->orm = new \SolutionORM\SolutionORM($this->db);
    }

    /**
     * Determine table by class name
     * @param string
     * @return string
     * @result:Pages => pages, ArticleTag => article_tag
     */
    private function tableNameByClass($className) {
        $tableName = explode("\\", $className);
        $tableName = lcfirst(array_pop($tableName));

        $replace = array(); // A => _a
        foreach (range("A", "Z") as $letter) {
            $replace[$letter] = "_" . strtolower($letter);
        }

        return strtr($tableName, $replace);
    }

}
